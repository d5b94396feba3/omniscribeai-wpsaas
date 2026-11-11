<?php
/**
 * Moove_License_Manager File Doc Comment
 *
 * @category Moove_License_Manager
 * @package   gdpr-cookie-compliance
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Moove_License_Manager Class Doc Comment
 *
 * @category Class
 * @package  Moove_License_Manager
 * @author   Moove Agency
 */
class Moove_GDPR_License_Manager {
	/**
	 * Construct function
	 */
	public function __construct() {

	}
	/**
	 * Bulk activate licence on MultiSites
	 */
	public static function gdpr_msba_bulk_activate_ajx() {
		$licence_key      = isset( $_POST['licence_key'] ) ? sanitize_text_field( wp_unslash( $_POST['licence_key'] ) ) : '';
		$blog_id          = isset( $_POST['blog_id'] ) ? intval( wp_unslash( $_POST['blog_id'] ) ) : false;
		$nonce            = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
		$action           = 'activate';
		$validate_license = array(
			'valid'   => false,
			'key'     => $licence_key,
			'message' => $nonce,
		);

		if ( $blog_id && $licence_key && class_exists( 'Moove_GDPR_License_Manager' ) && wp_verify_nonce( $nonce, 'gdpr_tab_licence_bulk' ) && current_user_can( 'manage_options' ) && function_exists( 'switch_to_blog' ) && is_multisite() && is_admin() ) :
			switch_to_blog( $blog_id );
			$licence_manager = new Moove_GDPR_License_Manager();

			$validate_license = $licence_manager->validate_license( $licence_key, 'gdpr', 'activate' );
			if ( $validate_license && isset( $validate_license['valid'] ) && true === $validate_license['valid'] ) :
				$plugin_token = isset( $validate_license['data'] ) && isset( $validate_license['data']['download_token'] ) && $validate_license['data']['download_token'] ? $validate_license['data']['download_token'] : false;

				$gdpr_default_content = new Moove_GDPR_Content();
				$option_key           = $gdpr_default_content->moove_gdpr_get_key_name();

				update_option(
					$option_key,
					array(
						'key'        => $validate_license['key'],
						'activation' => $validate_license['data']['today'],
					)
				);
				// VALID.
				$messages = isset( $validate_license['message'] ) && is_array( $validate_license['message'] ) ? implode( '<br>', $validate_license['message'] ) : '';

				if ( $plugin_token && 'activate' === $action ) :
						$plugin_slug = $licence_manager->get_add_on_plugin_slug();

					if ( $plugin_slug ) :
						if ( $licence_manager->is_plugin_installed( $plugin_slug ) ) {
							$installed = true;
						} else {
							$installed = $licence_manager->install_plugin( $plugin_token );
						}
						if ( ! is_wp_error( $installed ) && $installed ) {
							$activate = activate_plugin( $plugin_slug );
						}
							endif;
					endif;
				endif;

			restore_current_blog();
	endif;
		echo json_encode( $validate_license ); // phpcs:ignore
		die();
	}

	/**
	 * Licence validation
	 *
	 * @param string $license_key Licence key.
	 * @param string $type Type.
	 * @param string $action Action.
	 */
	public function validate_license( $license_key = false, $type = 'gdpr', $action = 'check' ) {
		$content       = new Moove_GDPR_Content();
		$license_token = $content->get_license_token();
		$request_url   = MOOVE_SHOP_URL . "/wp-json/license-manager/v1/validate_licence/?license_key=$license_key&license_token=$license_token&license_type=$type&license_action=$action";

		$response = wp_remote_get(
			$request_url,
			array(
				'timeout'     => 40,
				'httpversion' => '1.1',
			)
		);

		$status = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		$data   = is_wp_error( $response ) ? false : json_decode( wp_remote_retrieve_body( $response ), true );

		// The licence endpoint always answers 200, so a 5xx comes from the shop or its edge (e.g. Cloudflare 522), not from this site.
		if ( $status >= 500 ) {
			return $this->get_server_unavailable_response( $license_key, $action, $status );
		}

		if ( ! $data ) {
			$error_message = is_wp_error( $response ) ? implode( '<br />', array_map( 'esc_html', $response->get_error_messages() ) ) : 'Unexpected response from the licence server (HTTP ' . $status . ').';
			return array(
				'valid'   => false,
				'key'     => $license_key,
				'message' => array(
					'We cannot activate the licence due to errors with the setup of your website and/or your hosting.',
					'<strong>' . $error_message . '</strong>',
					'Once you resolve the issues, you will be able to activate the licence. You can also <a href="https://support.mooveagency.com/forum/gdpr-cookie-compliance/" target="_blank" class="error_admin_link">contact our support</a> if you need any additional assistance.',
				),
			);
		}

		return $data;
	}

