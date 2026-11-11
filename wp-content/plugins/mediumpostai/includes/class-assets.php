<?php
namespace MediumPostAI;

defined('ABSPATH') || exit;

class Assets {
    public static function init() {
        // Correctly hook into the action for enqueuing frontend scripts
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
    }

    public static function register_assets() {
        // Only load assets when shortcode is present
        global $post;
        // MPAI_PLUGIN_URL and MPAI_VERSION must be defined in your main plugin file
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'medium_post_ai')) {
            return;
        }

        // --- CSS REGISTRATIONS ---
        wp_register_style(
            'f2e-frontend',
            MPAI_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            MPAI_VERSION
        );

        wp_register_style(
            'f2e-prism',
            'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css',
            [],
            '1.29.0'
        );

        // --- JAVASCRIPT REGISTRATIONS ---
        
        // Prism.js (Registered but loaded dynamically by JS)
        wp_register_script(
            'f2e-prism',
            'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js',
            [],
            '1.29.0',
            true
        );

        wp_register_script(
            'f2e-prism-json',
            'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-json.min.js',
            ['f2e-prism'],
            '1.29.0',
            true
        );
        
        // 1. Core Module (Depends on jQuery)
        wp_register_script(
            'f2e-core',
            MPAI_PLUGIN_URL . 'assets/js/f2e-core.js',
            ['jquery'], 
            MPAI_VERSION,
            true // Load in footer
        );

        // 2. UI Components Module (Depends on Core)
        wp_register_script(
            'f2e-ui-components',
            MPAI_PLUGIN_URL . 'assets/js/f2e-ui-components.js',
            ['f2e-core'], 
            MPAI_VERSION,
            true
        );

        // 3. Generate Logic Module (Depends on Core and UI)
        wp_register_script(
            'f2e-generate',
            MPAI_PLUGIN_URL . 'assets/js/f2e-generate.js',
            ['f2e-core', 'f2e-ui-components'], 
            MPAI_VERSION,
            true
        );

        // 4. NEW Image Actions Module
        wp_register_script(
            'f2e-image-actions',
            MPAI_PLUGIN_URL . 'assets/js/f2e-image.js',
            ['f2e-core', 'f2e-ui-components'], // Depends on both core and UI (for loader, metadata functions)
            MPAI_VERSION,
            true
        );

        // --- LOCALIZATION (CRITICAL FIX) ---
        // Add new AJAX URLs to MPAI_vars for the new image functionality
        wp_localize_script(
            'f2e-core', 
            'MPAI_vars', 
            [
                'ajax_url' => rest_url('medium-post-ai/v1/generate'),
                'ajax_url_progress' => rest_url('medium-post-ai/v1/progress'),
                'ajax_url_refine_image' => rest_url('medium-post-ai/v1/refine-image'), // NEW ENDPOINT
                'ajax_url_regenerate_image' => rest_url('medium-post-ai/v1/regenerate-image'), // NEW ENDPOINT
                'nonce' => wp_create_nonce('wp_rest'),
                // NOTE: Adjust 'preview_page_url' retrieval as needed for your specific setup
                'preview_page_url' => get_permalink(get_page_by_title('MPAI Preview')) ?: site_url(), 
                'i18n' => [
                    'generating' => __('Generating...', 'medium-post-ai'),
                    'please_enter_description' => __('Please enter a description', 'medium-post-ai'),
                    'unknown_error' => __('An unknown error occurred.', 'medium-post-ai'),
                    'try_again' => __('Please try again.', 'medium-post-ai'),
                    'network_error' => __('Network error. Please check your connection.', 'medium-post-ai'),
                    'copied' => __('Copied!', 'medium-post-ai'),
                    'downloading' => __('Downloading...', 'medium-post-ai'),
                    'confirm_clear_generate' => __('Do you want to clear the generated content?', 'medium-post-ai'),
                    'preparing_preview' => __('Preparing preview...', 'medium-post-ai'),
                    'preview' => __('Preview', 'medium-post-ai'),
                    'confirm_regenerate_image' => __('Are you sure you want to regenerate the featured image? This will cost additional points.', 'medium-post-ai'), // NEW I18N
                    // Add any other i18n strings used in your JS
                ],
            ]
        );
        
        // --- ENQUEUING SCRIPTS AND STYLES ---

        // Styles
        // wp_enqueue_style('f2e-frontend');
        // You can conditionally enqueue f2e-prism style here if you want it loaded immediately.
        // wp_enqueue_style('f2e-prism');

        // Scripts (Must be in order due to dependencies)
        wp_enqueue_script('f2e-core');
        wp_enqueue_script('f2e-ui-components');
        wp_enqueue_script('f2e-image-actions'); // NEW
        wp_enqueue_script('f2e-generate');
    }
}