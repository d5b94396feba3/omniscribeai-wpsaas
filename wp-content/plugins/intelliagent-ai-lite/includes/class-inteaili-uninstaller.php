<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the complete removal of plugin data upon uninstallation.
 *
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 * @author     IntelliAgent Team
 */
class INTEAILI_Uninstaller {

	/**
	 * Plugin uninstall - COMPLETE removal when plugin is deleted.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function uninstall() {
		self::drop_database_table();
		self::cleanup_options();
		self::cleanup_temp_data();
	}

	/**
	 * Drop the database tables when plugin is uninstalled.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private static function drop_database_table() {
		global $wpdb;

		$tbl_prefix = 'inteaili';

		$inteaili_tables = array(
			$wpdb->prefix . $tbl_prefix . '_chat_logs',
			$wpdb->prefix . $tbl_prefix . '_feedback',
			$wpdb->prefix . $tbl_prefix . '_knowledge_base',
			$wpdb->prefix . $tbl_prefix . '_faqs',
		);

		foreach ( $inteaili_tables as $inteaili_table_name ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$inteaili_check = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $inteaili_table_name ) ) );

			if ( $inteaili_check === $inteaili_table_name ) {
				// phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$wpdb->query( 'DROP TABLE IF EXISTS `' . $inteaili_table_name . '`' );
			}
		}
	}

	/**
	 * Clean up plugin options from the database.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private static function cleanup_options() {
		delete_option( 'inteaili_display_name' );
		delete_option( 'inteaili_welcome_msg' );
		delete_option( 'inteaili_placeholder' );
		delete_option( 'inteaili_system_language' );
		delete_option( 'inteaili_logo_id' );
		delete_option( 'inteaili_bubble_icon' );
		delete_option( 'inteaili_primary_color' );
		delete_option( 'inteaili_ai_persona' );
		delete_option( 'inteaili_llm_model' );
		delete_option( 'inteaili_openai_api_key' );
		delete_option( 'inteaili_openai_model' );
		delete_option( 'inteaili_openai_temperature' );
		delete_option( 'inteaili_gemini_api_key' );
		delete_option( 'inteaili_gemini_model' );
		delete_option( 'inteaili_gemini_temperature' );
		delete_option( 'inteaili_theme_mode' );
		delete_option( 'inteaili_show_branding' );
		delete_option( 'inteaili_activated' );

		delete_option( 'inteaili_ai_engine_settings' );
		delete_option( 'inteaili_version' );
		delete_option( 'inteaili_installed_time' );
		delete_option( 'inteaili_last_cron_sync' );
	}

	/**
	 * Clean up temporary transients.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private static function cleanup_temp_data() {
		delete_transient( 'inteaili_temp_data' );
		delete_transient( 'inteaili_sync_lock' );
	}
}
