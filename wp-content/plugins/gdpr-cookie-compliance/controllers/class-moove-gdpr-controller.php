<?php
/**
 * Moove_Controller File Doc Comment
 *
 * @category Moove_Controller
 * @package   gdpr-cookie-compliance
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Moove_Controller Class Doc Comment
 *
 * @category Class
 * @package  Moove_Controller
 * @author   Moove Agency
 */
class Moove_GDPR_Controller {
	/**
	 * Construct function
	 */
	public function __construct() {
	}

	/**
	 * Custom Editor CSS added to GDPR plugin WYSIWYG editors
	 *
	 * @return void
	 */
	public static function moove_gdpr_add_editor_styles() {
		add_editor_style( moove_gdpr_get_plugin_directory_url() . 'dist/styles/custom-editor-style.css' );
	}

	/**
	 * Checking if database exists
	 *
	 * @return bool
	 */
	public static function moove_gdpr_check_database() {
		$has_database = get_option( 'gdpr_cc_has_database' ) ? true : false;
		return $has_database;
	}

	/**
	 * JavaScript localization script
	 */
	public static function moove_gdpr_localize_scripts() {
		header( 'Content-Type: application/json; charset=utf-8' );

		/**
		 * Whether to cache the whole response per anonymised REMOTE_ADDR.
		 *
		 * Add-ons that cache locations themselves turn this off: the key is
		 * the connecting address, which behind a proxy is not the visitor's,
		 * so one visitor's answer would be served to everyone behind it.
		 */
		$use_cache = apply_filters( 'gdpr_cc_geo_response_cache', true );

		if ( ! $use_cache ) {
			$content_cnt = new Moove_GDPR_Content();
			echo json_encode( $content_cnt->moove_gdpr_get_localize_scripts() ); // phpcs:ignore
			die();
		}

		// Cache per anonymised IP to avoid repeated geo-lookup API calls.
		// wp_privacy_anonymize_ip() masks the last octet (IPv4) or last 80 bits (IPv6)
		// so the key never stores a full identifiable IP address.
		$remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ip_anon   = function_exists( 'wp_privacy_anonymize_ip' ) ? wp_privacy_anonymize_ip( $remote_ip ) : '';
		$cache_key = 'gdpr_geo_' . md5( $ip_anon );

		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			echo $cached; // phpcs:ignore
			die();
		}

