<?php
/**
 * Public-facing functionality of the plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/public
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class INTEAILI_Public
 *
 * Handles the public chat widget, AJAX messaging, visitor identification,
 * and visitor-specific interactions.
 */
class INTEAILI_Public {

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
	 * @var      string    $version    The current version of this plugin.
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
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_slug The slug of the plugin.
	 * @param string $version     The version of the plugin.
	 */
	public function __construct( $plugin_slug, $version ) {
		$this->plugin_slug = $plugin_slug;
		$this->version     = $version;

		if ( class_exists( 'INTEAILI_DB_Manager' ) ) {
			$this->db_manager = new INTEAILI_DB_Manager( $this->plugin_slug );
		}
	}

	/**
	 * Handle incoming AJAX chat messages.
	 *
	 * Processes the visitor's message, coordinates with the LLM processor,
	 * handles LLM logic, and returns the AI response in JSON format.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_chat_message_ajax() {

		check_ajax_referer( 'chat_message_action', 'chat_nonce' );

		$user_message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		if ( empty( $user_message ) ) {
			wp_send_json_error( array( 'message' => __( 'Empty message.', 'intelliagent-ai-lite' ) ) );
		}

		$is_regen    = isset( $_POST['is_regen'] ) && ( 'true' === $_POST['is_regen'] || true === $_POST['is_regen'] );
		$chat_uuid = $this->get_chat_visitor_id();
		$history     = $this->get_prepared_history( $user_message, $is_regen );

		$llm_processor = new INTEAILI_LLM_Processor( $this->plugin_slug );

		$ai_response_html = $llm_processor->get_llm_response( $user_message, $history );

		if ( 0 === strpos( $ai_response_html, 'Error:' ) ) {
			wp_send_json_error( array( 'message' => $ai_response_html ) );
		}

		$this->finalize_chat_session( $chat_uuid, $history, $ai_response_html, $is_regen );

		wp_send_json_success( array( 'reply' => wp_kses_post( $ai_response_html ) ) );
	}

	/**
	 * Prepares conversation history for the current message.
	 *
	 * @since    1.0.0
	 * @param string $user_message Current message.
	 * @param bool   $is_regen     Is this a regeneration request.
	 * @return array The updated conversation history array.
	 */
	private function get_prepared_history( $user_message, $is_regen ) {
		$chat_uuid   = $this->get_chat_visitor_id();
		$transient_key = 'inteaili_ai_hist_' . $chat_uuid;
		$history       = get_transient( $transient_key );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		if ( ! $is_regen ) {
			$history[] = array(
				'role'    => 'user',
				'content' => $user_message,
				'time'    => time(),
			);
		}
		return $history;
	}

	/**
	 * Updates the session transient and the persistent database log.
	 *
	 * @since 1.0.0
	 * @param string $chat_uuid     Session UUID.
	 * @param array  $history         Conversation history.
	 * @param string $content_to_save AI response.
	 * @param bool   $is_regen        Is this a regeneration.
	 * @return void
	 */
	private function finalize_chat_session( $chat_uuid, $history, $content_to_save, $is_regen ) {
		$transient_key = 'inteaili_ai_hist_' . $chat_uuid;

		if ( $is_regen && ! empty( $history ) ) {
			for ( $i = count( $history ) - 1; $i >= 0; $i-- ) {
				if ( 'assistant' === $history[ $i ]['role'] ) {
					$history[ $i ] = array(
						'role'    => 'assistant',
						'content' => $content_to_save,
						'time'    => time(),
					);
					break;
				}
			}
		} else {
			$history[] = array(
				'role'    => 'assistant',
				'content' => $content_to_save,
				'time'    => time(),
			);
		}

		set_transient( $transient_key, $history, 1 * HOUR_IN_SECONDS );
		if ( $this->db_manager ) {
			$this->db_manager->save_chat_history( $chat_uuid, $history );
		}
	}

