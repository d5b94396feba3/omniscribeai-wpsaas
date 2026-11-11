<?php
/**
 * Automated Cron Sync Logic for the IntelliAgent AI Lite plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class INTEAILI_WP_Cron
 *
 * Handles the scheduling and execution of daily automated knowledge base
 * synchronization tasks via the WordPress Cron API.
 */
class INTEAILI_WP_Cron {

	/**
	 * The name of the cron hook.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $hook;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The ID of this plugin.
	 */
	private $plugin_slug;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_slug The slug of the plugin.
	 */
	public function __construct( $plugin_slug ) {
		$this->plugin_slug = $plugin_slug;
		$this->hook        = sanitize_key( $plugin_slug ) . '_daily_sync_event';
	}

	/**
	 * Executes the automated knowledge base synchronization.
	 *
	 * Includes a transient lock to prevent multiple sync processes from
	 * running concurrently.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function run_automated_sync() {

		$lock_key = 'inteaili_sync_lock';

		// Check if a sync is already in progress.
		if ( get_transient( $lock_key ) ) {
			return;
		}

		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}

		// Lock for 1 hour to avoid concurrent overlapping syncs.
		set_transient( $lock_key, 'lock', HOUR_IN_SECONDS );

		if ( function_exists( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
			@set_time_limit( 300 );
		}

		$scraper = new INTEAILI_Simple_HTML_DOM();
		$db      = new INTEAILI_DB_Manager( $this->plugin_slug );

		$scraped_pages = $scraper->sync_website_content();

		if ( ! empty( $scraped_pages ) ) {
			foreach ( $scraped_pages as $page ) {
				// Update Knowledge Base with content.
				$db->update_knowledge_base( esc_url_raw( $page['url'] ), $page['content'] );
			}

			update_option( 'inteaili_last_cron_sync', current_time( 'timestamp' ) );
		}

		delete_transient( $lock_key );
	}

	/**
	 * Schedules the daily sync event if it isn't already scheduled.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function schedule_sync() {
		if ( ! wp_next_scheduled( $this->hook ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', $this->hook );
		}
	}

	/**
	 * Clears the scheduled sync event from WordPress Cron.
	 *
	 * Usually called upon plugin deactivation.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function clear_schedule() {
		$wp_next = wp_next_scheduled( $this->hook );
		if ( $wp_next ) {
			wp_unschedule_event( $wp_next, $this->hook );
		}
	}
}
