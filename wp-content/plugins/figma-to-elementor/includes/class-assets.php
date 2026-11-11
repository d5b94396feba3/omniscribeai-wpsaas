<?php
namespace FigmaToElementor;

class Assets {
    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
    }

    public static function register_assets() {
        // Only load assets when shortcode is present
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'figma_to_elementor')) {
            return;
        }

        // CSS
        wp_register_style(
            'f2e-frontend',
            F2E_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            F2E_VERSION
        );

        // Prism.js for syntax highlighting
        wp_register_style(
            'f2e-prism',
            'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css',
            [],
            '1.29.0'
        );

        // JavaScript
        wp_register_script(
            'f2e-frontend',
            F2E_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            F2E_VERSION,
            true
        );

        // Prism.js for syntax highlighting
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
    }
}