		$content_cnt = new Moove_GDPR_Content();
		$data        = json_encode( $content_cnt->moove_gdpr_get_localize_scripts() ); // phpcs:ignore
		set_transient( $cache_key, $data, HOUR_IN_SECONDS );
		echo $data; // phpcs:ignore
		die();
	}

	/**
	 * Reading plugin statistics from WordPress.org
	 * - star rating
	 * - downloads & active installations
	 *
	 * @param string $plugin_slug Plugin slug.
	 */
	public function get_gdpr_plugin_details( $plugin_slug = '' ) {
		$plugin_return   = false;
		$wp_repo_plugins = '';
		$wp_response     = '';
		$wp_version      = get_bloginfo( 'version' );
		$transient       = get_transient( 'plugin_info_' . $plugin_slug );

		if ( $transient ) :
			$plugin_return = $transient;
		else :
			if ( $plugin_slug && $wp_version > 3.8 ) :
				$url  = 'http://api.wordpress.org/plugins/info/1.2/';
				$args = array(
					'author' => 'MooveAgency',
					'fields' => array(
						'downloaded'      => true,
						'active_installs' => true,
						'ratings'         => true,
					),
				);

				$url = add_query_arg(
					array(
						'action'  => 'query_plugins',
						'request' => $args,
					),
					$url
				);

				$http_url = $url;
				$ssl      = wp_http_supports( array( 'ssl' ) );
				if ( $ssl ) :
					$url = set_url_scheme( $url, 'https' );
			endif;

				$http_args = array(
					'timeout'    => 30,
					'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
				);
				$request   = wp_remote_get( $url, $http_args );

				if ( ! is_wp_error( $request ) ) :
					$response = json_decode( wp_remote_retrieve_body( $request ), true );
					if ( is_array( $response ) ) :
						$wp_repo_plugins = isset( $response['plugins'] ) && is_array( $response['plugins'] ) ? $response['plugins'] : array();
						foreach ( $wp_repo_plugins as $plugin_details ) :
							$plugin_details = (object) $plugin_details;
							if ( isset( $plugin_details->slug ) && $plugin_slug === $plugin_details->slug ) :
								$plugin_return = $plugin_details;
								set_transient( 'plugin_info_' . $plugin_slug, $plugin_return, 12 * HOUR_IN_SECONDS );
							endif;
						endforeach;
					endif;
				endif;
			endif;
		endif;
		return $plugin_return;
	}

	/**
	 * CSS minification for inlined CSS styles
	 *
	 * @param  string $input Inlined styles.
	 * @return string        Minified styles.
	 */
	public function moove_gdpr_minify_css( $input ) {
		if ( trim( $input ) === '' ) {
			return $input;
		}
		return preg_replace(
			array(
				// Remove comment(s).
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
				// Remove unused white-space(s).
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
				// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`.
				'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
				// Replace `:0 0 0 0` with `:0`.
				'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
				// Replace `background-position:0` with `background-position:0 0`.
				'#(background-position):0(?=[;\}])#si',
				// Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space.
				'#(?<=[\s:,\-])0+\.(\d+)#s',
				// Minify string value.
				'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
				'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
				// Minify HEX color code.
				'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
				// Replace `(border|outline):none` with `(border|outline):0`.
				'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
				// Remove empty selector(s).
				'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
			),
			array(
				'$1',
				'$1$2$3$4$5$6$7',
				'$1',
				':0',
				'$1:0 0',
				'.$1',
				'$1$3',
				'$1$2$4$5',
				'$1$2$3',
				'$1:0',
				'$1$2',
			),
			$input
		);
	}

	/**
	 * Inline styles based on the colours selected in the options page
	 *
	 * The rules that use these colours are static, in assets/styles/modules/_branding.scss
	 * (compiled into the main stylesheet). Only the values are printed inline, as custom properties,
	 * plus the rule that keeps the settings modal hidden when the main stylesheet is blocked.
	 *
	 * @param string $primary_colour Primary Color.
	 * @param string $secondary_colour Secondary Color.
	 * @param string $button_bg Button Background Color.
	 * @param string $button_hover_bg Button Hover Background Color.
	 * @param string $button_font Button Font Color.
	 * @param string $font_family Font Family.
	 */
	public function get_minified_styles( $primary_colour, $secondary_colour, $button_bg, $button_hover_bg, $button_font, $font_family ) {
		ob_start();
		?>
		#moove_gdpr_cookie_modal,
		#moove_gdpr_cookie_info_bar,
		.gdpr_cookie_settings_shortcode_content {
			font-family: <?php echo gdpr_sanitize_css_font_family( $font_family ); // phpcs:ignore ?>;
		}
		:root {
			--moove-gdpr-primary-colour: <?php echo esc_attr( $primary_colour ); ?>;
			--moove-gdpr-button-bg: <?php echo esc_attr( $button_bg ); ?>;
			--moove-gdpr-button-hover-bg: <?php echo esc_attr( $button_hover_bg ); ?>;
			--moove-gdpr-button-font: <?php echo esc_attr( $button_font ); ?>;
		}
		<?php
		$input = apply_filters( 'moove_gdpr_inline_styles', ob_get_clean(), $primary_colour, $secondary_colour, $button_bg, $button_hover_bg, $button_font );

		// The modal is server-rendered as <dialog open>, so it is visible unless this rule applies.
		// It must stay inline, not only in the main stylesheet: cookie-notice filter lists
		// (EasyList Cookie in Brave, uBlock Origin, Malwarebytes) block everything under
		// /plugins/gdpr-cookie-compliance/, and the modal then renders as an unstyled box in the page.
		// Added after the filter so that replacing the filtered styles cannot drop it.
		$input .= '#moove_gdpr_cookie_modal.gdpr_lightbox-hide{display:none}';

		$gdpr_controller = new Moove_GDPR_Controller();
		return ! is_admin() || wp_doing_ajax() ? $gdpr_controller->moove_gdpr_minify_css( $input ) : '';
	}

	/**
	 * GDPR Modal Main content
	 *
	 * @return void
	 */
	public static function moove_gdpr_cookie_popup_modal() {
		if ( ! is_admin() ) :
			// FLOATING BUTTON.
			$content = gdpr_get_module( 'floating-button' );
			apply_filters( 'gdpr_cc_keephtml', $content, true );

			// MODAL CONTENT.
			$content = gdpr_get_module( 'modal-base' );
			apply_filters( 'gdpr_cc_keephtml', $content, true );
		endif;
	}

	/**
	 * GDPR Cookie info bar with settings icon
	 *
	 * @return void
	 */
	public static function moove_gdpr_cookie_popup_info() {
		if ( ! is_admin() ) :
			$content = gdpr_get_module( 'infobar-base' );
			apply_filters( 'gdpr_cc_keephtml', $content, true );
		endif;
	}

	/**
	 * GDPR Get scripts
	 */
	public static function moove_gdpr_get_static_scripts() {
		$strict               = true;
		$thirdparty           = true;
		$advanced             = true;

		$gdpr_default_content = new Moove_GDPR_Content();
		$wp_lang              = $gdpr_default_content->moove_gdpr_get_wpml_lang();

		$gdpr_cache_enabled  	= apply_filters( 'gdpr_transient_cache_enabled', true ); 
		$transient_key = 'gdpr_cookie_cache' . $wp_lang . MOOVE_GDPR_VERSION;
		$transient     = $gdpr_cache_enabled ? apply_filters( 'gdpr_cookie_script_cache', get_transient( $transient_key ) ) : array();

		if ( ! empty( $transient ) ) :
			$transient_from_cache = json_decode( $transient, true );
		else :
			$option_name   = $gdpr_default_content->moove_gdpr_get_option_name();
			$modal_options = get_option( $option_name );

			$cache_array = array(
				'strict'  => array(
					'header' => '',
					'body'   => '',
					'footer' => '',
				),
				'thirdparty'  => array(
					'header' => '',
					'body'   => '',
					'footer' => '',
				),
				'advanced'    => array(
					'header' => '',
					'body'   => '',
					'footer' => '',
				),
				'performance' => array(
					'header' => '',
					'body'   => '',
					'footer' => '',
				),
				'preference'  => array(
					'header' => '',
					'body'   => '',
					'footer' => '',
				),
			);

			// STRICTLY NECESSARY - SCRIPT CACHE.


			ob_start();
			if ( isset( $modal_options['moove_gdpr_strictly_ccat_enable'] ) && intval( $modal_options['moove_gdpr_strictly_ccat_enable'] ) === 1 ) :
				$strict_scripts = isset( $modal_options['moove_gdpr_strictly_header_scripts'] ) && $modal_options['moove_gdpr_strictly_header_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_strictly_header_scripts'] ) : '';
				$strict_scripts = apply_filters( 'moove_gdpr_strictly_header_assets', $strict_scripts );
				apply_filters( 'gdpr_cc_keephtml', $strict_scripts, true );
			endif;
			$cache_array['strict']['header'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_strictly_ccat_enable'] ) && intval( $modal_options['moove_gdpr_strictly_ccat_enable'] ) === 1 ) :
				$strict_scripts = isset( $modal_options['moove_gdpr_strictly_body_scripts'] ) && $modal_options['moove_gdpr_strictly_body_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_strictly_body_scripts'] ) : '';
				$strict_scripts = apply_filters( 'moove_gdpr_strictly_body_assets', $strict_scripts );
				apply_filters( 'gdpr_cc_keephtml', $strict_scripts, true );
			endif;
			$cache_array['strict']['body'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_strictly_ccat_enable'] ) && intval( $modal_options['moove_gdpr_strictly_ccat_enable'] ) === 1 ) :
				$strict_scripts = isset( $modal_options['moove_gdpr_strictly_footer_scripts'] ) && $modal_options['moove_gdpr_strictly_footer_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_strictly_footer_scripts'] ) : '';
				$strict_scripts = apply_filters( 'moove_gdpr_strictly_footer_assets', $strict_scripts );
				apply_filters( 'gdpr_cc_keephtml', $strict_scripts, true );
			endif;
			$cache_array['strict']['footer'] .= ob_get_clean();

			// THIRD PARTY - SCRIPT CACHE.
			ob_start();
			if ( isset( $modal_options['moove_gdpr_third_party_cookies_enable'] ) && intval( $modal_options['moove_gdpr_third_party_cookies_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_third_party_header_scripts'] ) && $modal_options['moove_gdpr_third_party_header_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_third_party_header_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_third_party_header_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['thirdparty']['header'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_third_party_cookies_enable'] ) && intval( $modal_options['moove_gdpr_third_party_cookies_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_third_party_body_scripts'] ) && $modal_options['moove_gdpr_third_party_body_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_third_party_body_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_third_party_body_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['thirdparty']['body'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_third_party_cookies_enable'] ) && intval( $modal_options['moove_gdpr_third_party_cookies_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_third_party_footer_scripts'] ) && $modal_options['moove_gdpr_third_party_footer_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_third_party_footer_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_third_party_footer_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['thirdparty']['footer'] .= ob_get_clean();

			// ADVANCED - SCRIPT CACHE.
			ob_start();
			if ( isset( $modal_options['moove_gdpr_advanced_cookies_enable'] ) && intval( $modal_options['moove_gdpr_advanced_cookies_enable'] ) === 1 ) :
				$advanced_scripts = isset( $modal_options['moove_gdpr_advanced_cookies_header_scripts'] ) && $modal_options['moove_gdpr_advanced_cookies_header_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_advanced_cookies_header_scripts'] ) : '';
				$advanced_scripts = apply_filters( 'moove_gdpr_advanced_cookies_header_assets', $advanced_scripts );
				apply_filters( 'gdpr_cc_keephtml', $advanced_scripts, true );
			endif;
			$cache_array['advanced']['header'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_advanced_cookies_enable'] ) && intval( $modal_options['moove_gdpr_advanced_cookies_enable'] ) === 1 ) :
				$advanced_scripts = isset( $modal_options['moove_gdpr_advanced_cookies_body_scripts'] ) && $modal_options['moove_gdpr_advanced_cookies_body_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_advanced_cookies_body_scripts'] ) : '';
				$advanced_scripts = apply_filters( 'moove_gdpr_advanced_cookies_body_assets', $advanced_scripts );
				apply_filters( 'gdpr_cc_keephtml', $advanced_scripts, true );
			endif;
			$cache_array['advanced']['body'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_advanced_cookies_enable'] ) && intval( $modal_options['moove_gdpr_advanced_cookies_enable'] ) === 1 ) :
				$advanced_scripts = isset( $modal_options['moove_gdpr_advanced_cookies_footer_scripts'] ) && $modal_options['moove_gdpr_advanced_cookies_footer_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_advanced_cookies_footer_scripts'] ) : '';
				$advanced_scripts = apply_filters( 'moove_gdpr_advanced_cookies_footer_assets', $advanced_scripts );
				apply_filters( 'gdpr_cc_keephtml', $advanced_scripts, true );
			endif;
			$cache_array['advanced']['footer'] .= ob_get_clean();

			$cache_array = apply_filters( 'gdpr_cc_before_script_cache_set', $cache_array, $modal_options );

			$cache_json = json_encode( $cache_array, true ); // phpcs:ignore

			set_transient( $transient_key, $cache_json, 86400 );
			$transient_from_cache = $cache_array;
		endif;

		$scripts_array = array(
			'cache'  => ! empty( $transient ),
			'header' => '',
			'body'   => '',
			'footer' => '',
		);

		if ( true === $strict ) :
			$transient_from_cache = apply_filters( 'gdpr_lss_extension', $transient_from_cache, $wp_lang );
			$transient_from_cache = Moove_GDPR_Content::gdpr_append_gtmc2_consent_reassert( $transient_from_cache );
			if ( $thirdparty ) :
				if ( isset( $transient_from_cache['thirdparty'] ) ) :
					$scripts_array['thirdparty']['header'] = $transient_from_cache['thirdparty']['header'];
					$scripts_array['thirdparty']['body']   = $transient_from_cache['thirdparty']['body'];
					$scripts_array['thirdparty']['footer'] = $transient_from_cache['thirdparty']['footer'];
				endif;
			endif;

			if ( $strict ) :
				if ( isset( $transient_from_cache['strict'] ) ) :
					$scripts_array['strict']['header'] = $transient_from_cache['strict']['header'];
					$scripts_array['strict']['body']   = $transient_from_cache['strict']['body'];
					$scripts_array['strict']['footer'] = $transient_from_cache['strict']['footer'];
				endif;
			endif;

			if ( $advanced ) :
				if ( isset( $transient_from_cache['advanced'] ) ) :
					$scripts_array['advanced']['header'] = $transient_from_cache['advanced']['header'];
					$scripts_array['advanced']['body']   = $transient_from_cache['advanced']['body'];
					$scripts_array['advanced']['footer'] = $transient_from_cache['advanced']['footer'];
				endif;
			endif;
		endif;
		$scripts_array 	= apply_filters( 'gdpr_additional_cookie_cat_extension', $scripts_array, $transient_from_cache );

		$scripts_json 	= apply_filters( 'gdpr_filter_scripts_before_insert', json_encode( $scripts_array ) ); // phpcs:ignore


		return str_replace( '<script', '<script data-gdpr', $scripts_json );
	}

	/**
	 * AJAX function to display the allowed scripts from the plugin settings page
	 *
	 * This endpoint is intentionally nonce-free for full-page cache compatibility
	 * (WP Rocket, WPEngine, etc.). Returning the admin-configured scripts is read-only.
	 * The non-strict branch, however, clears the visitor's tracking cookies, which is a
	 * state-changing action — it is therefore gated to same-origin POST requests only so
	 * it cannot be triggered via CSRF (cross-site GET, <img>, or no-Referer navigation).
	 * Auth/commerce cookies are never in the deletion set (case-insensitive allowlist).
	 *
	 * @return void
	 */
	public static function moove_gdpr_get_scripts() {
		// Lightweight origin check — compatible with full-page caching.
		$referer     = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		$same_origin = $referer && wp_parse_url( $referer, PHP_URL_HOST ) === wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $referer && ! $same_origin ) :
			wp_send_json_error( 'Invalid origin', 403 );
		endif;
		// The cookie-clearing branch below mutates state; only allow it on a same-origin
		// POST (an empty/cross-site Referer or a bodyless GET must not reach that branch).
		$is_post = isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) );
		$strict      = isset( $_POST['strict'] ) && intval( $_POST['strict'] ) && 1 === intval( $_POST['strict'] ) ? true : false;
		$thirdparty  = isset( $_POST['thirdparty'] ) && intval( $_POST['thirdparty'] ) && 1 === intval( $_POST['thirdparty'] ) ? true : false;
		$advanced    = isset( $_POST['advanced'] ) && intval( $_POST['advanced'] ) && 1 === intval( $_POST['advanced'] ) ? true : false;
		// main.js has always posted these two, but they used to be dropped here, so
		// the premium Performance/Preferences scripts never shipped over AJAX - only
		// via the pre-localised path in moove_gdpr_get_static_scripts().
		$performance = isset( $_POST['performance'] ) && 1 === intval( $_POST['performance'] ) ? true : false;
		$preference  = isset( $_POST['preference'] ) && 1 === intval( $_POST['preference'] ) ? true : false;

		$wp_lang = isset( $_POST['wp_lang'] ) ? sanitize_text_field( wp_unslash( urlencode( $_POST['wp_lang'] ) ) ) : ''; // phpcs:ignore

		$gdpr_cache_enabled  	= apply_filters( 'gdpr_transient_cache_enabled', true );
		$transient_key = 'gdpr_cookie_cache' . $wp_lang . MOOVE_GDPR_VERSION;
		$transient     = $gdpr_cache_enabled ? apply_filters( 'gdpr_cookie_script_cache', get_transient( $transient_key ) ) : array();

		if ( ! empty( $transient ) ) :
			$transient_from_cache = json_decode( $transient, true );
		else :
			$gdpr_default_content = new Moove_GDPR_Content();
			$option_name          = $gdpr_default_content->moove_gdpr_get_option_name();
			$modal_options        = get_option( $option_name );

			$cache_array = array(
				'strict' => array(
					'header' => '',
					'body'   => '',
					'footer' => '',
				),
				'thirdparty' => array(
					'header' => '',
					'body'   => '',
					'footer' => '',
				),
				'advanced'   => array(
					'header' => '',
					'body'   => '',
					'footer' => '',
				),
			);

			// STRICTLY NECESSARY - SCRIPT CACHE.
			ob_start();
			if ( isset( $modal_options['moove_gdpr_strictly_ccat_enable'] ) && intval( $modal_options['moove_gdpr_strictly_ccat_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_strictly_header_scripts'] ) && $modal_options['moove_gdpr_strictly_header_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_strictly_header_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_strictly_header_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['strict']['header'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_strictly_ccat_enable'] ) && intval( $modal_options['moove_gdpr_strictly_ccat_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_strictly_body_scripts'] ) && $modal_options['moove_gdpr_strictly_body_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_strictly_body_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_strictly_body_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['strict']['body'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_strictly_ccat_enable'] ) && intval( $modal_options['moove_gdpr_strictly_ccat_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_strictly_footer_scripts'] ) && $modal_options['moove_gdpr_strictly_footer_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_strictly_footer_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_strictly_footer_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['strict']['footer'] .= ob_get_clean();

			// THIRD PARTY - SCRIPT CACHE.
			ob_start();
			if ( isset( $modal_options['moove_gdpr_third_party_cookies_enable'] ) && intval( $modal_options['moove_gdpr_third_party_cookies_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_third_party_header_scripts'] ) && $modal_options['moove_gdpr_third_party_header_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_third_party_header_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_third_party_header_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['thirdparty']['header'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_third_party_cookies_enable'] ) && intval( $modal_options['moove_gdpr_third_party_cookies_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_third_party_body_scripts'] ) && $modal_options['moove_gdpr_third_party_body_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_third_party_body_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_third_party_body_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['thirdparty']['body'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_third_party_cookies_enable'] ) && intval( $modal_options['moove_gdpr_third_party_cookies_enable'] ) === 1 ) :
				$third_party_scripts = isset( $modal_options['moove_gdpr_third_party_footer_scripts'] ) && $modal_options['moove_gdpr_third_party_footer_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_third_party_footer_scripts'] ) : '';
				$third_party_scripts = apply_filters( 'moove_gdpr_third_party_footer_assets', $third_party_scripts );
				apply_filters( 'gdpr_cc_keephtml', $third_party_scripts, true );
			endif;
			$cache_array['thirdparty']['footer'] .= ob_get_clean();

			// ADVANCED - SCRIPT CACHE.
			ob_start();
			if ( isset( $modal_options['moove_gdpr_advanced_cookies_enable'] ) && intval( $modal_options['moove_gdpr_advanced_cookies_enable'] ) === 1 ) :
				$advanced_scripts = isset( $modal_options['moove_gdpr_advanced_cookies_header_scripts'] ) && $modal_options['moove_gdpr_advanced_cookies_header_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_advanced_cookies_header_scripts'] ) : '';
				$advanced_scripts = apply_filters( 'moove_gdpr_advanced_cookies_header_assets', $advanced_scripts );
				apply_filters( 'gdpr_cc_keephtml', $advanced_scripts, true );
			endif;
			$cache_array['advanced']['header'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_advanced_cookies_enable'] ) && intval( $modal_options['moove_gdpr_advanced_cookies_enable'] ) === 1 ) :
				$advanced_scripts = isset( $modal_options['moove_gdpr_advanced_cookies_body_scripts'] ) && $modal_options['moove_gdpr_advanced_cookies_body_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_advanced_cookies_body_scripts'] ) : '';
				$advanced_scripts = apply_filters( 'moove_gdpr_advanced_cookies_body_assets', $advanced_scripts );
				apply_filters( 'gdpr_cc_keephtml', $advanced_scripts, true );
			endif;
			$cache_array['advanced']['body'] .= ob_get_clean();

			ob_start();
			if ( isset( $modal_options['moove_gdpr_advanced_cookies_enable'] ) && intval( $modal_options['moove_gdpr_advanced_cookies_enable'] ) === 1 ) :
				$advanced_scripts = isset( $modal_options['moove_gdpr_advanced_cookies_footer_scripts'] ) && $modal_options['moove_gdpr_advanced_cookies_footer_scripts'] ? maybe_unserialize( $modal_options['moove_gdpr_advanced_cookies_footer_scripts'] ) : '';
				$advanced_scripts = apply_filters( 'moove_gdpr_advanced_cookies_footer_assets', $advanced_scripts );
				apply_filters( 'gdpr_cc_keephtml', $advanced_scripts, true );
			endif;
			$cache_array['advanced']['footer'] .= ob_get_clean();

			$cache_array = apply_filters( 'gdpr_cc_before_script_cache_set', $cache_array, $modal_options );

			$cache_json = json_encode( $cache_array, true ); // phpcs:ignore

			set_transient( $transient_key, $cache_json, 86400 );
			$transient_from_cache = $cache_array;
		endif;

		$scripts_array = array(
			'cache'  => ! empty( $transient ),
			'header' => '',
			'body'   => '',
			'footer' => '',
		);

		if ( true === $strict ) :
			$transient_from_cache = apply_filters( 'gdpr_lss_extension', $transient_from_cache, $wp_lang );
			$transient_from_cache = Moove_GDPR_Content::gdpr_append_gtmc2_consent_reassert( $transient_from_cache );

			/*
			 * Mirrors the category list handled by moove_gdpr_get_static_scripts().
			 * 'strict' is intentionally absent: it is delivered by the pre-localised
			 * path, not this endpoint.
			 */
			$accepted_cats = array(
				'thirdparty'  => $thirdparty,
				'advanced'    => $advanced,
				'performance' => $performance,
				'preference'  => $preference,
			);

			foreach ( $accepted_cats as $_cat => $_accepted ) :
				if ( ! $_accepted || ! isset( $transient_from_cache[ $_cat ] ) ) :
					continue;
				endif;
				$scripts_array['header'] .= isset( $transient_from_cache[ $_cat ]['header'] ) ? $transient_from_cache[ $_cat ]['header'] : '';
				$scripts_array['body']   .= isset( $transient_from_cache[ $_cat ]['body'] ) ? $transient_from_cache[ $_cat ]['body'] : '';
				$scripts_array['footer'] .= isset( $transient_from_cache[ $_cat ]['footer'] ) ? $transient_from_cache[ $_cat ]['footer'] : '';
			endforeach;
		elseif ( $is_post && $same_origin ) :
			// Also drops our own consent cookie on reject when the site opts out of remembering it.
			self::moove_gdpr_expire_request_cookies( ! apply_filters( 'gdpr_cc_store_cookie_on_reject', true ) );
		endif;
		header( 'Content-Type: application/json; charset=utf-8' );
		$scripts_json = apply_filters( 'gdpr_filter_scripts_before_insert', json_encode( $scripts_array ) ); // phpcs:ignore
		echo str_replace( '<script', '<script data-gdpr', $scripts_json ); // phpcs:ignore
		die();
	}

	/**
	 * Removing all the cookies including www and non-www domains
	 */
	public static function moove_gdpr_remove_php_cookies() {
		$cookies_removed = array();
		$nonce    = isset( $_POST['security'] ) ? sanitize_key( wp_unslash( $_POST['security'] ) ) : false;
		$nonce    = $nonce ? $nonce : ( isset( $_GET['security'] ) ? sanitize_key( wp_unslash( $_GET['security'] ) ) : false );
		if ( $nonce && wp_verify_nonce( $nonce, 'gdpr-cookie-compliance' ) ) :
			$cookies_removed = self::moove_gdpr_expire_request_cookies();
		endif;
		echo json_encode( $cookies_removed ); // phpcs:ignore
	}

	/**
	 * Cookie names tags write on a parent domain rather than the site host - gtag and the
	 * Conversion Linker default to the registrable domain (.example.com), as does Meta Pixel.
	 * Matched as substrings, so prefixed variants (_ga_XXXX, _gcl_au, custom GA prefixes) count.
	 *
	 * Shared with main.js so the static and dynamic removal methods reach the same domains.
	 *
	 * @return string[]
	 */
	public static function moove_gdpr_get_parent_domain_cookies() {
		$d_domains = array( '_ga', '_fbp', '_gid', '_gat', '__utma', '__utmb', '__utmc', '__utmt', '__utmz', '_gcl_', '_gac_', '_fbc' );
		$d_domains = apply_filters( 'gdpr_d_domains_filter', $d_domains );
		return array_values( array_filter( (array) $d_domains, 'is_string' ) );
	}

	/**
	 * Expires every removable cookie sent with the current request.
	 *
	 * Reads the raw Cookie header rather than $_COOKIE, which rewrites dots and spaces in
	 * names to underscores and would expire a cookie that does not exist.
	 *
	 * @param bool $include_consent_cookie Also expire moove_gdpr_popup.
	 * @return array Cookie name => domains it was expired on.
	 */
	private static function moove_gdpr_expire_request_cookies( $include_consent_cookie = false ) {
		$cookies_removed = array();
		$cookies         = isset( $_SERVER['HTTP_COOKIE'] ) ? explode( ';', sanitize_text_field( wp_unslash( $_SERVER['HTTP_COOKIE'] ) ) ) : array();
		$host            = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
		$d_domains       = self::moove_gdpr_get_parent_domain_cookies();

		foreach ( $cookies as $cookie ) {
			$parts = explode( '=', $cookie );
			$name  = trim( $parts[0] );
			if ( ! self::moove_gdpr_is_removable_cookie( $name ) || ( 'moove_gdpr_popup' === $name && ! $include_consent_cookie ) ) :
				continue;
			endif;

			$domains = self::moove_gdpr_get_cookie_domains( $name, $host, $d_domains );
			setcookie( $name, '', -1, '/' );
			foreach ( $domains as $domain ) {
				setcookie( $name, '', -1, '/', '.' . $domain );
			}
			$cookies_removed[ $name ] = $domains;
		}
		return $cookies_removed;
	}

	/**
	 * Whether a cookie may be expired on consent revocation.
	 *
	 * wp_consent_* is kept for the same reason as in main.js: without it, WP Consent API
	 * plugins read "never asked" instead of "denied".
	 *
	 * @param string $name Cookie name.
	 * @return bool
	 */
	private static function moove_gdpr_is_removable_cookie( $name ) {
		// setcookie() throws a ValueError on these characters in PHP 8.
		if ( '' === $name || preg_match( '/[=,; \t\r\n\013\014]/', $name ) ) :
			return false;
		endif;
		return false === strpos( $name, 'woocommerce' )
			&& false === strpos( $name, 'wc_' )
			&& false === stripos( $name, 'wordpress' )
			&& false === strpos( $name, 'wp_consent' );
	}

	/**
	 * Domains a cookie has to be expired on, besides host-only. A Domain=.example.com cookie
	 * and a host-only cookie with the same name are separate cookies to the browser, so each
	 * scope needs its own Set-Cookie.
	 *
	 * @param string   $name      Cookie name.
	 * @param string   $host      Site host.
	 * @param string[] $d_domains Parent-domain cookie names, see moove_gdpr_get_parent_domain_cookies().
	 * @return string[] Domains without the leading dot.
	 */
	private static function moove_gdpr_get_cookie_domains( $name, $host, $d_domains ) {
		$domains = array( $host, preg_replace( '/^www\./', '', $host ) );

		foreach ( $d_domains as $d_domain ) {
			if ( '' !== $d_domain && false !== strpos( $name, $d_domain ) ) :
				// Walk up to the registrable domain (blog.example.com -> example.com); we cannot
				// tell which level the tag picked. Browsers reject the public-suffix levels.
				$labels = explode( '.', $host );
				while ( count( $labels ) > 1 ) {
					$domains[] = implode( '.', $labels );
					array_shift( $labels );
				}
				break;
			endif;
		}
		return array_values( array_unique( array_filter( $domains ) ) );
	}

	/**
	 * Language notice hide
	 */
	public static function moove_hide_language_notice() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : false;
		if ( ! wp_verify_nonce( $nonce, 'gdpr_hide_language_nonce' ) || ! current_user_can( 'manage_options' ) ) :
			wp_send_json_error( 'Unauthorized', 403 );
		endif;
		$user_id = get_current_user_id();
		if ( $user_id ) :
			$gdpr_default_content = new Moove_GDPR_Content();
			$option_name          = $gdpr_default_content->moove_gdpr_get_option_name();
			$modal_options        = get_option( $option_name );
			$modal_options[ 'gdpr_hide_language_notice_' . $user_id ] = 1;
			update_option( $option_name, $modal_options );
		endif;
		wp_die();
	}

}
new Moove_GDPR_Controller();
