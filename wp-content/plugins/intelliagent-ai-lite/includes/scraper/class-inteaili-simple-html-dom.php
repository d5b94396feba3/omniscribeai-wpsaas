<?php
/**
 * Website Scraper using Simple HTML DOM.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class INTEAILI_Simple_HTML_DOM
 *
 * Handles website content extraction by scraping pages and products to
 * populate the AI Knowledge Base.
 */
class INTEAILI_Simple_HTML_DOM {

	/**
	 * Initialize the class and load dependencies.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_lib();
	}

	/**
	 * Loads the Simple HTML DOM library if not already present.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   void
	 */
	private function load_lib() {
		$lib_path = INTEAILI_PATH . 'vendor/simplehtmldom/simple_html_dom.php';
		if ( ! function_exists( 'str_get_html' ) && file_exists( $lib_path ) ) {
			require_once $lib_path;
		}
	}

	/**
	 * Scans the website for pages and products to sync content.
	 *
	 * @since  1.0.0
	 * @return array Scraped data containing titles, URLs, and content.
	 */
	public function sync_website_content() {
		$scraped_data = array();

		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		$excluded_slugs = array(
			'my-account',
			'logout',
			'login',
			'register',
			'forgot',
			'password-reset',
			'change-password',
			'payment-success',
			'payment-cancelled',
			'thank-you',
			'sample-page',
			'blog',
			'blogs',
		);

		foreach ( $pages as $page ) {
			if ( in_array( $page->post_name, $excluded_slugs, true ) ) {
				continue;
			}

			$url     = get_permalink( $page->ID );
			$content = $this->scrape_page( $url );

			if ( ! empty( $content ) ) {
				$scraped_data[] = array(
					'title'   => $page->post_title,
					'url'     => $url,
					/* translators: %s = content prefix label */
					'content' => __( '[PAGE CONTENT]: ', 'intelliagent-ai-lite' ) . $content,
				);
			}
		}

		if ( class_exists( 'WooCommerce' ) ) {
			$woo_manager = new INTEAILI_WooCommerce( INTEAILI_SLUG );
			$products    = wc_get_products(
				array(
					'status' => 'publish',
					'limit'  => -1,
				)
			);

			foreach ( $products as $product ) {
				$product_url    = get_permalink( $product->get_id() );
				$scraped_data[] = array(
					'title'   => $product->get_name(),
					'url'     => $product_url,
					'content' => $woo_manager->get_product_data_as_text( $product_url ),
				);
			}
		}

		return $scraped_data;
	}

	/**
	 * Scrapes a specific URL and returns the plaintext content.
	 *
	 * @since 1.0.0
	 * @param string $url The URL to scrape.
	 * @return string Plaintext content of the page.
	 */
	public function scrape_page( $url ) {
		$url = esc_url_raw( $url );

		$args = array(
			'timeout'    => 120,
			'user-agent' => 'AICA-IntelliAgent/' . INTEAILI_VERSION . '; ' . get_bloginfo( 'url' ),
		);

		$response = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
			return '';
		}

		$html_content = wp_remote_retrieve_body( $response );

		// Protect against extremely large pages.
		if ( strlen( $html_content ) > 2000000 ) {
			return '';
		}

		if ( ! function_exists( 'str_get_html' ) ) {
			return '';
		}

		$html = str_get_html( $html_content );
		if ( ! $html ) {
			return '';
		}

		// Remove non-informative elements.
		foreach ( $html->find( 'script, style, nav, footer, header, noscript, .gdpr, .cookie' ) as $item ) {
			$item->outertext = '';
		}

		$text = $html->plaintext;
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );

		// Clean whitespace and strip any remaining tags.
		$text = wp_strip_all_tags( preg_replace( '/\s+/u', ' ', $text ) );

		$html->clear();
		unset( $html );

		return trim( $text );
	}
}
