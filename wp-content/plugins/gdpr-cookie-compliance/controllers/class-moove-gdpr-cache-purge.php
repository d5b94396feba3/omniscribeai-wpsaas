<?php
/**
 * Moove_GDPR_Cache_Purge File Doc Comment
 *
 * Clears page caches and optimisation caches when the GDPR settings are saved. Cached
 * pages hold the banner markup, the plugin settings printed into the page and the
 * blocked iframes, so without this a change only reaches visitors once the cache
 * expires or is cleared by hand.
 *
 * @category Moove_GDPR_Cache_Purge
 * @package   gdpr-cookie-compliance
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Moove_GDPR_Cache_Purge Class Doc Comment
 *
 * @category Class
 * @package  Moove_GDPR_Cache_Purge
 * @author   Moove Agency
 */
class Moove_GDPR_Cache_Purge {
	/**
	 * Construct function
	 */
	public function __construct() {
		// Late, so the caches are cleared after every other save handler has written its settings.
		add_action( 'gdpr_cookie_filter_settings', array( &$this, 'purge' ), 99 );
	}

	/**
	 * Whether caches are cleared when the settings are saved.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		/**
		 * Lets a site keep its caches when the settings are saved, for example where a
		 * full purge is costly and the cache is cleared on a schedule instead.
		 *
		 * @param bool $enabled Whether to clear caches on save.
		 */
		return (bool) apply_filters( 'gdpr_cc_purge_cache_on_save', true );
	}

	/**
	 * Caching and optimisation plugins active on the site, with how to clear each one.
	 *
	 * Each callback does what the plugin's own "clear all" button does, through its
	 * public API. Optimisation caches come first, so a page rebuilt straight after the
	 * page cache is cleared does not pick up old combined files.
	 *
	 * @return array Clear callbacks keyed by plugin name.
	 */
	public static function get_caches() {
		$caches = array();

		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) :
			$caches['Autoptimize'] = function () {
				autoptimizeCache::clearall();
			};
		endif;

		// WP-Optimize is often used for database clean-up alone, so only when its caching is switched on.
		$wpo_page_cache = function_exists( 'WP_Optimize' ) ? WP_Optimize()->get_page_cache() : null;
		$wpo_page_cache = $wpo_page_cache && $wpo_page_cache->is_enabled() ? $wpo_page_cache : null;
		$wpo_minify     = class_exists( 'WP_Optimize_Minify_Cache_Functions' ) && function_exists( 'wp_optimize_minify_config' ) && wp_optimize_minify_config()->get( 'enabled' );
		if ( $wpo_page_cache || $wpo_minify ) :
			$caches['WP-Optimize'] = function () use ( $wpo_page_cache, $wpo_minify ) {
				if ( $wpo_minify ) :
					// Starts a new minify version rather than deleting files that cached pages still use.
					WP_Optimize_Minify_Cache_Functions::reset();
				endif;
				if ( $wpo_page_cache ) :
					$wpo_page_cache->purge();
				endif;
			};
		endif;

		if ( function_exists( 'rocket_clean_domain' ) ) :
			$caches['WP Rocket'] = function () {
				if ( function_exists( 'rocket_clean_minify' ) ) :
					rocket_clean_minify();
				endif;
				if ( function_exists( 'rocket_clean_cache_busting' ) ) :
					rocket_clean_cache_busting();
				endif;
				rocket_clean_domain();
			};
		endif;

		if ( has_action( 'litespeed_purge_all' ) ) :
			$caches['LiteSpeed Cache'] = function () {
				do_action( 'litespeed_purge_all', 'GDPR Cookie Compliance settings saved' );
			};
		endif;

		// Only once connected to a NitroPack account, as there is no cache to clear before that.
		if ( function_exists( 'nitropack_sdk_purge' ) && function_exists( 'get_nitropack' ) && method_exists( get_nitropack(), 'isConnected' ) && get_nitropack()->isConnected() ) :
			$caches['NitroPack'] = function () {
				// Light purge keeps the optimised images, as NitroPack does itself when Elementor settings change.
				if ( defined( 'NitroPack\SDK\PurgeType::LIGHT_PURGE' ) ) :
					nitropack_sdk_purge( null, null, 'GDPR Cookie Compliance settings saved', \NitroPack\SDK\PurgeType::LIGHT_PURGE );
				else :
					nitropack_sdk_purge( null, null, 'GDPR Cookie Compliance settings saved' );
				endif;
			};
		endif;

		if ( function_exists( 'w3tc_flush_all' ) ) :
			$caches['W3 Total Cache'] = function () {
				w3tc_flush_all();
			};
		endif;

		if ( function_exists( 'wp_cache_clear_cache' ) ) :
			$caches['WP Super Cache'] = function () {
				// 0 clears every site in the network, so pass the current site on multisite.
				wp_cache_clear_cache( is_multisite() ? get_current_blog_id() : 0 );
			};
		endif;

		if ( has_action( 'wpfc_clear_all_cache' ) ) :
			$caches['WP Fastest Cache'] = function () {
				// True clears the minified files as well.
				do_action( 'wpfc_clear_all_cache', true );
			};
		endif;

		if ( has_action( 'cache_enabler_clear_site_cache' ) ) :
			$caches['Cache Enabler'] = function () {
				do_action( 'cache_enabler_clear_site_cache' );
			};
		endif;

		if ( function_exists( 'sg_cachepress_purge_everything' ) ) :
			$caches['SiteGround Speed Optimizer'] = function () {
				sg_cachepress_purge_everything();
			};
		endif;

		if ( has_action( 'wphb_clear_page_cache' ) ) :
			$caches['Hummingbird'] = function () {
				do_action( 'wphb_clear_page_cache' );
			};
		endif;

		if ( has_action( 'breeze_clear_all_cache' ) ) :
			$caches['Breeze'] = function () {
				do_action( 'breeze_clear_all_cache' );
			};
		endif;

		if ( has_action( 'rt_nginx_helper_purge_all' ) ) :
			$caches['Nginx Helper'] = function () {
				do_action( 'rt_nginx_helper_purge_all' );
			};
		endif;

		return $caches;
	}

	/**
	 * Clear every cache found, after the settings are saved.
	 *
	 * A failing cache plugin is skipped rather than breaking the settings page, as the
	 * settings themselves are already saved by this point.
	 *
	 * @return void
	 */
	public function purge() {
		if ( ! self::is_enabled() ) :
			return;
		endif;

		foreach ( self::get_caches() as $clear_cache ) :
			try {
				call_user_func( $clear_cache );
			} catch ( Throwable $e ) {
				continue;
			}
		endforeach;

		/**
		 * Fires after the caches are cleared, for caches this plugin does not know about,
		 * such as a host or CDN cache.
		 */
		do_action( 'gdpr_cc_after_cache_purge' );
	}
}
new Moove_GDPR_Cache_Purge();
