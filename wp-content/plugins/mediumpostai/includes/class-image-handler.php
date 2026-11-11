<?php
// Assume this is in includes/class-image-handler.php
namespace MediumPostAI;

defined('ABSPATH') || exit;

class ImageHandler {
    
    /**
     * Handles the image refinement request.
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public static function handle_refine_image(\WP_REST_Request $request) {
        ApiHandler::clean_output_buffers();
        
        // 1. Get Params
        $base_concept = $request->get_param('base_concept');
        $refinement_instruction = $request->get_param('refinement_instruction');
        $post_title = $request->get_param('post_title');
        $content_type = $request->get_param('content_type') ?: 'medium';
        $final_prompt = trim($base_concept . ', ' . $refinement_instruction);

        error_log("Image Refine Request - Final Prompt: " . $final_prompt);

        // 2. Load dependencies and check nonce
        if (!class_exists('MediumPostAI\Gemini_Handler')) {
            require_once MPAI_PLUGIN_DIR . 'includes/class-gemini-handler.php';
        }
        if (!class_exists('MediumPostAI\Mycred_Handler')) {
            require_once MPAI_PLUGIN_DIR . 'includes/class-mycred-handler.php';
        }
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Invalid nonce.', 'medium-post-ai')], 403);
        }
        
        // 3. Initialize handlers and process payment (Assuming 100 points for image generation/refinement)
        $mycred_handler = new Mycred_Handler();
        $cost = 100; // Define a cost for image ops
        if (!$mycred_handler->has_sufficient_balance($cost)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Insufficient balance for image refinement.', 'medium-post-ai')], 403);
        }
        if (!$mycred_handler->deduct_points($cost, 'image', 'refine', 1)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Payment processing failed for image refinement.', 'medium-post-ai')], 500);
        }

        try {
            // 4. Get System Prompt and Generate Image
            $imagen_system_prompt = ApiHandler::get_imagen_system_prompt_content();
            $ai_handler = new Gemini_Handler();
            
            // Call the Gemini handler's image generation function
            $generated_img_url = $ai_handler->generate_image($final_prompt, $imagen_system_prompt, $content_type);
            
            // 5. Success Response
            return new \WP_REST_Response([
                'success' => true,
                'message' => __('Image refined and regenerated successfully.', 'medium-post-ai'),
                'post_title' => $post_title,
                'image_url' => $generated_img_url,
            ], 200);

        } catch (\Exception $e) {
            ApiHandler::clean_output_buffers();
            error_log('Image Refine Error: ' . $e->getMessage());
            $mycred_handler->refund_points($e->getMessage());
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Image refinement failed. Error: ' . $e->getMessage(), 'medium-post-ai'),
                'error_code' => 400
            ], 400);
        }
    }
    
    /**
     * Handles the image regeneration request.
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public static function handle_regenerate_image(\WP_REST_Request $request) {
        ApiHandler::clean_output_buffers();

        // 1. Get Params
        $base_concept = $request->get_param('base_concept'); // This is the post title
        $post_title = $request->get_param('post_title');
        $content_type = $request->get_param('content_type') ?: 'medium';
        $final_prompt = $base_concept; // Use the title directly for regeneration

        error_log("Image Regenerate Request - Final Prompt: " . $final_prompt);

        // 2. Load dependencies and check nonce (same as refine)
        if (!class_exists('MediumPostAI\Gemini_Handler')) {
            require_once MPAI_PLUGIN_DIR . 'includes/class-gemini-handler.php';
        }
        if (!class_exists('MediumPostAI\Mycred_Handler')) {
            require_once MPAI_PLUGIN_DIR . 'includes/class-mycred-handler.php';
        }
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Invalid nonce.', 'medium-post-ai')], 403);
        }
        
        // 3. Initialize handlers and process payment (Assuming 100 points for image generation/refinement)
        $mycred_handler = new Mycred_Handler();
        $cost = 100; // Define a cost for image ops
        if (!$mycred_handler->has_sufficient_balance($cost)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Insufficient balance for image regeneration.', 'medium-post-ai')], 403);
        }
        if (!$mycred_handler->deduct_points($cost, 'image', 'regenerate', 1)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Payment processing failed for image regeneration.', 'medium-post-ai')], 500);
        }

        try {
            // 4. Get System Prompt and Generate Image
            $imagen_system_prompt = ApiHandler::get_imagen_system_prompt_content();
            $ai_handler = new Gemini_Handler();
            
            // Call the Gemini handler's image generation function
            $generated_img_url = $ai_handler->generate_image($final_prompt, $imagen_system_prompt, $content_type);
            
            // 5. Success Response
            return new \WP_REST_Response([
                'success' => true,
                'message' => __('Image regenerated successfully.', 'medium-post-ai'),
                'post_title' => $post_title,
                'image_url' => $generated_img_url,
            ], 200);

        } catch (\Exception $e) {
            ApiHandler::clean_output_buffers();
            error_log('Image Regenerate Error: ' . $e->getMessage());
            $mycred_handler->refund_points($e->getMessage());
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Image regeneration failed. Error: ' . $e->getMessage(), 'medium-post-ai'),
                'error_code' => 400
            ], 400);
        }
    }
}