	/**
	 * Response used when the licence server itself is down
	 *
	 * @param string $license_key Licence key.
	 * @param string $action Action.
	 * @param int    $status HTTP status code.
	 */
	private function get_server_unavailable_response( $license_key, $action, $status ) {
		$support_link = '<a href="https://support.mooveagency.com/forum/gdpr-cookie-compliance/" target="_blank" class="error_admin_link">contact our support</a>';

		if ( 'activate' === $action ) :
			$summary = 'We could not activate your licence because our licence server is temporarily unavailable. This is not caused by your website or your hosting.';
			$advice  = 'Please try again later. If the problem persists, please ' . $support_link . '.';
		elseif ( 'deactivate' === $action ) :
			$summary = 'Your licence has been deactivated on this website, but our licence server is temporarily unavailable, so the activation could not be released on our side.';
			$advice  = 'This key may still count towards your activation limit. If you need to use it on another website, please ' . $support_link . '.';
		else :
			$summary = 'We could not verify your licence because our licence server is temporarily unavailable. This is not caused by your website or your hosting.';
			$advice  = 'Your licence remains active on this website and your premium features are not affected. Please check back later, or ' . $support_link . ' if the problem persists.';
		endif;

		return array(
			'valid'   => false,
			'key'     => $license_key,
			'type'    => 'server_unavailable',
			'message' => array(
				$summary,
				'<strong>Error code: HTTP ' . (int) $status . '</strong>',
				$advice,
			),
		);
	}

	/**
	 * Plugin add-on slug
	 */
	public function get_add_on_plugin_slug() {
		$slug = false;
		if ( function_exists( 'moove_gdpr_addon_get_plugin_dir' ) ) :
			// Folder name only: plugins_url() and WP_PLUGIN_URL can differ in scheme (cron, proxies), so stripping the URL prefix is unreliable.
			$slug = basename( untrailingslashit( moove_gdpr_addon_get_plugin_dir() ) ) . '/moove-gdpr-addon.php';
		else :
			if ( ! function_exists( 'get_plugins' ) ) :
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			endif;
			$all_plugins = get_plugins();
			if ( is_array( $all_plugins ) ) :
				foreach ( $all_plugins as $plugin_slug => $plugin_details ) :
					if ( isset( $plugin_details['TextDomain'] ) && 'gdpr-cookie-compliance-addon' === $plugin_details['TextDomain'] && is_plugin_active( $plugin_slug ) ) :
						$slug = $plugin_slug;
					endif;
				endforeach;
			endif;
			$slug = $slug ? $slug : 'gdpr-cookie-compliance-addon/moove-gdpr-addon.php';
		endif;
		return $slug;
	}

