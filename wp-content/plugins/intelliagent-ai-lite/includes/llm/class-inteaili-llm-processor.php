<?php
/**
 * LLM Processing Logic for the IntelliAgent AI Lite plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class INTEAILI_LLM_Processor
 *
 * Handles the orchestration of LLM requests, system instruction building,
 * and integration with WooCommerce and Knowledge Base data.
 */
class INTEAILI_LLM_Processor {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The ID of this plugin.
	 */
	private $plugin_slug;

	/**
	 * The custom system prompt set by the user.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $system_instruction
	 */
	private $system_instruction;

	/**
	 * The active LLM model instance (Grok, OpenAI, or Gemini).
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $llm_model
	 */
	private $llm_model;

	/**
	 * The preferred response language.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $system_language
	 */
	private $system_language;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_slug The slug of the plugin.
	 */
	public function __construct( $plugin_slug ) {
		$this->plugin_slug        = $plugin_slug;
		$this->system_instruction = get_option( 'inteaili_ai_persona', '' );

		$active_llm_model      = get_option( 'inteaili_llm_model', 'Gemini' );
		$this->system_language = get_option( 'inteaili_system_language', 'English' );

		if ( 'OpenAI' === $active_llm_model && class_exists( 'INTEAILI_LLM_OpenAI' ) ) {
			$this->llm_model = new INTEAILI_LLM_OpenAI( $this->plugin_slug );
		} elseif ( 'Gemini' === $active_llm_model && class_exists( 'INTEAILI_LLM_Gemini' ) ) {
			$this->llm_model = new INTEAILI_LLM_Gemini( $this->plugin_slug );
		}
	}

	/**
	 * Builds specific instructions if WooCommerce is active.
	 *
	 * @since  1.0.0
	 * @return string WooCommerce instruction text
	 */
	private function get_woocommerce_instruction(): string {

		$woo_helper = new INTEAILI_WooCommerce( $this->plugin_slug );

		$woo_instruction = '';

		if ( $woo_helper->is_active() ) {
			$woo_instruction = sprintf(
				"\n\n### %s:\n%s",
				__( 'ECOMMERCE CAPABILITIES (PRODUCT RECOMMENDATION, & PRODUCT INFORMATION)', 'intelliagent-ai-lite' ),
				__( "1. Recommend relevant products from the Knowledge Base.\n2. ALWAYS include the Product URL in your recommendation.\n3. Mention price and stock status. Always use the bold <b></b> as needed.\n4. Recommend products with URL and ID.\n5. CRITICAL: do not output any JSON, code blocks, or 'intent' labels in your response to the user. The system handles the logic internally. Just provide the conversational response.", 'intelliagent-ai-lite' )
			);
		}
		return $woo_instruction;
	}

	/**
	 * Retrieves context from user feedback to guide the AI.
	 *
	 * @since  1.0.0
	 * @return string Feedback context text
	 */
	private function get_feedback_context(): string {
		$db_manager        = new INTEAILI_DB_Manager( $this->plugin_slug );
		$liked_examples    = $db_manager->get_feedback_by_type( 'like', 3 );
		$disliked_examples = $db_manager->get_feedback_by_type( 'dislike', 3 );

		$feedback_context = '';

		if ( ! empty( $liked_examples ) ) {
			$feedback_context .= "\n\n### POSITIVE EXAMPLES (FOLLOW THIS STYLE):";
			foreach ( $liked_examples as $example ) {
				$feedback_context .= "\nUser: " . esc_html( $example['user_query'] );
				$feedback_context .= "\nSuccessful Response: " . $example['ai_response'] . "\n---";
			}
		}

		if ( ! empty( $disliked_examples ) ) {
			$feedback_context .= "\n\n### NEGATIVE EXAMPLES (AVOID THESE MISTAKES):";
			foreach ( $disliked_examples as $example ) {
				$feedback_context .= "\nUser: " . esc_html( $example['user_query'] );
				$feedback_context .= "\nRejected Response: " . $example['ai_response'];
				$feedback_context .= "\nReason: The user found this unhelpful. Do not repeat this approach.\n---";
			}
		}

		return $feedback_context;
	}

	/**
	 * Main entry point for generating AI responses.
	 *
	 * @since 1.0.0
	 * @param string $user_query The prompt from the visitor.
	 * @param array  $history    The conversation history.
	 * @return string Response text
	 */
	public function get_llm_response( string $user_query, array $history = array() ): string {
		if ( ! $this->llm_model ) {
			return __( 'Error: LLM Model not properly initialized.', 'intelliagent-ai-lite' );
		}

		$system_instruction_path = INTEAILI_PATH . 'includes/prompts/llm-system-instruction-core.php';

		if ( ! file_exists( $system_instruction_path ) ) {
			return __( 'Error: Internal configuration file missing.', 'intelliagent-ai-lite' );
		}

		$core_system_instruction = include_once $system_instruction_path;

		$feedback_context = $this->get_feedback_context();
		$faq_context      = $this->get_faq_context();
		$woo_instruction  = $this->get_woocommerce_instruction();

		$db_manager = new INTEAILI_DB_Manager( $this->plugin_slug );
		$kb_content = $db_manager->get_kb_content_as_string();

		if ( strlen( $kb_content ) > 500000 ) {
			$kb_content = substr( $kb_content, 0, 500000 ) . '... [Truncated]';
		}

		$final_instruction  = $this->system_instruction . $woo_instruction . $faq_context . "\n\n" . $core_system_instruction . $feedback_context;
		$final_instruction .= "\n\n### WEBSITE KNOWLEDGE:\n" . $kb_content;

		return $this->llm_model->llm_api_v2( $final_instruction, $user_query, $history );
	}


	/**
	 * Retrieves verified FAQ knowledge.
	 *
	 * @since  1.0.0
	 * @return string FAQ context text
	 */
	private function get_faq_context(): string {
		$db_manager = new INTEAILI_DB_Manager( $this->plugin_slug );
		$faqs       = $db_manager->get_all_faqs();

		if ( empty( $faqs ) ) {
			return '';
		}

		$faq_context  = "\n\n### " . __( 'VERIFIED FAQ KNOWLEDGE (PRIORITIZE THESE ANSWERS)', 'intelliagent-ai-lite' ) . ':';
		$faq_context .= "\n" . __( 'When a user asks a question found here, use the verified answer provided.', 'intelliagent-ai-lite' );

		foreach ( $faqs as $faq ) {
			$faq_context .= "\n\n" . __( 'Question', 'intelliagent-ai-lite' ) . ': ' . wp_strip_all_tags( $faq['question'] );
			$faq_context .= "\n" . __( 'Verified Answer', 'intelliagent-ai-lite' ) . ': ' . wp_strip_all_tags( $faq['answer'] );
		}

		return $faq_context;
	}

	/**
	 * Checks if the active LLM model has a configured API Key.
	 *
	 * @since    1.0.0
	 * @return   bool True if configured, false otherwise.
	 */
	public function is_ai_api_active(): bool {
		$active_model = get_option( 'inteaili_llm_model', 'Gemini' );
		$api_key      = '';

		switch ( $active_model ) {
			case 'OpenAI':
				$api_key = get_option( 'inteaili_openai_api_key' );
				break;
			case 'Gemini':
				$api_key = get_option( 'inteaili_gemini_api_key' );
				break;
			default:
				$api_key = get_option( 'inteaili_gemini_api_key' );
				break;
		}

		return ! empty( $api_key );
	}
}
