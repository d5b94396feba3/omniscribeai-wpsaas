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
        $result = ['usage' => []]; // Initialize $result with an empty usage array
        $usage = []; // Initialize $usage variable
        $preview_id = null; // Initialize preview ID

        $url = $request->get_param('url');

        $siteURL = $request->get_param('siteURL');
        $output_type="html"; // Fixed output type for this handler

        error_log('iterations: ' . $iterations);
        error_log('description: ' . $description);
        error_log('current_code: ' . $current_code);
        error_log('siteURL: ' . $siteURL);

        $content_type = $request->get_param('content_type') ?: 'medium';
    
        // Update system prompt based on content type
        if ($content_type === 'devio') {
            $system_prompt = ApiHandler::get_devio_system_prompt_content();
        } 
        else if($content_type === 'medium') {
            $system_prompt = ApiHandler::get_medium_system_prompt_content();
        }
        else if($content_type === 'linkedin') {
            $system_prompt = ApiHandler::get_linkedin_system_prompt_content();
        }
        else if($content_type === 'x-twitter') {
            $system_prompt = ApiHandler::get_x_twitter_system_prompt_content();
        }
        else if($content_type === 'website') {
            $system_prompt = ApiHandler::get_website_system_prompt_content();
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


        if( !empty($description) && $output_type === 'html' && empty($current_code)) {

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
                $messages = $ai_handler->prepare_messages_gemini_for_description($mode, $output_type, $system_prompt, $url, $logo, $description);

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

                // Only generate image if we have a title/concept
                if (!empty($title)) {
                    $generated_img_link = $ai_handler->generate_image($title, $imagen_system_prompt);
                }

                error_log('Extracted Title: ' . $title);
                error_log('Extracted Tags: ' . implode(', ', $tags));
                error_log('Image Concept: ' . $image_concept);
                error_log('generated_img_link 1: ' . $generated_img_link);
                error_log('content: ' . $content);
                error_log('generated_content row: ' . print_r($generated_content, true));
            }

        }

        if(!empty($description) && $output_type === 'html' && !empty($current_code)) {

            $refine_system_prompt =  $system_prompt;
            // Choose AI handler (Gemini or OpenAI)
            $llm_source = "Google Gemini";
    
            if ($llm_source == "OpenAI") {
                // OpenAI refinement logic here...
            } else if ($llm_source == "Google Gemini") {
                $ai_handler = new Gemini_Handler();
                // For Gemini, prepare messages specifically for refinement
                $messages = $ai_handler->prepare_refine_description_messages_gemini(
                    $refine_system_prompt, 
                    $current_code, 
                    $update_description,
                    $description,
                    $logo
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

                // Only generate image if we have a title/concept
                if (!empty($title)) {
                    $generated_img_link = $ai_handler->generate_image($title, $imagen_system_prompt);
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
}