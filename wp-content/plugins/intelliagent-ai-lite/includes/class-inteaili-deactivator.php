<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the deactivation of the plugin.
 *
 * This class handles the cleanup of temporary data, transients, and
 * scheduled cron events when the plugin is deactivated.
 */
class INTEAILI_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Triggers the cleanup process and flushes rewrite rules to ensure
	 * a clean state upon deactivation.
	 *
	 * @since  1.0.0
	 * @param  string $plugin_slug The plugin text domain/slug.
	 * @return void
	 */
	public static function deactivate( $plugin_slug ) {
		self::cleanup_temp_data( $plugin_slug );

		flush_rewrite_rules();
	}

	/**
	 * Clean up temporary data and scheduled tasks.
	 *
	 * Removes transients and clears scheduled cron events associated
	 * with the plugin.
	 *
	 * @since  1.0.0
	 * @param  string $plugin_slug The plugin text domain/slug.
	 * @return void
	 */
	private static function cleanup_temp_data( $plugin_slug ) {
		$safe_slug = sanitize_key( $plugin_slug );

		delete_transient( $safe_slug . '_temp_data' );

		if ( file_exists( INTEAILI_PATH . 'includes/wp-cron/class-inteaili-wp-cron.php' ) ) {
			require_once INTEAILI_PATH . 'includes/wp-cron/class-inteaili-wp-cron.php';
			if ( class_exists( 'INTEAILI_WP_Cron' ) ) {
				$cron = new INTEAILI_WP_Cron( INTEAILI_SLUG );
				$cron->clear_schedule();
			}
		}

		wp_clear_scheduled_hook( $safe_slug . '_daily_event' );
	}
}
