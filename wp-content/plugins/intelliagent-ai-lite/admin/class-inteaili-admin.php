<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * Handles the registration of admin menus, settings, AJAX endpoints for
 * maintenance tasks, and enqueuing admin assets.
 */
class INTEAILI_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The ID of this plugin.
	 */
	private $plugin_slug;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version        The current version of this plugin.
	 */
	private $version;

	/**
	 * Database manager instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      INTEAILI_DB_Manager    $db_manager
	 */
	private $db_manager;

	/**
	 * LLM Processor instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      INTEAILI_LLM_Processor $llm_processor
	 */
	private $llm_processor;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_slug    The name of this plugin.
	 * @param    string $version        The version of this plugin.
	 */
	public function __construct( $plugin_slug, $version = '1.0' ) {
		$this->plugin_slug   = $plugin_slug;
		$this->version       = $version;
		// $this->llm_processor = new INTEAILI_LLM_Processor( $this->plugin_slug );
	}

	/**
	 * Get or initialize the LLM Processor instance used by this plugin.
	 *
	 * Lazy-loads the processor so it is only created when needed,
	 * and persists for future use.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   INTEAILI_LLM_Processor  The LLM Processor instance.
	 */
	private function get_llm_processor() {
		if ( ! isset( $this->llm_processor ) ) {
			$this->llm_processor = new INTEAILI_LLM_Processor( $this->plugin_slug );
		}
		return $this->llm_processor;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_admin_menu() {
		$icon_url = INTEAILI_URL . 'assets/images/plugin-icon.jpg';

		add_menu_page(
			'IntelliAgent AI Lite',
			'IntelliAgent AI Lite',
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_admin_dashboard' ),
			$icon_url,
			30
		);

		add_submenu_page(
			$this->plugin_slug,
			__( 'Dashboard', 'intelliagent-ai-lite' ),
			__( 'Dashboard', 'intelliagent-ai-lite' ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_admin_dashboard' )
		);

		add_submenu_page(
			$this->plugin_slug,
			__( 'Feedback & Training', 'intelliagent-ai-lite' ),
			__( 'Feedback & Training', 'intelliagent-ai-lite' ),
			'manage_options',
			'inteaili-feedback',
			array( $this, 'display_feedback_dashboard' )
		);

		add_submenu_page(
			$this->plugin_slug,
			__( 'Verified FAQs', 'intelliagent-ai-lite' ),
			__( 'Verified FAQs', 'intelliagent-ai-lite' ),
			'manage_options',
			'inteaili-faq',
			array( $this, 'display_faq_dashboard' )
		);

		add_submenu_page(
			$this->plugin_slug,
			__( 'Settings', 'intelliagent-ai-lite' ),
			__( 'Settings', 'intelliagent-ai-lite' ),
			'manage_options',
			'inteaili-settings',
			array( $this, 'display_settings_page' )
		);

		add_submenu_page(
			$this->plugin_slug,
			__( 'Status', 'intelliagent-ai-lite' ),
			__( 'Status', 'intelliagent-ai-lite' ),
			'manage_options',
			'inteaili-status',
			array( $this, 'render_system_status_page' )
		);

		add_submenu_page(
			$this->plugin_slug,
			__( 'Support & Features', 'intelliagent-ai-lite' ),
			__( 'Support & Features', 'intelliagent-ai-lite' ),
			'manage_options',
			'inteaili-support',
			array( $this, 'display_support_page' )
		);

		add_submenu_page(
			$this->plugin_slug,
			__( 'Upgrade to Pro', 'intelliagent-ai-lite' ),
			'<span class="inteaili-upgrade-pro-menu">' . __( 'Upgrade to Pro', 'intelliagent-ai-lite' ) . '</span>',
			'manage_options',
			'inteaili-upgrade-pro',
			array( $this, 'handle_admin_redirects' )
		);
	}

	/**
	 * Register the settings for the plugin.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function register_settings() {
		// Group: Content
		register_setting( 'inteaili_content_settings', 'inteaili_display_name', 'sanitize_text_field' );
		register_setting( 'inteaili_content_settings', 'inteaili_welcome_msg', 'sanitize_textarea_field' );
		register_setting( 'inteaili_content_settings', 'inteaili_placeholder', 'sanitize_text_field' );
		register_setting( 'inteaili_content_settings', 'inteaili_system_language', 'sanitize_text_field' );
		register_setting( 'inteaili_content_settings', 'inteaili_show_branding', 'absint' );

		// Group: Style
		register_setting( 'inteaili_style_settings', 'inteaili_logo_id', 'absint' );
		register_setting( 'inteaili_style_settings', 'inteaili_bubble_icon', 'sanitize_text_field' );
		register_setting( 'inteaili_style_settings', 'inteaili_primary_color', 'sanitize_hex_color' );
		register_setting( 'inteaili_style_settings', 'inteaili_theme_mode', 'sanitize_key' );

		// Group: Persona
		register_setting( 'inteaili_ai_persona_settings', 'inteaili_ai_persona', 'sanitize_textarea_field' );

		// Group: AI Engine
		register_setting( 'inteaili_ai_engine_settings', 'inteaili_llm_model', 'sanitize_text_field' );
		register_setting( 'inteaili_ai_engine_settings', 'inteaili_openai_api_key', 'sanitize_text_field' );
		register_setting( 'inteaili_ai_engine_settings', 'inteaili_openai_model', 'sanitize_text_field' );
		register_setting( 'inteaili_ai_engine_settings', 'inteaili_openai_temperature', 'floatval' );
		register_setting( 'inteaili_ai_engine_settings', 'inteaili_gemini_api_key', 'sanitize_text_field' );
		register_setting( 'inteaili_ai_engine_settings', 'inteaili_gemini_model', 'sanitize_text_field' );
		register_setting( 'inteaili_ai_engine_settings', 'inteaili_gemini_temperature', 'floatval' );
	}

	/**
	 * Retrieve all chat logs.
	 *
	 * @since    1.0.0
	 * @return   array    Array of chat log results.
	 */
	public function get_all_chat_logs() {
		$db = $this->get_db_manager();
		return $db->get_all_chat_logs();
	}

	/**
	 * Retrieve chat statistics.
	 *
	 * @since    1.0.0
	 * @return   array    Statistics including total and recent counts.
	 */
	public function get_chat_stats() {
		$db = $this->get_db_manager();
		return $db->get_chat_stats();
	}

	/**
	 * Handle AJAX request to delete a chat log.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_delete_chat_ajax() {
		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'intelliagent-ai-lite' ) );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( ! $id ) {
			wp_send_json_error( __( 'Invalid ID', 'intelliagent-ai-lite' ) );
		}

		$db = $this->get_db_manager();
		if ( $db->delete_chat_log( $id ) ) {
			wp_send_json_success( __( 'Log deleted successfully', 'intelliagent-ai-lite' ) );
		} else {
			wp_send_json_error( __( 'Database deletion failed', 'intelliagent-ai-lite' ) );
		}
	}

	/**
	 * Handle AJAX request to export chat logs to CSV.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_export_chat_ajax() {
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'intelliagent-ai-lite' ) );
		}

		$log_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$db     = $this->get_db_manager();
		$logs   = $db->get_logs_for_export( $log_id );

		if ( empty( $logs ) ) {
			wp_send_json_error( __( 'No logs found.', 'intelliagent-ai-lite' ) );
		}

		ob_start();
		$df = fopen( 'php://output', 'w' );
		fprintf( $df, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
		fputcsv(
			$df,
			array(
				__( 'ID', 'intelliagent-ai-lite' ),
				__( 'Date', 'intelliagent-ai-lite' ),
				__( 'Transcript', 'intelliagent-ai-lite' ),
			)
		);

		foreach ( $logs as $l ) {
			$history    = json_decode( $l['full_history'], true );
			$transcript = '';
			if ( is_array( $history ) ) {
				foreach ( $history as $msg ) {
					$role          = ( 'user' === $msg['role'] ) ? __( 'Visitor', 'intelliagent-ai-lite' ) : __( 'AI Agent', 'intelliagent-ai-lite' );
					$clean_text    = wp_strip_all_tags( $msg['content'] );
					$clean_content = str_replace( array( "\r", "\n", '"' ), array( ' ', ' ', "'" ), $clean_text );
					$transcript   .= "[$role]: " . $clean_content . ' | ';
				}
			}
			fputcsv( $df, array( $l['id'], $l['last_updated'], trim( $transcript ) ) );
		}

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $df );
		$csv_data = ob_get_clean();

		wp_send_json_success(
			array(
				'csv_data' => $csv_data,
				'filename' => 'chat-export-' . ( $log_id ? 'id-' . $log_id : 'all' ) . '-' . gmdate( 'Y-m-d' ) . '.csv',
			)
		);
	}

	/**
	 * Handle AJAX request to sync website content.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_sync_website_ajax() {
		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'intelliagent-ai-lite' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$step    = isset( $_POST['sync_step'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_step'] ) ) : 'discovery';
		$db      = $this->get_db_manager();
		$scraper = new INTEAILI_Simple_HTML_DOM();
		$woo     = new INTEAILI_WooCommerce( $this->plugin_slug );

		if ( 'discovery' === $step ) {
			$all_pages      = get_posts(
				array(
					'post_type'      => array( 'page' ),
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				)
			);
			$pages          = array();
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

			foreach ( $all_pages as $page ) {
				if ( in_array( $page->post_name, $excluded_slugs, true ) ) {
					continue;
				}
				$url       = get_permalink( $page->ID );
				$sync_data = $db->get_sync_status( $url );
				$pages[]   = array(
					'url'    => $url,
					'title'  => $page->post_title,
					'status' => $sync_data ? __( 'Synced', 'intelliagent-ai-lite' ) : __( 'Pending', 'intelliagent-ai-lite' ),
					'last'   => $sync_data ? date_i18n( get_option( 'date_format' ), strtotime( $sync_data['last_synced'] ) ) : '-',
				);
			}

			$product_count = 0;
			if ( $woo->is_active() ) {
				$woo_products  = $woo->get_discovery_products();
				$product_count = count( $woo_products );
				foreach ( $woo_products as $p ) {
					$sync_data = $db->get_sync_status( $p['url'] );
					$pages[]   = array(
						'url'    => $p['url'],
						'title'  => $p['title'],
						'status' => $sync_data ? __( 'Synced', 'intelliagent-ai-lite' ) : __( 'Pending', 'intelliagent-ai-lite' ),
						'last'   => $sync_data ? date_i18n( get_option( 'date_format' ), strtotime( $sync_data['last_synced'] ) ) : '-',
					);
				}
			}

			wp_send_json_success(
				array(
					'pages'         => $pages,
					'pages_count'   => count( $pages ),
					'product_count' => $product_count,
				)
			);
		}

		if ( 'sync_page' === $step ) {
			$url        = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
			$is_product = ( strpos( $url, '/product/' ) !== false || ( class_exists( 'WooCommerce' ) && url_to_postid( $url ) && get_post_type( url_to_postid( $url ) ) === 'product' ) );

			if ( $is_product && $woo->is_active() ) {
				$content = $woo->get_product_content( $url );
			} else {
				$content = $scraper->scrape_page( $url );
			}

			if ( empty( $content ) ) {
				$content = 'Source available at ' . $url . ' (No content extracted)';
			}

			$db->update_knowledge_base( $url, $content );
			wp_send_json_success(
				array(
					'status' => __( 'Synced', 'intelliagent-ai-lite' ),
					'last'   => current_time( 'mysql' ),
				)
			);
		}
	}
	

	/**
	 * Display the admin dashboard.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_admin_dashboard() {
		$dashboard_file = INTEAILI_PATH . 'admin/partials/dashboard.php';
		if ( file_exists( $dashboard_file ) ) {
			include_once $dashboard_file;
		} else {
			echo '<div class="wrap"><h1>' . esc_html( INTEAILI_NAME ) . ' ' . esc_html__( 'Dashboard', 'intelliagent-ai-lite' ) . '</h1>';
			echo '<p>' . esc_html__( 'Welcome to the AICA IntelliAgent system.', 'intelliagent-ai-lite' ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Display the settings page.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_settings_page() {
		$settings_file = INTEAILI_PATH . 'admin/partials/admin-settings.php';
		if ( file_exists( $settings_file ) ) {
			include_once $settings_file;
		} else {
			echo '<div class="wrap"><h1>' . esc_html( INTEAILI_NAME ) . ' ' . esc_html__( 'Settings', 'intelliagent-ai-lite' ) . '</h1>';
			echo '<p>' . esc_html__( 'Settings file not found. Please check the plugin installation.', 'intelliagent-ai-lite' ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Singleton pattern for DB manager.
	 *
	 * @since    1.0.0
	 * @return   INTEAILI_DB_Manager    The database manager instance.
	 */
	private function get_db_manager() {
		if ( ! isset( $this->db_manager ) ) {
			$this->db_manager = new INTEAILI_DB_Manager( $this->plugin_slug );
		}
		return $this->db_manager;
	}

	/**
	 * Get feedback data for admin.
	 *
	 * @since    1.0.0
	 * @return   array    Array containing logs and stats.
	 */
	public function get_feedback_data() {
		$db = $this->get_db_manager();
		return array(
			'logs'  => $db->get_all_feedback(),
			'stats' => $db->get_feedback_stats(),
		);
	}

	/**
	 * Display feedback dashboard.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_feedback_dashboard() {
		$db        = $this->get_db_manager();
		$feedbacks = $db->get_all_feedback();
		include_once INTEAILI_PATH . 'admin/partials/feedback-dashboard.php';
	}

	/**
	 * Handle AJAX to retrieve feedback.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_get_feedback_ajax() {
		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'intelliagent-ai-lite' ) );
		}

		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$db   = $this->get_db_manager();
		$data = $db->get_feedback_by_id( $id );

		if ( $data ) {
			wp_send_json_success( $data );
		}
		wp_send_json_error( __( 'Feedback not found', 'intelliagent-ai-lite' ) );
	}

	/**
	 * Handle AJAX to save feedback.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_save_feedback_ajax() {
		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'intelliagent-ai-lite' ) );
		}

		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$data = array(
			'user_query'  => isset( $_POST['user_query'] ) ? sanitize_textarea_field( wp_unslash( $_POST['user_query'] ) ) : '',
			'ai_response' => isset( $_POST['ai_response'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ai_response'] ) ) : '',
			'rating'      => isset( $_POST['rating'] ) ? sanitize_key( $_POST['rating'] ) : '',
			'created_at'  => current_time( 'mysql' ),
		);

		$db = $this->get_db_manager();
		if ( $id > 0 ) {
			$db->update_feedback( $id, $data );
		} else {
			$db->store_feedback( $data['user_query'], $data['ai_response'], $data['rating'] );
		}
		wp_send_json_success();
	}

	/**
	 * Handle AJAX to delete feedback.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_delete_feedback_ajax() {
		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'intelliagent-ai-lite' ) );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$db = $this->get_db_manager();
		if ( $db->delete_feedback( $id ) ) {
			wp_send_json_success();
		}
		wp_send_json_error( __( 'Delete failed', 'intelliagent-ai-lite' ) );
	}

	/**
	 * Get FAQ data.
	 *
	 * @since    1.0.0
	 * @return   array    Array containing logs and total count.
	 */
	public function get_faq_data() {
		$db = $this->get_db_manager();
		return array(
			'logs'  => $db->get_all_faqs(),
			'total' => count( $db->get_all_faqs() ),
		);
	}

	/**
	 * Display FAQ dashboard.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_faq_dashboard() {
		$this->get_db_manager();
		include_once INTEAILI_PATH . 'admin/partials/faq-dashboard.php';
	}

	/**
	 * Handle AJAX to get FAQ.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_get_faq_ajax() {
		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$data = $this->get_db_manager()->get_faq_by_id( $id );

		if ( $data ) {
			wp_send_json_success( $data );
		}
		wp_send_json_error();
	}

	/**
	 * Handle AJAX to save FAQ.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_save_faq_ajax() {
		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$data = array(
			'title'    => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'question' => isset( $_POST['question'] ) ? sanitize_textarea_field( wp_unslash( $_POST['question'] ) ) : '',
			'answer'   => isset( $_POST['answer'] ) ? sanitize_textarea_field( wp_unslash( $_POST['answer'] ) ) : '',
		);

		if ( $this->get_db_manager()->save_faq( $data, $id ) ) {
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Handle AJAX to delete FAQ.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_delete_faq_ajax() {
		check_ajax_referer( 'inteaili_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( $this->get_db_manager()->delete_faq( $id ) ) {
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Display the support page.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_support_page() {
		$support_file = INTEAILI_PATH . 'admin/partials/support-dashboard.php';

		if ( file_exists( $support_file ) ) {
			include_once $support_file;
		} else {
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Support', 'intelliagent-ai-lite' ); ?></h1>
				<p><?php esc_html_e( 'For support, please visit our website.', 'intelliagent-ai-lite' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Render System Status Page.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function render_system_status_page() {
		$status_engine = new INTEAILI_System_Status();
		$report        = $status_engine->get_report();
		require_once INTEAILI_PATH . 'admin/partials/status-dashboard.php';
	}

	/**
	 * Setup notice for API configuration.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_activation_notices() {
		// Only show to admins
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, 'inteaili-settings' ) !== false ) {
			return;
		}

		$is_api_active = $this->get_llm_processor()->is_ai_api_active();

		// Early exit if API is configured
		if ( $is_api_active ) {
			return;
		}

		$ai_engine_tab_url = admin_url( 'admin.php?page=inteaili-settings&tab=ai-engine' );

		?>
		<div class="notice notice-warning is-dismissible">
			<div style="padding: 10px 0;">
				<h3 style="margin: 0 0 10px 0;">
					<?php esc_html_e( 'IntelliAgent AI Lite: Setup Required', 'intelliagent-ai-lite' ); ?>
				</h3>
				
				<ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
					<li>
						<?php
						printf(
							/* translators: 1: opening anchor tag, 2: closing anchor tag */
							esc_html__( 'No AI Engine detected. %1$sConfigure your API Key%2$s (OpenAI, or Gemini) to start chatting.', 'intelliagent-ai-lite' ),
							'<a href="' . esc_url( $ai_engine_tab_url ) . '" style="font-weight:600;">',
							'</a>'
						);
						?>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle external redirect for upgrade link.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function handle_admin_redirects() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! is_admin() || ! isset( $_GET['page'] ) || 'inteaili-upgrade-pro' !== $_GET['page'] ) {
			return;
		}

		wp_safe_redirect( 'https://aica-intelliagent.com/' );
		exit;
	}

	/**
	 * Whitelist the external domain for safe redirects.
	 *
	 * @since 1.0.0
	 * @param array $hosts Allowed hosts.
	 * @return array
	 */
	public function whitelist_external_host( $hosts ) {
		$hosts[] = 'aica-intelliagent.com';
		return $hosts;
	}

	/**
	 * Add "Upgrade to Pro" link to the plugin action links.
	 *
	 * @since    1.0.0
	 * @param    array $links  The existing action links.
	 * @return   array  $links  The modified action links.
	 */
	public function inteaili_add_action_links( $links ) {
		$upgrade_link = sprintf(
			'<a href="%s" target="_blank" style="font-weight: bold; color: #d63638;">%s</a>',
			esc_url( 'https://aica-intelliagent.com/' ),
			__( 'Upgrade to Pro', 'intelliagent-ai-lite' )
		);

		// Add to the beginning of the array
		array_unshift( $links, $upgrade_link );

		return $links;
	}

	/**
	 * Add Documentation and Support links below the description.
	 *
	 * @since 1.0.0
	 * @param array  $links Existing meta links.
	 * @param string $file  The plugin file being processed.
	 * @return array Modified meta links.
	 */
	public function inteaili_add_row_meta_links( $links, $file ) {
		// Security check: Only apply to this specific plugin file
		if ( INTEAILI_BASENAME === $file ) {
			$new_links = array(
				'docs'    => sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( 'https://aica-intelliagent.com/docs' ), __( 'Documentation', 'intelliagent-ai-lite' ) ),
				'support' => sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( 'https://aica-intelliagent.com/support' ), __( 'Support', 'intelliagent-ai-lite' ) ),
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	/**
	 * Enqueue styles for the admin area.
	 *
	 * @since    1.0.0
	 * @param    string $hook    The current admin page hook.
	 * @return   void
	 */
	public function enqueue_styles( $hook ) {
		wp_enqueue_style(
			$this->plugin_slug . '-admin-menu',
			INTEAILI_URL . 'assets/css/admin/menu-icon.css',
			array(),
			$this->version
		);

		if ( false === strpos( $hook, 'inteaili' ) && false === strpos( $hook, $this->plugin_slug ) ) {
			return;
		}

		wp_enqueue_style( $this->plugin_slug . '-ad-header', INTEAILI_URL . 'assets/css/admin/header.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-ad-dashboard', INTEAILI_URL . 'assets/css/admin/dashboard.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-settings', INTEAILI_URL . 'assets/css/admin/settings.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-feedback', INTEAILI_URL . 'assets/css/admin/feedback.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-theme-logic', INTEAILI_URL . 'assets/css/public/theme-logic.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-chat-widget', INTEAILI_URL . 'assets/css/public/chat-widget.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-faq', INTEAILI_URL . 'assets/css/admin/faq.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-kb', INTEAILI_URL . 'assets/css/admin/knowledge-sync.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-ad-support', INTEAILI_URL . 'assets/css/admin/support.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-sys-status', INTEAILI_URL . 'assets/css/admin/system-status.css', array(), $this->version, 'all' );
	}

	/**
	 * Enqueue scripts for the admin area.
	 *
	 * @since    1.0.0
	 * @param    string $hook    The current admin page hook.
	 * @return   void
	 */
	public function enqueue_scripts( $hook ) {
		if ( false === strpos( $hook, $this->plugin_slug ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( $this->plugin_slug . '-admin', INTEAILI_URL . 'assets/js/admin/admin.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_slug . '-unsaved-changes', INTEAILI_URL . 'assets/js/admin/unsaved-changes.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_slug . '-knowledge-base', INTEAILI_URL . 'assets/js/admin/knowledge-base.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_slug . '-preview', INTEAILI_URL . 'assets/js/admin/preview.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_slug . '-media-uploader', INTEAILI_URL . 'assets/js/admin/media-uploader.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_slug . '-feedback', INTEAILI_URL . 'assets/js/admin/feedback.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_slug . '-faq', INTEAILI_URL . 'assets/js/admin/faq.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_slug . '-sys-status', INTEAILI_URL . 'assets/js/admin/system-status.js', array( 'jquery' ), $this->version, true );

		$admin_data = array(
			'ajax_url'                => admin_url( 'admin-ajax.php' ),
			'nonce'                   => wp_create_nonce( 'inteaili_admin_nonce' ),
			'i18n_visitor'            => __( 'Visitor', 'intelliagent-ai-lite' ),
			'i18n_ai'                 => __( 'AI Agent', 'intelliagent-ai-lite' ),
			'i18n_deleting'           => __( 'Deleting...', 'intelliagent-ai-lite' ),
			'i18n_saving'             => __( 'Saving...', 'intelliagent-ai-lite' ),
			'i18n_summary_title'      => __( 'Chat Summary', 'intelliagent-ai-lite' ),
			'i18n_confirm_delete'     => __( 'Delete this conversation forever?', 'intelliagent-ai-lite' ),
			'i18n_confirm_delete_faq' => __( 'Delete this faq forever?', 'intelliagent-ai-lite' ),
			'i18n_pages'              => __( 'Pages', 'intelliagent-ai-lite' ),
			'i18n_products'           => __( 'Products', 'intelliagent-ai-lite' ),
			'i18n_synced'             => __( 'Synced', 'intelliagent-ai-lite' ),
			'i18n_pending'            => __( 'Pending', 'intelliagent-ai-lite' ),
			'i18n_uptodate'           => __( 'Everything Up to Date', 'intelliagent-ai-lite' ),
			/* translators: %s = number of pages waiting for sync */
			'i18n_sync_pending'       => __( 'Sync %s Pending Pages', 'intelliagent-ai-lite' ),
			'i18n_syncing'            => __( 'Syncing...', 'intelliagent-ai-lite' ),
			'i18n_processing'         => __( 'Processing:', 'intelliagent-ai-lite' ),
			'i18n_justnow'            => __( 'Just now', 'intelliagent-ai-lite' ),
			'i18n_confirm_gen'        => __( 'This will replace your current instructions with a new AI-generated version based on your website content. Proceed?', 'intelliagent-ai-lite' ),
			'i18n_select_avatar'      => __( 'Select Avatar', 'intelliagent-ai-lite' ),
			'i18n_use_image'          => __( 'Use this Image', 'intelliagent-ai-lite' ),
			'i18n_server_error'       => __( 'Error during discovery', 'intelliagent-ai-lite' ),
		);

		wp_localize_script( $this->plugin_slug . '-admin', 'inteaili_admin_data', $admin_data );
		wp_localize_script( $this->plugin_slug . '-knowledge-base', 'inteaili_admin_data', $admin_data );

		wp_localize_script(
			$this->plugin_slug . '-preview',
			'inteaili_admin_assets',
			array(
				'default_avatar' => INTEAILI_URL . 'assets/images/default-avater.svg',
			)
		);
	}
}