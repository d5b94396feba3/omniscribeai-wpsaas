<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The core plugin class.
 *
 * This is the main orchestrator of the plugin. It initializes the loader
 * and defines all action and filter hooks for both admin and public contexts.
 *
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 * @author     IntelliAgent Team
 */
class INTEAILI_Main {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      INTEAILI_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The string used to uniquely identify this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version        The current version of the plugin.
	 */
	protected $version;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_slug    The unique identifier of this plugin.
	 */
	protected $plugin_slug;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name    The name of the plugin.
	 * @param    string $plugin_slug    The plugin text domain/slug.
	 * @param    string $version        The current version of the plugin.
	 */
	public function __construct( $plugin_name, $plugin_slug, $version ) {
		$this->plugin_name = $plugin_name;
		$this->plugin_slug = $plugin_slug;
		$this->version     = $version;
		$this->loader      = new INTEAILI_Loader();
		$this->define_hooks();
	}

	/**
	 * Register all of the hooks related to the functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   void
	 */
	private function define_hooks() {
		$this->loader->add_action( 'init', $this, 'load_textdomain' );

		if ( class_exists( 'INTEAILI_WP_Cron' ) ) {
			$cron = new INTEAILI_WP_Cron( $this->plugin_slug );
			$this->loader->add_action( $cron->hook, $cron, 'run_automated_sync' );
		}

		if ( is_admin() ) {
			$admin = new INTEAILI_Admin( $this->plugin_slug, $this->version );

			$this->loader->add_action( 'admin_notices', $admin, 'display_activation_notices' );
			$this->loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );
			$this->loader->add_action( 'admin_init', $admin, 'register_settings' );
			$this->loader->add_filter( 'allowed_redirect_hosts', $admin, 'whitelist_external_host' );
			$this->loader->add_action( 'admin_init', $admin, 'handle_admin_redirects' );
			$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

			// Chat log actions.
			$this->loader->add_action( 'wp_ajax_inteaili_delete_chat_log', $admin, 'handle_delete_chat_ajax' );
			$this->loader->add_action( 'wp_ajax_inteaili_export_chat_log', $admin, 'handle_export_chat_ajax' );

			// sync actions.
			$this->loader->add_action( 'wp_ajax_inteaili_sync_website_knowledge', $admin, 'handle_sync_website_ajax' );

			// Feedback actions.
			$this->loader->add_action( 'wp_ajax_inteaili_get_feedback', $admin, 'handle_get_feedback_ajax' );
			$this->loader->add_action( 'wp_ajax_inteaili_save_feedback', $admin, 'handle_save_feedback_ajax' );
			$this->loader->add_action( 'wp_ajax_inteaili_delete_feedback', $admin, 'handle_delete_feedback_ajax' );

			// FAQ actions.
			$this->loader->add_action( 'wp_ajax_inteaili_get_faq', $admin, 'handle_get_faq_ajax' );
			$this->loader->add_action( 'wp_ajax_inteaili_save_faq', $admin, 'handle_save_faq_ajax' );
			$this->loader->add_action( 'wp_ajax_inteaili_delete_faq', $admin, 'handle_delete_faq_ajax' );

			// Add Upgrade link to Plugin Action Links.
			$this->loader->add_filter( 'plugin_action_links_' . INTEAILI_BASENAME, $admin, 'inteaili_add_action_links' );

			// Add Docs/Support to Plugin Row Meta.
			$this->loader->add_filter( 'plugin_row_meta', $admin, 'inteaili_add_row_meta_links', 10, 2 );
		}

		// Public-facing hooks.
		$public = new INTEAILI_Public( $this->plugin_slug, $this->version );

		// Early session initialization
		$this->loader->add_action( 'init', $public, 'inteaili_init_visitor_cookie', 1 );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_footer', $public, 'inteaili_render_chat_ui' );

		// Public AJAX handlers.
		$this->loader->add_action( 'wp_ajax_nopriv_inteaili_send_chat_message', $public, 'handle_chat_message_ajax' );
		$this->loader->add_action( 'wp_ajax_inteaili_send_chat_message', $public, 'handle_chat_message_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_inteaili_submit_chat_feedback', $public, 'handle_chat_feedback_ajax' );
		$this->loader->add_action( 'wp_ajax_inteaili_submit_chat_feedback', $public, 'handle_chat_feedback_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_inteaili_clear_chat_history', $public, 'handle_clear_chat_ajax' );
		$this->loader->add_action( 'wp_ajax_inteaili_clear_chat_history', $public, 'handle_clear_chat_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_inteaili_get_recent_chats', $public, 'handle_fetch_recent_chats' );
		$this->loader->add_action( 'wp_ajax_inteaili_get_recent_chats', $public, 'handle_fetch_recent_chats' );
	}

	/**
	 * Load the plugin text domain for internationalization.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function load_textdomain() {
		// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound, WordPress.WP.DeprecatedFunctions.load_plugin_textdomainFound
		load_plugin_textdomain(
			$this->plugin_slug,
			false,
			dirname( plugin_basename( INTEAILI_FILE ) ) . '/languages/'
		);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function run() {
		$this->loader->run();
	}
}
