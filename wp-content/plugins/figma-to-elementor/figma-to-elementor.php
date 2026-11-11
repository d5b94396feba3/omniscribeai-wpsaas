<?php
/**
 * Plugin Name: Figma/Screenshot to Elementor Template
 * Description: Convert Figma designs or screenshots to Elementor JSON templates.
 * Version: 1.0.0
 * Author: Shah
 * Author URI: https://yourwebsite.com
 * Text Domain: figma-to-elementor
 */

defined('ABSPATH') || exit;

// Include Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Define plugin constants
define('F2E_VERSION', '1.0.0');
define('F2E_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('F2E_PLUGIN_URL', plugin_dir_url(__FILE__));
define('F2E_PLUGIN_FILE', __FILE__);

// Check for required PHP version
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        printf(
            __('Figma to Elementor requires PHP 7.4 or higher. Your server is running PHP %s.', 'figma-to-elementor'),
            PHP_VERSION
        );
        echo '</p></div>';
    });
    return;
}

// Manually include the required files since autoloading isn't working
require_once F2E_PLUGIN_DIR . 'includes/class-api-handler.php';
require_once F2E_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once F2E_PLUGIN_DIR . 'includes/class-assets.php';
require_once F2E_PLUGIN_DIR . 'includes/class-error-logger.php';
require_once F2E_PLUGIN_DIR . 'includes/class-generate-handler.php';
require_once F2E_PLUGIN_DIR . 'includes/class-refine-handler.php';

// Initialize the plugin
add_action('plugins_loaded', function() {
    if (!class_exists('Elementor\Plugin')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            _e('Figma to Elementor works best with Elementor installed and activated.', 'figma-to-elementor');
            echo '</p></div>';
        });
    }
    
    // Load text domain
    load_plugin_textdomain('figma-to-elementor', false, basename(dirname(__FILE__)) . '/languages');
    
    // Initialize components
    FigmaToElementor\ApiHandler::init();
    FigmaToElementor\Shortcode::init();
    FigmaToElementor\Assets::init();
});

// Activation/Deactivation hooks
register_activation_hook(__FILE__, function() {
    // Activation tasks if needed
});

register_deactivation_hook(__FILE__, function() {
    // Deactivation tasks if needed
});