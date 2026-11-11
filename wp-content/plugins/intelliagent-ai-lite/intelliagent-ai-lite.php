<?php
/**
 * Plugin Name:  IntelliAgent AI Lite
 * Description:  24/7 AI Sales & Customer Support Assistant for WordPress & WooCommerce. Automate FAQs, recommend products, and boost sales with multi-model AI.
 * Version:      1.0.7
 * Author:       IntelliAgent AI
 * Author URI:   https://aica-intelliagent.com/
 * Text Domain:  intelliagent-ai-lite
 * Domain Path:  /languages
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 10.9
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package      IntelliAgent_AI_Lite
 * @author       IntelliAgent AI
 * @since        1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get plugin header data using a prefixed variable.
$inteaili_plugin_data = get_file_data(
	__FILE__,
	array(
		'Version'    => 'Version',
		'TextDomain' => 'Text Domain',
		'Name'       => 'Plugin Name',
	),
	'plugin'
);

if ( ! defined( 'INTEAILI_NAME' ) ) {
	define( 'INTEAILI_NAME', $inteaili_plugin_data['Name'] );
}
if ( ! defined( 'INTEAILI_SLUG' ) ) {
	define( 'INTEAILI_SLUG', $inteaili_plugin_data['TextDomain'] );
}
if ( ! defined( 'INTEAILI_VERSION' ) ) {
	define( 'INTEAILI_VERSION', $inteaili_plugin_data['Version'] );
}
if ( ! defined( 'INTEAILI_PATH' ) ) {
	define( 'INTEAILI_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'INTEAILI_URL' ) ) {
	define( 'INTEAILI_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'INTEAILI_FILE' ) ) {
	define( 'INTEAILI_FILE', __FILE__ );
}
if ( ! defined( 'INTEAILI_BASENAME' ) ) {
	define( 'INTEAILI_BASENAME', plugin_basename( __FILE__ ) );
}

// Clean up the global variable.
unset( $inteaili_plugin_data );

/**
 * The bootstrap class that handles loading the plugin.
 *
 * @since    1.0.0
 */
class INTEAILI_Bootstrap {

	private static $inteaili_initiated = false;

	/**
	 * Initialize the plugin.
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		// If already initiated, stop here.
        if ( self::$inteaili_initiated ) {
            return;
        }
        self::$inteaili_initiated = true;

        self::load_dependencies();
        self::run_plugin();
    
	}

	/**
	 * Load all required class dependencies.
	 *
	 * @since    1.0.0
	 */
	private static function load_dependencies() {
		require_once INTEAILI_PATH . 'includes/class-inteaili-loader.php';
		require_once INTEAILI_PATH . 'includes/class-inteaili-main.php';
		require_once INTEAILI_PATH . 'admin/class-inteaili-admin.php';
		require_once INTEAILI_PATH . 'includes/llm/class-inteaili-llm-processor.php';
		require_once INTEAILI_PATH . 'includes/llm/class-inteaili-llm-gemini.php';
		require_once INTEAILI_PATH . 'includes/llm/class-inteaili-llm-openai.php';
		require_once INTEAILI_PATH . 'includes/db/class-inteaili-db-manager.php';
		require_once INTEAILI_PATH . 'includes/scraper/class-inteaili-simple-html-dom.php';
		require_once INTEAILI_PATH . 'includes/woo/class-inteaili-woocommerce.php';
		require_once INTEAILI_PATH . 'includes/wp-cron/class-inteaili-wp-cron.php';
		require_once INTEAILI_PATH . 'includes/class-inteaili-system-status.php';
		require_once INTEAILI_PATH . 'public/class-inteaili-public.php';
	}

	/**
	 * Run the main plugin execution class.
	 *
	 * @since    1.0.0
	 */
	private static function run_plugin() {
		$plugin = new INTEAILI_Main( INTEAILI_NAME, INTEAILI_SLUG, INTEAILI_VERSION );
		$plugin->run();
	}

	/**
	 * Fired during plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		require_once INTEAILI_PATH . 'includes/class-inteaili-activator.php';
		INTEAILI_Activator::activate( INTEAILI_SLUG );
	}

	/**
	 * Fired during plugin deactivation.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		require_once INTEAILI_PATH . 'includes/class-inteaili-deactivator.php';
		INTEAILI_Deactivator::deactivate( INTEAILI_SLUG );
	}

	/**
	 * Declare WooCommerce High-Performance Order Storage (HPOS) compatibility.
	 *
	 * @since 1.0.1
	 */
	public static function declare_woo_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', INTEAILI_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', INTEAILI_FILE, true );
		}
	}
}

/**
 * Register Hooks.
 */
register_activation_hook( __FILE__, array( 'INTEAILI_Bootstrap', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'INTEAILI_Bootstrap', 'deactivate' ) );

add_action( 'before_woocommerce_init', array( 'INTEAILI_Bootstrap', 'declare_woo_compatibility' ) );
add_action( 'plugins_loaded', array( 'INTEAILI_Bootstrap', 'init' ) );