	/**
	 * Retrieves previous session IDs stored in the visitor's browser.
	 *
	 * @since 1.0.0
	 * @return array Array of past session UUIDs.
	 */
	private function get_user_session_tracker() {
		$cookie_name = 'inteaili_user_sessions';
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return explode( ',', sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) );
		}
		return array();
	}

	/**
	 * Adds current session UUID to the history tracker cookie.
	 *
	 * @since 1.0.0
	 * @param string $uuid Current session UUID.
	 * @return void
	 */
	private function add_session_to_tracker( $uuid ) {
		$sessions = $this->get_user_session_tracker();
		if ( ! in_array( $uuid, $sessions, true ) ) {
			$sessions[] = $uuid;
			$sessions   = array_slice( $sessions, -15 );
			setcookie( 'inteaili_user_sessions', implode( ',', $sessions ), time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	/**
	 * Gets the unique session ID for the visitor.
	 *
	 * @since 1.0.0
	 * @return string The unique session UUID or empty string if not set.
	 */
	private function get_chat_visitor_id() {
		$cookie_name = 'inteaili_chat_uuid';

		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return sanitize_key( $_COOKIE[ $cookie_name ] );
		}

		return '';
	}

	/**
	 * Renders the chat widget UI onto the page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function inteaili_render_chat_ui() {
		$chat_uuid   = $this->get_chat_visitor_id();
		$transient_key = 'inteaili_ai_hist_' . $chat_uuid;

		$existing_history = get_transient( $transient_key );
		if ( ! is_array( $existing_history ) ) {
			$existing_history = array();
		}

		$template_path = INTEAILI_PATH . 'public/partials/chat-template.php';

		if ( file_exists( $template_path ) ) {
			ob_start();
			$chat_history = $existing_history;
			include_once $template_path;

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Handle AJAX to clear the current chat session.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_clear_chat_ajax() {
		check_ajax_referer( 'chat_message_action', 'nonce' );
		setcookie( 'inteaili_chat_uuid', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		wp_send_json_success();
	}


	/**
	 * Handle AJAX to fetch past conversations for the history menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_fetch_recent_chats() {
		check_ajax_referer( 'chat_message_action', 'nonce' );

		$user_sessions = $this->get_user_session_tracker();

		if ( empty( $user_sessions ) || ! $this->db_manager ) {
			wp_send_json_success( array() );
		}

		// Call the DB Manager function.
		$logs = $this->db_manager->get_logs_by_session_ids( $user_sessions );

		$output = array();

		foreach ( $logs as $log ) {
			$history = json_decode( $log['full_history'], true );
			if ( empty( $history ) ) {
				continue;
			}

			// Find the first user message to use as a preview title.
			$preview = __( 'New Conversation', 'intelliagent-ai-lite' );
			foreach ( $history as $msg ) {
				if ( 'user' === $msg['role'] ) {
					$preview = $msg['content'];
					break;
				}
			}

			$output[] = array(
				'id'      => sanitize_key( $log['session_id'] ),
				'date'    => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log['last_updated'] ) ),
				'preview' => wp_trim_words( $preview, 8 ),
				'history' => $history, // sanitized during storage.
			);
		}

		wp_send_json_success( $output );
	}

	/**
	 * Handle AJAX feedback rating (thumbs up/down).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_chat_feedback_ajax() {
		check_ajax_referer( 'chat_message_action', 'nonce' );

		$query    = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		$response = isset( $_POST['response'] ) ? sanitize_text_field( wp_unslash( $_POST['response'] ) ) : '';
		$rating   = isset( $_POST['rating'] ) ? sanitize_key( wp_unslash( $_POST['rating'] ) ) : '';

		if ( $this->db_manager && ! empty( $query ) ) {
			$this->db_manager->store_feedback( $query, $response, $rating );
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Initialize visitor cookie early.
	 * This replaces PHP Sessions to remain cache-compatible.
	 * 
	 * 	@since 1.0.5
	 */
	public function inteaili_init_visitor_cookie() {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		$cookie_name = 'inteaili_chat_uuid';

		if ( ! isset( $_COOKIE[ $cookie_name ] ) && ! headers_sent() ) {
			// Generate a unique identifier (not a PHP session ID)
			$uuid = wp_generate_password( 16, false ) . '_' . time();

			setcookie(
				$cookie_name,
				$uuid,
				time() + ( 1 * DAY_IN_SECONDS ), // 24 hour expiry
				COOKIEPATH,
				COOKIE_DOMAIN,
				is_ssl(),
				true // HttpOnly for security
			);

			$_COOKIE[ $cookie_name ] = $uuid;
			$this->add_session_to_tracker( $uuid );
		}
	}

	/**
	 * Register the CSS for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_slug . '-theme-logic', INTEAILI_URL . 'assets/css/public/theme-logic.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_slug . '-chat-widget', INTEAILI_URL . 'assets/css/public/chat-widget.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		$js_path = INTEAILI_URL . 'assets/js/public/';
		$ver     = $this->version;

		wp_enqueue_script( $this->plugin_slug . '-core', $js_path . 'chat-core.js', array( 'jquery' ), $ver, true );
		wp_enqueue_script( $this->plugin_slug . '-messaging', $js_path . 'chat-messaging.js', array( $this->plugin_slug . '-core' ), $ver, true );
		wp_enqueue_script( $this->plugin_slug . '-menu', $js_path . 'chat-menu.js', array( $this->plugin_slug . '-core' ), $ver, true );
		wp_enqueue_script( $this->plugin_slug . '-history', $js_path . 'chat-history.js', array( $this->plugin_slug . '-core' ), $ver, true );

		wp_localize_script(
			$this->plugin_slug . '-core',
			'inteaili_public_data',
			array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'nonce'                 => wp_create_nonce( 'chat_message_action' ),
				'just_now'              => __( 'just now', 'intelliagent-ai-lite' ),
				'm_ago'                 => __( 'm ago', 'intelliagent-ai-lite' ),
				'h_ago'                 => __( 'h ago', 'intelliagent-ai-lite' ),
				'i18n_copy'             => __( 'Copy', 'intelliagent-ai-lite' ),
				'i18n_regen'            => __( 'Regenerate', 'intelliagent-ai-lite' ),
				'i18n_helpful'          => __( 'Helpful', 'intelliagent-ai-lite' ),
				'i18n_not_helpful'      => __( 'Not helpful', 'intelliagent-ai-lite' ),
				'popup_delay'           => 3000,
				'i18n_start_fresh'      => __( 'Start fresh?', 'intelliagent-ai-lite' ),
				'i18n_start_fresh_desc' => __( 'This will clear your current messages so you can start a new conversation.', 'intelliagent-ai-lite' ),
				'i18n_end_chat_title'   => __( 'End this chat?', 'intelliagent-ai-lite' ),
				'i18n_end_chat_desc'    => __( 'This will clear your current messages and start a new conversation again.', 'intelliagent-ai-lite' ),
				'i18n_confirm'          => __( 'Confirm', 'intelliagent-ai-lite' ),
				'i18n_processing'       => __( 'Processing...', 'intelliagent-ai-lite' ),
				'i18n_loading_history'  => __( 'Loading history...', 'intelliagent-ai-lite' ),
				'i18n_no_recent'        => __( 'No recent chats found.', 'intelliagent-ai-lite' ),
			)
		);
	}
}
