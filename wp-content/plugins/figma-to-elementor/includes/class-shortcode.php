<?php
namespace FigmaToElementor;

class Shortcode {
    public static function init() {
        add_shortcode('figma_to_elementor', [__CLASS__, 'render_shortcode']);
        add_shortcode('figma_to_elementor_preview', [__CLASS__, 'render_preview_shortcode']);
        add_action('template_include', [__CLASS__, 'handle_preview_template']);
    }

    public static function render_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<div class="f2e-notice">' . 
                   __('Please log in to use this tool.', 'figma-to-elementor') . 
                   '</div>';
        }

        wp_enqueue_style('f2e-frontend');
        wp_enqueue_script('f2e-frontend');
        
        // Get or create preview page
        $preview_page_id = self::get_preview_page_id();
        
        wp_localize_script('f2e-frontend', 'f2e_vars', [
            'ajax_url' => rest_url('figma-to-elementor/v1/generate'),
            'ajax_url_progress' => rest_url('figma-to-elementor/v1/progress'),
            'ajax_url_refine' => rest_url('figma-to-elementor/v1/refine'),
            'preview_page_url' => $preview_page_id ? get_permalink($preview_page_id) : '',
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n' => [
                'screenshot' => __('Screenshot', 'figma-to-elementor'),
                'figma_url' => __('Figma URL', 'figma-to-elementor'),
                'upload_screenshot' => __('Upload Screenshot', 'figma-to-elementor'),
                'figma_url_placeholder' => __('https://www.figma.com/file/...', 'figma-to-elementor'),
                'generate' => __('Generate', 'figma-to-elementor'),
                'generated_output' => __('Generated Output', 'figma-to-elementor'),
                'output_type' => __('Output Type', 'figma-to-elementor'),
                'iterations' => __('Number of Iterations', 'figma-to-elementor'),
                'copy_to_clipboard' => __('Copy to Clipboard', 'figma-to-elementor'),
                'download' => __('Download', 'figma-to-elementor'),
                'copied' => __('Copied!', 'figma-to-elementor'),
                'downloading' => __('Downloading...', 'figma-to-elementor'),
                'error' => __('Error:', 'figma-to-elementor'),
                'unknown_error' => __('Unknown error occurred', 'figma-to-elementor'),
                'file_too_large' => __('File is too large. Maximum size is 20MB.', 'figma-to-elementor'),
                'allowed_types' => __('Allowed types', 'figma-to-elementor'),
                'max_size' => __('Max size', 'figma-to-elementor'),
                'figma_url_required' => __('Figma URL is required.', 'figma-to-elementor'),
                'screenshot_required' => __('Screenshot file is required.', 'figma-to-elementor'),
                'invalid_file_type' => __('Invalid file type. Please upload a JPEG or PNG image.', 'figma-to-elementor'),
                'network_error' => __('Network error. Please check your connection.', 'figma-to-elementor'),
                'starting_iteration' => __('Starting iteration', 'figma-to-elementor'),
                'working_on_iteration' => __('Working on iteration', 'figma-to-elementor'),
                'processing_iteration' => __('Processing iteration', 'figma-to-elementor'),
                'refining_code' => __('Please wait while your code is being refined...', 'figma-to-elementor'),
                'of' => __('of', 'figma-to-elementor'),
                'preview' => __('Preview', 'figma-to-elementor'),
                'preparing_preview' => __('Preparing preview...', 'figma-to-elementor'),
                'preview_error' => __('Failed to prepare preview. Please try again.', 'figma-to-elementor'),
                'confirm_clear_generate' => __('Do you want to clear code preview and generate new code?', 'figma-to-elementor'),
                'try_again' => __('An error occurred while processing your request. Please try again.', 'figma-to-elementor'),
                'refine_points' => 100,
                'base_points_per_iteration' => 100
            ]
        ]);

        ob_start();
        include F2E_PLUGIN_DIR . 'templates/tool-template.php';
        return ob_get_clean();
    }

    public static function render_preview_shortcode() {
        // Verify we have a preview ID
        $preview_id = isset($_GET['preview_id']) ? sanitize_text_field($_GET['preview_id']) : '';

        if (empty($preview_id)) {
            return '<div class="f2e-error">' . __('Invalid preview link.', 'figma-to-elementor') . '</div>';
        }

        // Get the preview data
        $preview_data = get_transient('f2e_preview_' . $preview_id);

        // Validate preview data
        if (!$preview_data || !is_array($preview_data) || !isset($preview_data['content'])) {
            return '<div class="f2e-error">' . __('Preview expired or not found.', 'figma-to-elementor') . '</div>';
        }

        // Verify user access if needed
        if (apply_filters('f2e_verify_preview_user', true) && 
            get_current_user_id() !== (int) $preview_data['user_id']) {
            return '<div class="f2e-error">' . __('You do not have permission to view this preview.', 'figma-to-elementor') . '</div>';
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
            plugins_url('assets/css/preview-frontend.css', F2E_PLUGIN_FILE),
            [],
            filemtime(F2E_PLUGIN_DIR . 'assets/css/preview-frontend.css')
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
        include F2E_PLUGIN_DIR . 'templates/preview-template.php';
        return ob_get_clean();
    }

    public static function handle_preview_template($template) {
        if (isset($_GET['f2e_preview']) && $_GET['f2e_preview'] === '1') {
            return F2E_PLUGIN_DIR . 'templates/preview-template.php';
        }
        return $template;
    }

    private static function get_preview_page_id() {
        $preview_page = get_page_by_path('design-to-code-preview');
        
        if (!$preview_page) {
            $page_id = wp_insert_post([
                'post_title' => __('Design to Code Preview', 'figma-to-elementor'),
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