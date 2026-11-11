<?php
namespace MediumPostAI;

class Shortcode {
    public static function init() {
        add_shortcode('medium_post_ai', [__CLASS__, 'render_shortcode']);
        add_shortcode('medium_post_ai_preview', [__CLASS__, 'render_preview_shortcode']);
        add_action('template_include', [__CLASS__, 'handle_preview_template']);
    }

    public static function render_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<div class="f2e-notice">' . 
                   __('Please log in to use this tool.', 'medium-post-ai') . 
                   '</div>';
        }

        wp_enqueue_style('f2e-frontend');
        wp_enqueue_script('f2e-frontend');
        
        // Get or create preview page
        $preview_page_id = self::get_preview_page_id();
        
        wp_localize_script('f2e-frontend', 'MPAI_vars', [
            'ajax_url' => rest_url('medium-post-ai/v1/generate'),
            'ajax_url_progress' => rest_url('medium-post-ai/v1/progress'),
            'ajax_url_refine' => rest_url('medium-post-ai/v1/refine'),
            'preview_page_url' => $preview_page_id ? get_permalink($preview_page_id) : '',
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n' => [
                'screenshot' => __('Screenshot', 'medium-post-ai'),
                'figma_url' => __('Figma URL', 'medium-post-ai'),
                'upload_screenshot' => __('Upload Screenshot', 'medium-post-ai'),
                'figma_url_placeholder' => __('https://www.figma.com/file/...', 'medium-post-ai'),
                'generate' => __('Generate', 'medium-post-ai'),
                'generated_output' => __('Generated Output', 'medium-post-ai'),
                'output_type' => __('Output Type', 'medium-post-ai'),
                'iterations' => __('Number of Iterations', 'medium-post-ai'),
                'copy_to_clipboard' => __('Copy to Clipboard', 'medium-post-ai'),
                'download' => __('Download', 'medium-post-ai'),
                'copied' => __('Copied!', 'medium-post-ai'),
                'downloading' => __('Downloading...', 'medium-post-ai'),
                'error' => __('Error:', 'medium-post-ai'),
                'unknown_error' => __('Unknown error occurred', 'medium-post-ai'),
                'file_too_large' => __('File is too large. Maximum size is 20MB.', 'medium-post-ai'),
                'allowed_types' => __('Allowed types', 'medium-post-ai'),
                'max_size' => __('Max size', 'medium-post-ai'),
                'figma_url_required' => __('Figma URL is required.', 'medium-post-ai'),
                'screenshot_required' => __('Screenshot file is required.', 'medium-post-ai'),
                'invalid_file_type' => __('Invalid file type. Please upload a JPEG or PNG image.', 'medium-post-ai'),
                'network_error' => __('Network error. Please check your connection.', 'medium-post-ai'),
                'starting_iteration' => __('Starting iteration', 'medium-post-ai'),
                'working_on_iteration' => __('Working on iteration', 'medium-post-ai'),
                'processing_iteration' => __('Processing iteration', 'medium-post-ai'),
                'refining_code' => __('Please wait while your code is being refined...', 'medium-post-ai'),
                'of' => __('of', 'medium-post-ai'),
                'preview' => __('Preview', 'medium-post-ai'),
                'preparing_preview' => __('Preparing preview...', 'medium-post-ai'),
                'preview_error' => __('Failed to prepare preview. Please try again.', 'medium-post-ai'),
                'confirm_clear_generate' => __('Do you want to clear code preview and generate new code?', 'medium-post-ai'),
                'try_again' => __('An error occurred while processing your request. Please try again.', 'medium-post-ai'),
                'refine_points' => 100,
                'base_points_per_iteration' => 100
            ]
        ]);

        ob_start();
        include MPAI_PLUGIN_DIR . 'templates/tool-template.php';
        return ob_get_clean();
    }

    public static function render_preview_shortcode() {
        // Verify we have a preview ID
        $preview_id = isset($_GET['preview_id']) ? sanitize_text_field($_GET['preview_id']) : '';

        if (empty($preview_id)) {
            return '<div class="f2e-error">' . __('Invalid preview link.', 'medium-post-ai') . '</div>';
        }

        // Get the preview data
        $preview_data = get_transient('MPAI_preview_' . $preview_id);

        // Validate preview data
        if (!$preview_data || !is_array($preview_data) || !isset($preview_data['content'])) {
            return '<div class="f2e-error">' . __('Preview expired or not found.', 'medium-post-ai') . '</div>';
        }

        // Verify user access if needed
        if (apply_filters('MPAI_verify_preview_user', true) && 
            get_current_user_id() !== (int) $preview_data['user_id']) {
            return '<div class="f2e-error">' . __('You do not have permission to view this preview.', 'medium-post-ai') . '</div>';
        }

        // Clean up the content
        $content = wp_unslash($preview_data['content']);
        $output_type = isset($preview_data['output_type']) ? $preview_data['output_type'] : 'html';
        // Convert 'redesign' to 'html' since they use the same format
        if ($output_type === 'redesign') {
            $output_type = 'html';
        }

        // Enqueue styles
        wp_enqueue_style(
            'f2e-preview-frontend',
            plugins_url('assets/css/preview-frontend.css', MPAI_PLUGIN_FILE),
            [],
            filemtime(MPAI_PLUGIN_DIR . 'assets/css/preview-frontend.css')
        );

        // Enqueue Prism.js for syntax highlighting
        if ($output_type === 'html' || $output_type === 'redesign') {
            wp_enqueue_script('prism', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js', [], '1.29.0');
            wp_enqueue_script('prism-html', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-html.min.js', ['prism'], '1.29.0');
        } else {
            wp_enqueue_script('prism', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js', [], '1.29.0');
            wp_enqueue_script('prism-json', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-json.min.js', ['prism'], '1.29.0');
        }

        ob_start();
        include MPAI_PLUGIN_DIR . 'templates/preview-template.php';
        return ob_get_clean();
    }

    public static function handle_preview_template($template) {
        if (isset($_GET['MPAI_preview']) && $_GET['MPAI_preview'] === '1') {
            return MPAI_PLUGIN_DIR . 'templates/preview-template.php';
        }
        return $template;
    }

    private static function get_preview_page_id() {
        $preview_page = get_page_by_path('design-to-code-preview');
        
        if (!$preview_page) {
            $page_id = wp_insert_post([
                'post_title' => __('Design to Code Preview', 'medium-post-ai'),
                'post_name' => 'design-to-code-preview',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '' // Leave empty - we'll handle content via shortcode
            ]);
            
            return !is_wp_error($page_id) ? $page_id : false;
        }
        
        return $preview_page->ID;
    }

    
}