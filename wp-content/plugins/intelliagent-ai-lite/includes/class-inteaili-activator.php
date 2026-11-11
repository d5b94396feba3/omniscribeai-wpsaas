<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the activation of the plugin.
 *
 * This class handles the initialization of database tables and
 * option management when the plugin is activated.
 */
class INTEAILI_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Performs initialization tasks such as creating tables,
	 * adding default options, and flushing rewrite rules.
	 *
	 * @since  1.0.0
	 * @param  string $plugin_slug The plugin text domain/slug.
	 * @return void
	 */
	public static function activate( $plugin_slug ) {
		$safe_slug = sanitize_key( $plugin_slug );

		if ( ! get_option( 'inteaili_activated' ) ) {
			add_option( 'inteaili_activated', true );
		}

		if ( ! get_option( 'inteaili_version' ) ) {
			add_option( 'inteaili_version', INTEAILI_VERSION );
		}

		if ( ! class_exists( 'INTEAILI_DB_Manager' ) ) {
			$db_manager_path = INTEAILI_PATH . 'includes/db/class-inteaili-db-manager.php';
			if ( file_exists( $db_manager_path ) ) {
				require_once $db_manager_path;
			}
		}

		if ( class_exists( 'INTEAILI_DB_Manager' ) ) {
			INTEAILI_DB_Manager::create_table( $safe_slug );
		}

		flush_rewrite_rules();
	}
}