	/**
	 * Add-on download actions
	 *
	 * @param string $license_key Licence key.
	 * @param string $action Action.
	 */
	public function get_premium_add_on( $license_key = false, $action = 'check' ) {
		$validate_license = $this->validate_license( $license_key, 'gdpr', $action );
		if ( $validate_license && isset( $validate_license['valid'] ) && true === $validate_license['valid'] ) :
			$plugin_token     = isset( $validate_license['data'] ) && isset( $validate_license['data']['download_token'] ) && $validate_license['data']['download_token'] ? $validate_license['data']['download_token'] : false;
			$is_valid_license = true;
			if ( $plugin_token && 'activate' === $action ) :
				$plugin_slug = $this->get_add_on_plugin_slug();
				add_filter( 'upgrader_source_selection', array( &$this, 'upgrader_source_selection' ), 10, 4 );
				if ( $plugin_slug ) :
					if ( $this->is_plugin_installed( $plugin_slug ) ) {
						$this->upgrade_plugin( $plugin_slug );
						$installed = true;
					} else {
						$installed = $this->install_plugin( $plugin_token );
					}
					if ( ! is_wp_error( $installed ) && $installed ) {
						$activate = activate_plugin( $plugin_slug );
					}
				endif;
				remove_filter( 'upgrader_source_selection', array( &$this, 'upgrader_source_selection' ), 10, 4 );
			endif;
		endif;
		return $validate_license;
	}

	/**
	 * Rename the plugin folder
	 *
	 * @param string $source Source.
	 * @param string $remote_source Remote Source.
	 * @param object $upgrader Upgrader.
	 * @param string $hook_extra Extra hooks.
	 */
	public function upgrader_source_selection( $source, $remote_source, $upgrader, $hook_extra = null ) {
		global $wp_filesystem;
		$plugin      = isset( $hook_extra['plugin'] ) ? $hook_extra['plugin'] : false;
		$plugin_slug = $this->get_add_on_plugin_slug();
		$temp_slug   = basename( trailingslashit( $source ) );
		$plugin_slug = explode( '/', $plugin_slug );
		$plugin_slug = isset( $plugin_slug[0] ) && $plugin_slug[0] ? $plugin_slug[0] : 'gdpr-cookie-compliance-addon';
		if ( $temp_slug !== $plugin_slug ) :
			$new_source = trailingslashit( $remote_source );
			$new_source = str_replace( $temp_slug, $plugin_slug, $new_source );
			$wp_filesystem->move( $source, $new_source );
			return trailingslashit( $new_source );
		endif;
		return $source;
	}

	/**
	 * Plugin deactivate
	 *
	 * @param string $license_key Licence key.
	 */
	public function premium_deactivate( $license_key = false ) {
		$validate_license = $this->validate_license( $license_key, 'gdpr', 'deactivate' );
		if ( $validate_license && isset( $validate_license['valid'] ) && true === $validate_license['valid'] ) :
			$plugin_slug = $this->get_add_on_plugin_slug();
			if ( $plugin_slug ) :
				if ( $this->is_plugin_installed( $plugin_slug ) ) :
					deactivate_plugins( plugin_basename( $plugin_slug ) );
					$deactivated = true;
				endif;
			endif;
		endif;
		return $validate_license;
	}

	/**
	 * Plugin installed
	 *
	 * @param string $slug Slug.
	 */
	public function is_plugin_installed( $slug = false ) {
		if ( function_exists( 'moove_gdpr_addon_get_plugin_dir' ) ) :
			return true;
		endif;
		if ( $slug ) :
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$all_plugins = get_plugins();
			if ( ! empty( $all_plugins[ $slug ] ) ) :
				return true;
			else :
				return false;
			endif;
		endif;
		return false;
	}

	/**
	 * Plugin install
	 *
	 * @param string $plugin_token Plugin token.
	 */
	public function install_plugin( $plugin_token ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();
		$upgrader  = new Plugin_Upgrader();
		$installed = $upgrader->install( $plugin_token );
		return $installed;
	}

	/**
	 * Upgrade plugin
	 *
	 * @param string $plugin_slug Plugin slug.
	 */
	public function upgrade_plugin( $plugin_slug ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();
		$upgrader = new Plugin_Upgrader();
		$upgraded = $upgrader->upgrade( $plugin_slug );
		return $upgraded;
	}


}
new Moove_GDPR_License_Manager();
