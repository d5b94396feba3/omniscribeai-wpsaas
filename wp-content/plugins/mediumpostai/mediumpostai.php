<?php
/**
 * Plugin Name: mediumpostai
 * Description: mediumpostai
 * Version: 1.0.0
 * Author: Shah
 * Author URI: https://yourwebsite.com
 * Text Domain: mediumpostai
 */

defined('ABSPATH') || exit;


// Define plugin constants
define('MPAI_VERSION', '1.0.0');
define('MPAI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MPAI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MPAI_PLUGIN_FILE', __FILE__);

// Check for required PHP version
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        printf(
            __('Figma to Elementor requires PHP 7.4 or higher. Your server is running PHP %s.', 'medium-post-ai'),
            PHP_VERSION
        );
        echo '</p></div>';
    });
    return;
}

// Manually include the required files since autoloading isn't working
require_once MPAI_PLUGIN_DIR . 'includes/class-api-handler.php';
require_once MPAI_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once MPAI_PLUGIN_DIR . 'includes/class-assets.php';
require_once MPAI_PLUGIN_DIR . 'includes/class-error-logger.php';
require_once MPAI_PLUGIN_DIR . 'includes/class-generate-handler.php';
require_once MPAI_PLUGIN_DIR . 'includes/class-refine-handler.php';
// Ensure the new ImageHandler class is loaded
require_once MPAI_PLUGIN_DIR . 'includes/class-image-handler.php';
// Initialize the plugin
add_action('plugins_loaded', function() {
    if (!class_exists('Elementor\Plugin')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            _e('Figma to Elementor works best with Elementor installed and activated.', 'medium-post-ai');
            echo '</p></div>';
        });
    }
    
    // Load text domain
    load_plugin_textdomain('medium-post-ai', false, basename(dirname(__FILE__)) . '/languages');
    
    // Initialize components
    MediumPostAI\ApiHandler::init();
    MediumPostAI\Shortcode::init();
    MediumPostAI\Assets::init();
});

// Activation/Deactivation hooks
register_activation_hook(__FILE__, function() {
    // Activation tasks if needed
});

register_deactivation_hook(__FILE__, function() {
    // Deactivation tasks if needed
});