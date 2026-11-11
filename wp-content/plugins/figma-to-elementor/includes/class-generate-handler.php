<?php
namespace FigmaToElementor;

defined('ABSPATH') || exit;

class GenerateHandler {
    public static function handle(\WP_REST_Request $request) {

        error_log('GenerateHandler::handle() called'); // Debug line

        ApiHandler::clean_output_buffers();
        
        try {
            if (!class_exists('FigmaToElementor\OpenAI_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-openai-handler.php';
            }
            if (!class_exists('FigmaToElementor\Gemini_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-gemini-handler.php';
            }
            if (!class_exists('FigmaToElementor\Elementor_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-elementor-handler.php';
            }
            if (!class_exists('FigmaToElementor\FigmaAPI_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-figma-api-handler.php';
            }
            if (!class_exists('FigmaToElementor\SiteURL_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-site-url-handler.php';
            }
            if (!class_exists('FigmaToElementor\Mycred_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-mycred-handler.php';
            }
            
            // Validate nonce
            $nonce = $request->get_header('X-WP-Nonce');
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                throw new \RuntimeException(__('Invalid nonce.', 'figma-to-elementor'), 403);
            }

            // Process request with new parameters
            $mode = "";
            $redesign_output = "html";
            $url = "";
            $output_type = "";
            $description = $request->get_param('description');
            $current_code = $request->get_param('current_code');
            // $update_description = $request->get_param('update_description');
            $update_description = $description;
            $iterations = (int)$request->get_param('iterations');
            $desc_tag_name = "";
            $siteURL = "";
            $logo="";
            $system_prompt = "";
            $action_content= "";
            $generated_content= "";
            $enable_e2j = $output_type === 'elementor';
            $mode = "";
            $url = "";

            $output_type="html";

            error_log('iterations: ' . $iterations);
            error_log('description: ' . $description);
            error_log('current_code: ' . $current_code);

            // Initialize myCred handler
            $mycred_handler = new Mycred_Handler();

            // Calculate cost and check balance
            $cost = $mycred_handler->calculate_cost($mode, $output_type, $iterations);
            
            if (!$mycred_handler->has_sufficient_balance($cost)) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => __('Insufficient balance to proceed. Please purchase points from the Pricing section.', 'figma-to-elementor'),
                    'error_code' => 403
                ], 403);
            }

            // Deduct points upfront
            if (!$mycred_handler->deduct_points($cost, $mode, $output_type, $iterations)) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => __('Payment processing failed.', 'figma-to-elementor'),
                    'error_code' => 500
                ], 500);
            }


        if( !empty($description) && $output_type === 'html' && empty($current_code)) {


            $system_prompt =  ApiHandler::get_description_html_system_prompt_content();

            error_log('Initializing Google Gemini for generating 3d badge maker from the description');
            
            // HTML generation from the screenshot
            $html_llm_source = "Google Gemini";
            // $html_llm_source = "OpenAI";
            if ($html_llm_source == "OpenAI") {
                error_log('Initializing OpenAI for generating HTML from the screenshot');
                $ai_handler = new OpenAI_Handler();
                $messages = $ai_handler->prepare_messages_openai($mode, $system_prompt, $url, $logo, $description);
                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);
                $generated_content = $result['content'];

            } else if ($html_llm_source == "Google Gemini") {
                error_log('Initializing Google Gemini for generating HTML from the description');
                // Option 2 - Gemini
                $ai_handler = new Gemini_Handler();
                $messages = $ai_handler->prepare_messages_gemini_for_description($mode, $output_type, $system_prompt, $url, $logo, $description);

                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);
                $generated_content = $result['content'];
                error_log('generated_content row: ' . $generated_content);

            }

        }

        if(!empty($description) && $output_type === 'html' && !empty($current_code)) {


            $refine_system_prompt =  ApiHandler::get_description_html_system_prompt_content();
            // Choose AI handler (Gemini or OpenAI)
            $ai_handler = null;
            $llm_source = "Google Gemini"; // You can make this configurable
    
            if ($llm_source == "OpenAI") {
                $ai_handler = new OpenAI_Handler();
                // For OpenAI, prepare messages specifically for refinement
                $messages = $ai_handler->prepare_refine_messages_openai(
                    $refine_system_prompt, 
                    $current_code, 
                    $update_description,
                    $description,
                );
                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);
                $generated_content = $result['content'];
    
    
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
                $generated_content = $result['content'];
    
                // error_log('generated_content: ' . $generated_content);
            } else {
                throw new \Exception(__('No valid AI source configured for refinement.', 'figma-to-elementor'), 500);
            }
        }

            // Validate the generated content
            ApiHandler::content_error_validation($output_type, $generated_content);

            // Store the generated content and get preview ID
            $preview_id = ApiHandler::store_preview_content($output_type, $generated_content);
  
            return new \WP_REST_Response([
                'success' => true,
                'input_type' => $mode,
                'result' => $generated_content,
                'output_type' => $output_type,
                'iterations' => $iterations,
                'action_response' => $action_content,
                'filename' => 'figma-to-elementor-' . time(),
                'usage' => $result['usage'],
                'preview_id' => $preview_id
            ], 200);

        } catch (\Exception $e) {
            ApiHandler::clean_output_buffers();
            error_log('API Handler Error: ' . $e->getMessage());
            
            ErrorLogger::log_error([
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'request_type' => 'generate',
                'request_data' => $request->get_params(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            $refund_result = $mycred_handler->refund_points($e->getMessage());
            if ($refund_result) {
                error_log('Points refunded successfully');
            } else {
                error_log('Failed to refund points');
            }

            return new \WP_REST_Response([
                'success' => false,
                'message' => __('An error occurred while processing your request, please try again!', 'figma-to-elementor'),
                'error_code' => is_numeric($e->getCode()) ? $e->getCode() : 400,
                'allowed_types' => ['JPEG', 'PNG'],
                'max_size' => '20MB'
            ], is_numeric($e->getCode()) ? $e->getCode() : 400);
        }
    }
}