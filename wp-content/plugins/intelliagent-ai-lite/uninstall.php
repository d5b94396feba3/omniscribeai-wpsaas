<?php
/**
 * Uninstall IntelliAgent AI Lite
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 */

// Exit if uninstall not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Get the slug from the plugin basename.
 * When uninstalling, WP_UNINSTALL_PLUGIN contains the path to the main file.
 */

$inteaili_plugin_slug = dirname( plugin_basename( __FILE__ ) );

// Validation: Ensure the slug is not empty and is not a single dot (current dir).
if ( ! empty( $inteaili_plugin_slug ) && '.' !== $inteaili_plugin_slug ) {

	$inteaili_uninstaller_path = plugin_dir_path( __FILE__ ) . 'includes/class-inteaili-uninstaller.php';

	if ( file_exists( $inteaili_uninstaller_path ) ) {
		require_once $inteaili_uninstaller_path;

		// Execute the cleanup logic using the dynamically discovered slug.
		if ( class_exists( 'INTEAILI_Uninstaller' ) ) {
			INTEAILI_Uninstaller::uninstall();
		}
	}
} else {
    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( 'IntelliAgent AI Lite: Uninstall failed - Could not determine plugin slug.' );
}
