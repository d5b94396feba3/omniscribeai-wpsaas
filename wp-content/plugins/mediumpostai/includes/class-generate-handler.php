<?php
namespace MediumPostAI;

defined('ABSPATH') || exit;

class GenerateHandler {
    public static function handle(\WP_REST_Request $request) {

        error_log('GenerateHandler::handle() called'); // Debug line

        ApiHandler::clean_output_buffers();
        
        // 1. Initialize all return and intermediate variables
        $mode = "";
        $redesign_output = "html";
        $url = "";
        $output_type = "";
        $description = $request->get_param('description');
        $description_yt = $request->get_param('description_yt');
        $current_code = $request->get_param('current_code');
        $update_description = $description;
        $iterations = (int)$request->get_param('iterations');
        $desc_tag_name = "";
        $siteURL = "";
        $logo="";
        $system_prompt = "";
        $action_content= "";
        $generated_content= "";
        $generated_img_link = ""; // Initialize for image link
        $title = ""; // Initialize for extracted title
        $content = ""; // Initialize for extracted content (HTML/text)
        $tags = []; // Initialize for extracted tags
        $target_keyword = ""; 
        $audience_level = ""; 
        $desired_tone = ""; 
        $language = ""; // NEW FIELD
        $required_length = ""; // NEW FIELD
        $call_to_action = ""; // NEW FIELD
        $result = ['usage' => []]; // Initialize $result with an empty usage array
        $usage = []; // Initialize $usage variable
        $preview_id = null; // Initialize preview ID

        // $url = $request->get_param('url');

        $siteURL = $request->get_param('siteURL');
        $output_type="html"; // Fixed output type for this handler

        // NEW: Retrieve Advanced Settings
        $target_keyword = $request->get_param('target_keyword');
        $audience_level = $request->get_param('audience_level');
        $desired_tone = $request->get_param('desired_tone');
        $language = $request->get_param('language'); // NEW
        $required_length = $request->get_param('required_length'); // NEW
        $call_to_action = $request->get_param('call_to_action'); // NEW
        // END NEW

        error_log('iterations: ' . $iterations);
        error_log('description: ' . $description);
        error_log('description_yt: ' . $description_yt);
        error_log('current_code: ' . $current_code);
        error_log('siteUR2L: ' . $siteURL);
        error_log('target_keyword: ' . $target_keyword);
        error_log('audience_level: ' . $audience_level);
        error_log('desired_tone: ' . $desired_tone);
        error_log('language: ' . $language); // NEW
        error_log('required_length: ' . $required_length); // NEW
        error_log('call_to_action: ' . $call_to_action); // NEW

        if (trim($current_code) === 'No content generated yet.') {
            $current_code = '';
        }

        // return;

        $description = $request->get_param('description');

        // Ensure description is properly sanitized without breaking text content
        if (!empty($description)) {
            $description = wp_strip_all_tags($description, false);
        }

        $content_type = $request->get_param('content_type') ?: 'medium';
    
        // Optimized: Use mapping to reduce branching and redundancy.
        $system_prompt_map_with_url = [
            'facebook'   => ['method' => 'get_yt_facebook_system_prompt_content', 'log' => true],
            'website'    => ['method' => 'get_yt_website_system_prompt_content'],
            'devio'      => ['method' => 'get_yt_devio_system_prompt_content'],
            'medium'     => ['method' => 'get_yt_medium_system_prompt_content'],
            'linkedin'   => ['method' => 'get_yt_linkedin_system_prompt_content'],
            'x-twitter'  => ['method' => 'get_yt_x_twitter_system_prompt_content'],
            'instagram'  => ['method' => 'get_yt_instagram_system_prompt_content', 'log' => true],
        ];

        $system_prompt_map = [
            'devio'      => ['method' => 'get_devio_system_prompt_content'],
            'medium'     => ['method' => 'get_medium_system_prompt_content'],
            'linkedin'   => ['method' => 'get_linkedin_system_prompt_content'],
            'x-twitter'  => ['method' => 'get_x_twitter_system_prompt_content'],
            'website'    => ['method' => 'get_website_system_prompt_content'],
            'facebook'   => ['method' => 'get_facebook_system_prompt_content', 'log' => true],
            'instagram'  => ['method' => 'get_instagram_system_prompt_content', 'log' => true],
        ];

        if (!empty($siteURL) && isset($system_prompt_map_with_url[$content_type])) {
            if (!empty($system_prompt_map_with_url[$content_type]['log'])) {
                error_log('content_type: ' . $content_type);
            }
            $method = $system_prompt_map_with_url[$content_type]['method'];
            $system_prompt = ApiHandler::$method();
            if (!empty($system_prompt_map_with_url[$content_type]['log'])) {
                error_log('system_prompt: ' . $system_prompt);
            }
        } elseif (isset($system_prompt_map[$content_type])) {
            if (!empty($system_prompt_map[$content_type]['log'])) {
                error_log('content_type: ' . $content_type);
            }
            $method = $system_prompt_map[$content_type]['method'];
            $system_prompt = ApiHandler::$method();
            if (!empty($system_prompt_map[$content_type]['log'])) {
                error_log('system_prompt: ' . $system_prompt);
            }
        }

        try {

            if (!class_exists('MediumPostAI\Gemini_Handler')) {
                require_once MPAI_PLUGIN_DIR . 'includes/class-gemini-handler.php';
            }
            if (!class_exists('MediumPostAI\Mycred_Handler')) {
                require_once MPAI_PLUGIN_DIR . 'includes/class-mycred-handler.php';
            }
            
            // Validate nonce
            $nonce = $request->get_header('X-WP-Nonce');
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                throw new \RuntimeException(__('Invalid nonce.', 'medium-post-ai'), 403);
            }

            error_log('geting_imagen_system_prompt_content.... ');
            $imagen_system_prompt = ApiHandler::get_imagen_system_prompt_content();
            error_log('imagen_system_prompt: '.$imagen_system_prompt);

            // Initialize myCred handler
            $mycred_handler = new Mycred_Handler();

            // Calculate cost and check balance
            $cost = $mycred_handler->calculate_cost($mode, $output_type, $iterations);
            
            if (!$mycred_handler->has_sufficient_balance($cost)) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => __('Insufficient balance to proceed. Please purchase points from the Pricing section.', 'medium-post-ai'),
                    'error_code' => 403
                ], 403);
            }

            // Deduct points upfront
            if (!$mycred_handler->deduct_points($cost, $mode, $output_type, $iterations)) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => __('Payment processing failed.', 'medium-post-ai'),
                    'error_code' => 500
                ], 500);
            }


            $is_youtube_mode = !empty($siteURL);
            if (($is_youtube_mode || !empty($description)) && $output_type === 'html' && empty($current_code)) {

            error_log('Initializing Google Gemini for generating 3d badge maker from the description');
            
            // HTML generation from the description
            $html_llm_source = "Google Gemini";
            // $html_llm_source = "OpenAI"; // Assuming OpenAI_Handler is available if needed

            if ($html_llm_source == "OpenAI") {
                error_log('Initializing OpenAI for generating HTML from the screenshot');
                // $ai_handler = new OpenAI_Handler(); // Uncomment if OpenAI is used
                // $messages = $ai_handler->prepare_messages_openai($mode, $system_prompt, $url, $logo, $description); // Uncomment if OpenAI is used
                // $result = $ai_handler->generate_content_with_agentic($messages, $iterations); // Uncomment if OpenAI is used
                // $generated_content = $result['content']; // Uncomment if OpenAI is used

            } else if ($html_llm_source == "Google Gemini") {
                error_log('Initializing Google Gemini for generating HTML from the description');
                // Option 2 - Gemini
                $ai_handler = new Gemini_Handler();
                
                // UPDATED CALL: Pass all advanced parameters
                $messages = $ai_handler->prepare_messages_gemini_for_description(
                    $mode, $output_type, $system_prompt, $url, $logo, $description, 
                    $target_keyword, $audience_level, $desired_tone, 
                    $language, $required_length, $call_to_action // NEW PARAMS
                );
                // $messages = $ai_handler->prepare_messages_gemini_for_description_yt($mode, $output_type, $system_prompt, $url, $logo, $description, $siteURL); // Original commented-out line

                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);

                // Access the extracted data
                $generated_content = $result['content'];
                $usage = $result['usage']; // <--- Extract usage here
                $extracted_data = $result['extracted_data'];

                // Now you can easily access each component
                $title = $extracted_data['title'];
                $content = $extracted_data['content'];
                $tags = $extracted_data['tags'];
                $image_concept = $extracted_data['image'];


                // --- NEW VALIDATION CALL ---
                self::validate_extracted_metadata($title, $content, $tags);
                // ---------------------------

                // Only generate image if we have a title/concept
                if (!empty($title)) {
                    $generated_img_link = $ai_handler->generate_image($title, $imagen_system_prompt, $content_type);
                }

                error_log('Extracted Title: ' . $title);
                error_log('Extracted Tags: ' . implode(', ', $tags));
                error_log('Image Concept: ' . $image_concept);
                error_log('generated_img_link 1: ' . $generated_img_link);
                error_log('content: ' . $content);
                error_log('generated_content row: ' . print_r($generated_content, true));
            }

        }

        $is_youtube_mode = !empty($siteURL);
        if (($is_youtube_mode || !empty($description)) && $output_type === 'html' && !empty($current_code)) {

            $refine_system_prompt =  $system_prompt;

            // error_log('refine_system_prompt: ' . $system_prompt);
            error_log('current_code222: ' . $current_code);
            // error_log('current_code: ' . $current_code);
            // error_log('siteUR2L: ' . $siteURL);
            // Choose AI handler (Gemini or OpenAI)
            $llm_source = "Google Gemini";
    
            if ($llm_source == "OpenAI") {
                // OpenAI refinement logic here...
            } else if ($llm_source == "Google Gemini") {
                $ai_handler = new Gemini_Handler();
                // For Gemini, prepare messages specifically for refinement
                // $messages = $ai_handler->prepare_refine_description_messages_gemini(
                //     $refine_system_prompt, 
                //     $current_code, 
                //     $update_description,
                //     $description,
                //     $logo
                // ); // Original commented-out line

                // UPDATED CALL: Pass all advanced parameters to the refinement function
                $messages = $ai_handler->prepare_refine_description_messages_gemini_yt(
                    $refine_system_prompt, 
                    $current_code, 
                    $update_description,
                    $description,
                    $logo,
                    $siteURL,
                    $target_keyword,
                    $audience_level,
                    $desired_tone,
                    $language, $required_length, $call_to_action // NEW PARAMS
                );

                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);
                // Access the extracted data
                $generated_content = $result['content'];
                $usage = $result['usage']; // <--- Extract usage here
                $extracted_data = $result['extracted_data'];

                // Now you can easily access each component
                $title = $extracted_data['title'];
                $content = $extracted_data['content'];
                $tags = $extracted_data['tags'];
                $image_concept = $extracted_data['image'];

                // --- NEW VALIDATION CALL ---
                self::validate_extracted_metadata($title, $content, $tags);
                // ---------------------------

                // Only generate image if we have a title/concept
                if (!empty($title)) {
                    $generated_img_link = $ai_handler->generate_image($title, $imagen_system_prompt, $content_type);
                }

                error_log('Extracted Title: ' . $title);
                error_log('Extracted Tags: ' . implode(', ', $tags));
                error_log('Image Concept: ' . $image_concept);
                error_log('content: ' . $content);
                error_log('generated_img_link 2: ' . $generated_img_link);
                error_log('generated_content row: ' . print_r($generated_content, true));
            } else {
                throw new \Exception(__('No valid AI source configured for refinement.', 'medium-post-ai'), 500);
            }
        }

            // Validate the generated content
            ApiHandler::content_error_validation($output_type, $generated_content);

            // Store the generated content and get preview ID
            $preview_id = ApiHandler::store_preview_content($output_type, $generated_content);
  
            // Final success response using the extracted variables
            return new \WP_REST_Response([
                'success' => true,
                'input_type' => $mode,
                'title' => $title,
                'tags' => $tags,
                'image' => $generated_img_link,
                'content' => $content,
                'output_type' => $output_type,
                'iterations' => $iterations,
                'action_response' => $action_content,
                'filename' => 'medium-post-ai-' . time(),
                'usage' => $usage, // **Using the explicitly extracted $usage array**
                'preview_id' => $preview_id
            ], 200);

        } catch (\Exception $e) {
            ApiHandler::clean_output_buffers();
            error_log('API Handler Error: ' . $e->getMessage());
            
            // ... (ErrorLogger::log_error and refund_points logic remains the same)
            
            $refund_result = $mycred_handler->refund_points($e->getMessage());
            if ($refund_result) {
                error_log('Points refunded successfully');
            } else {
                error_log('Failed to refund points');
            }

            return new \WP_REST_Response([
                'success' => false,
                'message' => __('An error occurred while processing your request, please try again!', 'medium-post-ai'),
                'error_code' => is_numeric($e->getCode()) ? $e->getCode() : 400,
                'allowed_types' => ['JPEG', 'PNG'],
                'max_size' => '20MB'
            ], is_numeric($e->getCode()) ? $e->getCode() : 400);
        }
    }

    /**
     * Checks if the extracted data is sufficient for a valid post.
     *
     * @param string $title The extracted title.
     * @param string $content The extracted content body.
     * @param array $tags The extracted tags array.
     * @return bool True if validation passes.
     * @throws \Exception If validation fails.
     */
    private static function validate_extracted_metadata(string $title, string $content, array $tags): bool {
        
        // 1. Check for valid Title
        // 'Generated Content' is the default fallback title returned when extraction fails.
        if (empty(trim($title)) || $title === 'Generated Content') {
            error_log("Validation failed: Missing or default title.");
            throw new \Exception(__('Failed to extract a valid Title. The model output was not correctly formatted JSON. Please try refining the prompt.', 'medium-post-ai'), 400);
        }

        // 2. Check for valid Content
        // We look for a minimum length and ensure it's not too short.
        if (empty(trim($content)) || strlen(trim($content)) < 50) {
            error_log("Validation failed: Content too short or empty.");
            throw new \Exception(__('Failed to extract a meaningful Content body (minimum 50 characters required). The model output may be malformed or too brief.', 'medium-post-ai'), 400);
        }

        // 3. Check for Tags array
        if (empty($tags) || !is_array($tags) || count($tags) < 1) {
            error_log("Validation failed: Tags missing or empty.");
            throw new \Exception(__('Failed to extract required Tags. Please ensure the model returned a valid "tags" array in the JSON.', 'medium-post-ai'), 400);
        }
        
        return true;
    }
}