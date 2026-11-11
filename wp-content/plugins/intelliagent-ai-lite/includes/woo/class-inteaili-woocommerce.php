<?php
/**
 * WooCommerce Integration for the IntelliAgent AI Lite plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class INTEAILI_WooCommerce
 *
 * Provides helper methods to extract product data, handle direct checkout links,
 * and retrieve order tracking information for the LLM processor.
 */
class INTEAILI_WooCommerce {

	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $plugin_slug    The ID of this plugin.
	 */
	private $plugin_slug;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_slug The slug of the plugin.
	 */
	public function __construct( $plugin_slug ) {
		$this->plugin_slug = $plugin_slug;
	}

	/**
	 * Check if WooCommerce is active on the site.
	 *
	 * @return bool True if WooCommerce is active, false otherwise.
	 */
	public function is_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Get product ID by its SKU.
	 *
	 * @param string $sku The product SKU.
	 * @return int Product ID or 0 if not found.
	 */
	public function get_product_id_by_sku( $sku ) {
		if ( ! $this->is_active() || empty( $sku ) ) {
			return 0;
		}
		return wc_get_product_id_by_sku( sanitize_text_field( $sku ) );
	}

	/**
	 * Get product ID by its name.
	 *
	 * @param string $name The product name.
	 * @return int Product ID or 0 if not found.
	 */
	public function get_product_id_by_name( $name ) {
		if ( ! $this->is_active() || empty( $name ) ) {
			return 0;
		}

		$products = wc_get_products(
			array(
				'limit' => 1,
				'name'  => sanitize_text_field( $name ),
			)
		);

		if ( ! empty( $products ) ) {
			return $products[0]->get_id();
		}

		return 0;
	}

	/**
	 * Get a list of published products for discovery/indexing.
	 *
	 * @return array List of products with titles and URLs.
	 */
	public function get_discovery_products() {
		if ( ! $this->is_active() ) {
			return array();
		}

		$products = wc_get_products(
			array(
				'status' => 'publish',
				'limit'  => -1,
			)
		);

		$product_list = array();
		foreach ( $products as $product ) {
			$product_list[] = array(
				'url'   => get_permalink( $product->get_id() ),
				/* translators: %s = Product Name */
				'title' => sprintf( __( '[Product] %s', 'intelliagent-ai-lite' ), $product->get_name() ),
				'type'  => 'product',
			);
		}
		return $product_list;
	}

	/**
	 * Get formatted product content for the LLM knowledge base.
	 *
	 * @param string $url The product permalink.
	 * @return string Formatted text data.
	 */
	public function get_product_content( $url ) {
		if ( ! $this->is_active() ) {
			return '';
		}

		$product_id = url_to_postid( esc_url_raw( $url ) );
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			return '';
		}

		$data = array(
			__( 'ID', 'intelliagent-ai-lite' )          => $product->get_id(),
			__( 'Name', 'intelliagent-ai-lite' )        => $product->get_name(),
			__( 'Price', 'intelliagent-ai-lite' )       => $product->get_price() . ' ' . get_woocommerce_currency(),
			__( 'SKU', 'intelliagent-ai-lite' )         => $product->get_sku(),
			__( 'Status', 'intelliagent-ai-lite' )      => $product->is_in_stock() ? __( 'In Stock', 'intelliagent-ai-lite' ) : __( 'Out of Stock', 'intelliagent-ai-lite' ),
			__( 'Description', 'intelliagent-ai-lite' ) => wp_strip_all_tags( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ),
			__( 'Categories', 'intelliagent-ai-lite' )  => wp_strip_all_tags( wc_get_product_category_list( $product->get_id(), ', ', '', '' ) ),
			__( 'Link', 'intelliagent-ai-lite' )        => get_permalink( $product->get_id() ),
		);

		$output = '### ' . __( 'WOOCOMMERCE PRODUCT DATA', 'intelliagent-ai-lite' ) . " ###\n";
		foreach ( $data as $key => $val ) {
			if ( ! empty( $val ) ) {
				$output .= "$key: $val\n";
			}
		}

		return $output;
	}

	/**
	 * Generate a direct add-to-cart checkout URL.
	 *
	 * @param int $product_id The product ID.
	 * @return string The raw checkout URL with query parameters.
	 */
	public function get_direct_checkout_url( $product_id ) {
		if ( ! $this->is_active() ) {
			return '';
		}

		$checkout_url = wc_get_checkout_url();

		$direct_url = add_query_arg(
			array(
				'add-to-cart' => absint( $product_id ),
				'quantity'    => 1,
			),
			$checkout_url
		);

		return esc_url_raw( $direct_url );
	}

	/**
	 * Retrieve order tracking data for the LLM.
	 *
	 * @param int|string $order_id The WooCommerce Order ID.
	 * @return array|false Order data array or false if not found.
	 */
	public function get_order_tracking_data( $order_id ) {
		if ( ! $this->is_active() || ! is_numeric( $order_id ) ) {
			return false;
		}

		$order = wc_get_order( absint( $order_id ) );

		if ( ! $order ) {
			return false;
		}

		return array(
			'status'       => 'success',
			'order_status' => $order->get_status(),
			'total'        => $order->get_total() . ' ' . $order->get_currency(),
			'date_created' => $order->get_date_created()->date( 'Y-m-d H:i' ),
			'items'        => $order->get_item_count(),
			'view_url'     => $order->get_view_order_url(),
		);
	}
}
