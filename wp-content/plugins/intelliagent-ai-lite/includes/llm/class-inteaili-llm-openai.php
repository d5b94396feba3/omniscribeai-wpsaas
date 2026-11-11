<?php
/**
 * OpenAI LLM integration for the IntelliAgent AI Lite plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes/llm
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class INTEAILI_LLM_OpenAI
 *
 * Handles communication with the OpenAI API, supporting text inputs
 * and the specific 2026 response schema.
 */
class INTEAILI_LLM_OpenAI {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The ID of this plugin.
	 */
	private $plugin_slug;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_slug The slug of the plugin.
	 */
	public function __construct( $plugin_slug ) {
		$this->plugin_slug = $plugin_slug;
	}

	/**
	 * Main API call for OpenAI with history and attachments.
	 *
	 * @since 1.0.0
	 * @param string $system_instruction Core instructions.
	 * @param string $user_query         The prompt from the user.
	 * @param array  $history            Chat history.
	 * @return string Response text
	 */
	public function llm_api_v2( $system_instruction, $user_query, $history = array() ): string {

		$items = $this->format_conversation_items(
			$history,
			$user_query
		);

		return $this->request_openai( $system_instruction, $items );
	}


	/**
	 * Formats conversation items into the OpenAI /responses schema.
	 *
	 * @since 1.0.0
	 * @param array  $history          Chat history.
	 * @param string $user_query       Current query.
	 * @return array Formatted conversation items.
	 */
	private function format_conversation_items( $history, $user_query ): array {
		$items = array();

		foreach ( $history as $msg ) {
			$is_assistant = ( 'assistant' === $msg['role'] || 'ai' === $msg['role'] );
			$items[]      = array(
				'role'    => $is_assistant ? 'assistant' : 'user',
				'content' => array(
					array(
						'type' => $is_assistant ? 'output_text' : 'input_text',
						'text' => $msg['content'],
					),
				),
			);
		}

		$items[] = array(
			'role'    => 'user',
			'content' => array(
				array(
					'type' => 'input_text',
					'text' => $user_query,
				),
			),
		);

		return $items;
	}

	/**
	 * Simplified API call for summaries or generator tasks.
	 *
	 * @since 1.0.0
	 * @param string $system_instruction Core instructions.
	 * @param string $user_query         The query.
	 * @param array  $history            History array.
	 * @return string Response text
	 */
	public function llm_api_v3( $system_instruction, $user_query, $history = array() ): string {
		$instructions = $system_instruction;
		$items        = array();

		if ( ! empty( $history ) ) {
			foreach ( $history as $msg ) {
				$is_assistant = ( 'assistant' === $msg['role'] || 'ai' === $msg['role'] );
				$items[]      = array(
					'role'    => $is_assistant ? 'assistant' : 'user',
					'content' => array(
						array(
							'type' => $is_assistant ? 'output_text' : 'input_text',
							'text' => $msg['content'],
						),
					),
				);
			}
		}

		$items[] = array(
			'role'    => 'user',
			'content' => array(
				array(
					'type' => 'input_text',
					'text' => $user_query,
				),
			),
		);

		return $this->request_openai( $instructions, $items );
	}

	/**
	 * Final HTTP request to the OpenAI /responses endpoint.
	 *
	 * @since 1.0.0
	 * @param string $system_instruction System instructions.
	 * @param array  $items              Formatted input items.
	 * @return string Response text or error message
	 */
	private function request_openai( $system_instruction, $items ) {
		$api_key = get_option( 'inteaili_openai_api_key' );
		if ( empty( $api_key ) ) {
			return __( 'Error: OpenAI API key is missing. Please check your settings.', 'intelliagent-ai-lite' );
		}

		$model = get_option( 'inteaili_openai_model', 'gpt-5.1' );
		$temp  = floatval( get_option( 'inteaili_openai_temperature', '0.7' ) );
		$url   = 'https://api.openai.com/v1/responses';

		$payload = array(
			'model'        => $model,
			'instructions' => $system_instruction,
			'input'        => $items,
			'store'        => true,
			'temperature'  => $temp,
		);

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => json_encode( $payload ),
				'timeout' => 120,
			)
		);

		if ( is_wp_error( $response ) ) {
			/* translators: %s = error message */
			return sprintf( __( 'Connection Error: %s', 'intelliagent-ai-lite' ), $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$msg = $body['error']['message'] ?? __( 'Unknown OpenAI Error', 'intelliagent-ai-lite' );
			/* translators: %s = API error message */
			return sprintf( __( 'OpenAI Error: %s', 'intelliagent-ai-lite' ), esc_html( $msg ) );
		}

		if ( isset( $body['output'][0]['content'][0]['text'] ) ) {
			return $body['output'][0]['content'][0]['text'];
		}

		return __( 'Error: No text returned.', 'intelliagent-ai-lite' );
	}
}
