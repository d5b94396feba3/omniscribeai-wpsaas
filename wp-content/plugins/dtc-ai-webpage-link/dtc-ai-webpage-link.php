<?php
/**
 * Plugin Name: DTC AI Webpage Generator Link
 * Plugin URI:  https://yourdomain.com/
 * Description: Adds a "Generate My FREE Webpage" link to the WooCommerce My Account navigation, redirecting to your AI converter tool. Also includes a "My Balance" link.
 * Version:     1.0.0
 * Author:      ShahiSoft LLC
 * Author URI:  https://yourdomain.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: dtc-ai-webpage-link
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DTC_AI_Webpage_Link {
    private $generate_code_slug = 'generate-code';
    private $my_balance_link_slug = 'my-balance';
    private $generate_code_text = '';
    private $my_balance_link_text = '';
    private $generate_code_url = '';
    private $my_balance_url = '';

    public function __construct() {
        $this->generate_code_text = __( 'Generate Code', 'dtc-ai-webpage-link' );
        $this->my_balance_link_text = __( 'My Balance', 'dtc-ai-webpage-link' );
        $this->generate_code_url = home_url( '/generate-code/' );
        $this->my_balance_url = home_url( '/my-balance/' );

        add_action( 'init', array( $this, 'register_custom_endpoint' ) );
        add_filter( 'query_vars', array( $this, 'add_custom_query_vars' ) );
        add_filter( 'woocommerce_account_menu_items', array( $this, 'add_links_to_account_menu' ) );
        add_action( 'template_redirect', array( $this, 'redirect_custom_endpoint' ) );
    }

    public function register_custom_endpoint() {
        add_rewrite_endpoint( $this->generate_code_slug, EP_PAGES );
        add_rewrite_endpoint( $this->my_balance_link_slug, EP_PAGES );
    }

    public function add_custom_query_vars( $vars ) {
        $vars[] = $this->generate_code_slug;
        $vars[] = $this->my_balance_link_slug;
        return $vars;
    }

    public function add_links_to_account_menu( $menu_links ) {
        $new_menu_links = array();

        foreach ( $menu_links as $key => $value ) {
            $new_menu_links[ $key ] = $value;

            if ( 'dashboard' === $key ) {
                $new_menu_links[ $this->generate_code_slug ] = $this->generate_code_text;
                $new_menu_links[ $this->my_balance_link_slug ] = $this->my_balance_link_text;
            }
        }

        return $new_menu_links;
    }

    public function redirect_custom_endpoint() {
        global $wp_query;

        if ( isset( $wp_query->query_vars[ $this->generate_code_slug ] ) ) {
            wp_redirect( esc_url_raw( $this->generate_code_url ) );
            exit;
        }
        if ( isset( $wp_query->query_vars[ $this->my_balance_link_slug ] ) ) {
            wp_redirect( esc_url_raw( $this->my_balance_url ) );
            exit;
        }
    }
}

new DTC_AI_Webpage_Link();