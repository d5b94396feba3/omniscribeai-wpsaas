<?php
namespace MediumPostAI;

defined('ABSPATH') || exit;

class RefineHandler {
    public static function handle(\WP_REST_Request $request) {

        error_log('RefineHandler::handle() called'); // Debug line

        ApiHandler::clean_output_buffers();
        
        try {

            if (!class_exists('MediumPostAI\Gemini_Handler')) {
                require_once MPAI_PLUGIN_DIR . 'includes/class-gemini-handler.php';
            }
            if (!class_exists('MediumPostAI\Mycred_Handler')) {
                require_once MPAI_PLUGIN_DIR . 'includes/class-mycred-handler.php';
            }

            $mycred_handler = new Mycred_Handler(); 
            
            // Validate nonce
            $nonce = $request->get_header('X-WP-Nonce');
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                throw new \RuntimeException(__('Invalid nonce.', 'medium-post-ai'), 403);
            }

            // Retrieve parameters
            $mode = "";
            $redesign_output = "html";
            $url = "";
            $output_type = "";
            $desc_tag_name = "";
            $description = $request->get_param('description');
            $iterations = 1;
            $update_description = $request->get_param('update_description');
            $current_code = $request->get_param('current_code');

            $screenshot = ApiHandler::process_image_upload($mode);
            $logo = ApiHandler::process_logo_upload($mode);

            $output_type="html";

            // error_log('logo: ' . $logo);
            error_log('description: ' . $description);
        
            $cost = $mycred_handler->calculate_cost($mode, $output_type, $iterations);
            
            if (!$mycred_handler->has_sufficient_balance($cost)) {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => __('You do not have enough points to proceed. Please buy more points.', 'medium-post-ai'),
                    'error_code' => 403
                ], 403);
            }

            if (!$mycred_handler->deduct_points($cost, 'refine', $output_type, 1)) { 
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => __('Request processing failed. Please try again.', 'medium-post-ai'),
                    'error_code' => 500
                ], 500);
            }

            $generated_content = "";
            $action_content = "";
            $enable_e2j = ($output_type === 'elementor'); // Check if Elementor JSON is desired
    
        
         if(!empty($description) && $output_type === 'html') {

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
                throw new \Exception(__('No valid AI source configured for refinement.', 'medium-post-ai'), 500);
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
                'filename' => 'medium-post-ai-' . time(),
                'usage' => $result['usage'] ?? ['prompt_tokens' => 0, 'completion_tokens' => 0, 'total_tokens' => 0],
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
            
            $refund_result = $mycred_handler->refund_points($cost, $e->getMessage());
            if ($refund_result) {
                error_log('Points refunded successfully');
            } else {
                error_log('Failed to refund points');
            }

            return new \WP_REST_Response([
                'success' => false,
                'message' => __('An error occurred while processing your refinement request, please try again!', 'medium-post-ai'),
                'error_code' => is_numeric($e->getCode()) ? $e->getCode() : 400,
            ], is_numeric($e->getCode()) ? $e->getCode() : 400);
        }
    }
}



