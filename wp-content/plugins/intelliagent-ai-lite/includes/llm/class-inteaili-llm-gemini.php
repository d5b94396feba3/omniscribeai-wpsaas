<?php
/**
 * Gemini LLM integration for the IntelliAgent AI Lite plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes/llm
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class INTEAILI_LLM_Gemini
 *
 * Handles communication with the Google Gemini API, and conversation formatting.
 */
class INTEAILI_LLM_Gemini {

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
	 * Main API call for Gemini with history and attachments.
	 *
	 * @since 1.0.0
	 * @param string $system_instruction Core instructions.
	 * @param string $user_query         The prompt.
	 * @param array  $history            Chat history.
	 * @return string Response text
	 */
	public function llm_api_v2( $system_instruction, $user_query, $history = array() ): string {

		$contents = $this->format_conversation_contents(
			$history,
			$user_query
		);

		return $this->request_gemini( $system_instruction, $contents );
	}

	/**
	 * Formats the history into Gemini's contents array.
	 *
	 * @since 1.0.0
	 * @param array  $history          Chat history.
	 * @param string $user_query       User query.
	 * @return array Formatted contents array
	 */
	private function format_conversation_contents( $history, $user_query ): array {
		$contents = array();

		foreach ( $history as $msg ) {
			$role       = ( 'user' === $msg['role'] ) ? 'user' : 'model';
			$contents[] = array(
				'role'  => $role,
				'parts' => array( array( 'text' => $msg['content'] ) ),
			);
		}

		$contents[] = array(
			'role'  => 'user',
			'parts' => array( array( 'text' => $user_query ) ),
		);

		return $contents;
	}

	/**
	 * Simplified API call for summaries or prompt generation.
	 *
	 * @since 1.0.0
	 * @param string $system_instruction Instructions.
	 * @param string $user_query         The query.
	 * @param array  $history            History (usually empty).
	 * @return string Response text
	 */
	public function llm_api_v3( $system_instruction, $user_query, $history = array() ): string {
		$contents = array(
			array(
				'role'  => 'user',
				'parts' => array( array( 'text' => $user_query ) ),
			),
		);
		return $this->request_gemini( $system_instruction, $contents );
	}

	/**
	 * Final HTTP request to Gemini generateContent.
	 *
	 * @since 1.0.0
	 * @param string $system_instruction System instructions.
	 * @param array  $contents           The conversation array.
	 * @return string Response text or error message
	 */
	private function request_gemini( $system_instruction, $contents ) {
		$api_key = get_option( 'inteaili_gemini_api_key' );
		if ( empty( $api_key ) ) {
			return __( 'Error: Gemini API key is missing.', 'intelliagent-ai-lite' );
		}

		$model       = get_option( 'inteaili_gemini_model', 'gemini-2.0-flash' );
		$temperature = floatval( get_option( 'gemini_temperature', '0.7' ) );

		$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . esc_attr( $model ) . ':generateContent?key=' . esc_attr( $api_key );

		$payload = array(
			'system_instruction' => array( 'parts' => array( array( 'text' => $system_instruction ) ) ),
			'contents'           => $contents,
			'generationConfig'   => array( 'temperature' => $temperature ),
		);

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => json_encode( $payload ),
				'timeout' => 100,
			)
		);

		if ( is_wp_error( $response ) ) {
			/* translators: %s = error message */
			return sprintf( __( 'Connection Error: %s', 'intelliagent-ai-lite' ), $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$msg = $body['error']['message'] ?? __( 'Unknown Gemini Error', 'intelliagent-ai-lite' );
			/* translators: %s = error message from API */
			return sprintf( __( 'Gemini API Error: %s', 'intelliagent-ai-lite' ), esc_html( $msg ) );
		}

		if ( isset( $body['promptFeedback']['blockReason'] ) ) {
			return sprintf(
				/* translators: %s = reason returned by Gemini API */
				__( 'Gemini Blocked: The prompt was rejected (Reason: %s).', 'intelliagent-ai-lite' ),
				esc_html( $body['promptFeedback']['blockReason'] )
			);
		}

		$candidate = $body['candidates'][0] ?? null;

		if ( $candidate ) {
			if ( 'SAFETY' === $candidate['finishReason'] ) {
				return __( 'Gemini Blocked: Response filtered due to safety concerns.', 'intelliagent-ai-lite' );
			}

			if ( isset( $candidate['content']['parts'][0]['text'] ) ) {
				return $candidate['content']['parts'][0]['text'];
			}
		}

		return __( 'Error: Gemini failed to generate a response candidate.', 'intelliagent-ai-lite' );
	}
}
