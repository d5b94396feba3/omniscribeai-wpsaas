<?php
/**
 * AI Engine & LLM Configuration Template
 *
 * This template handles the administration settings for Large Language Model (LLM)
 * providers, including API authentication, model selection, and generation parameters.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials/Components
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prefixing global variables to avoid conflicts.
 */
$inteaili_llm_providers = array(
	'OpenAI' => array(
		'name' => 'OpenAI',
		'url'  => 'https://platform.openai.com/api-keys',
	),
	'Gemini' => array(
		'name' => 'Google Gemini',
		'url'  => 'https://aistudio.google.com/app/api-keys',
	),
);
?>

<form method="post" action="options.php">
<?php
/**
 * Settings Fields
 * Output nonce, action, and option_page fields for LLM settings.
 */
settings_fields( 'inteaili_ai_engine_settings' );
?>
<div class="postbox aibg-box">
	<div class="postbox-header">
		<h2 class="hndle"><?php esc_html_e( 'AI Engine Configuration', 'intelliagent-ai-lite' ); ?></h2>
	</div>
	<div class="inside">
		<table class="form-table" role="presentation">
			<tr class="inteaili_llm_model_selector">
				<th scope="row">
					<label for="inteaili_llm_model_selector"><?php esc_html_e( 'Primary AI Provider', 'intelliagent-ai-lite' ); ?></label>
				</th>
				<td>
					<select name="inteaili_llm_model" id="inteaili_llm_model_selector" class="regular-text inteaili-input">
						<option value="OpenAI" <?php selected( get_option( 'inteaili_llm_model' ), 'OpenAI' ); ?>><?php esc_html_e( 'OpenAI GPT', 'intelliagent-ai-lite' ); ?></option>
						<option value="Gemini" <?php selected( get_option( 'inteaili_llm_model' ), 'Gemini' ); ?>><?php esc_html_e( 'Google Gemini', 'intelliagent-ai-lite' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Choose the primary AI engine to power your agent’s reasoning, tool execution, and content generation.', 'intelliagent-ai-lite' ); ?></p>
				</td>
			</tr>

			<tbody id="settings-group-OpenAI" class="llm-settings-group" style="display:none;">
				<tr style="background: #f8fafc;">
					<td colspan="2" style="padding: 10px 20px;">
						<strong><?php esc_html_e( 'OpenAI Settings', 'intelliagent-ai-lite' ); ?></strong>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inteaili_openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<input type="password" id="inteaili_openai_api_key" name="inteaili_openai_api_key" value="<?php echo esc_attr( get_option( 'inteaili_openai_api_key' ) ); ?>" class="regular-text inteaili-input" placeholder="sk-...">
						<p class="description">
							<?php
							printf(
								/* translators: %s: Link to the OpenAI Developer Dashboard. */
								esc_html__( 'Access your secret keys via the %s.', 'intelliagent-ai-lite' ),
								'<a href="' . esc_url( $inteaili_llm_providers['OpenAI']['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'OpenAI Developer Dashboard', 'intelliagent-ai-lite' ) . '</a>'
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inteaili_openai_model"><?php esc_html_e( 'OpenAI Model Selection', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<select id="inteaili_openai_model" name="inteaili_openai_model" class="regular-text inteaili-input">
							<option value="gpt-5.4" <?php selected( get_option( 'inteaili_openai_model' ), 'gpt-5.4' ); ?>>
								<?php esc_html_e( 'GPT-5.4 (Recommended - Latest Stable)', 'intelliagent-ai-lite' ); ?>
							</option>
							<option value="gpt-5.2" <?php selected( get_option( 'inteaili_openai_model' ), 'gpt-5.2' ); ?>>
								<?php esc_html_e( 'GPT-5.2 (Stable Reasoning)', 'intelliagent-ai-lite' ); ?>
							</option>
							<option value="gpt-4.1" <?php selected( get_option( 'inteaili_openai_model' ), 'gpt-4.1' ); ?>>
								<?php esc_html_e( 'GPT-4.1 (Stable - High Intelligence)', 'intelliagent-ai-lite' ); ?>
							</option>
							<option value="gpt-4.1-mini" <?php selected( get_option( 'inteaili_openai_model' ), 'gpt-4.1-mini' ); ?>>
								<?php esc_html_e( 'GPT-4.1 Mini (Stable - Fast)', 'intelliagent-ai-lite' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inteaili_openai_temperature"><?php esc_html_e( 'Sampling Temperature', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<input type="number" id="inteaili_openai_temperature" name="inteaili_openai_temperature" value="<?php echo esc_attr( get_option( 'inteaili_openai_temperature', '0.7' ) ); ?>" min="0" max="2" step="0.1" class="small-text inteaili-input">
						<p class="description"><?php esc_html_e( 'Fine-tune the creativity of the response. Use 0.0 for consistent, factual answers and 1.0 or higher for more creative or varied writing.', 'intelliagent-ai-lite' ); ?></p>
					</td>
				</tr>
			</tbody>

			<tbody id="settings-group-Gemini" class="llm-settings-group" style="display:none;">
				<tr style="background: #f8fafc;">
					<td colspan="2" style="padding: 10px 20px;">
						<strong><?php esc_html_e( 'Google Gemini Settings', 'intelliagent-ai-lite' ); ?></strong>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inteaili_gemini_api_key"><?php esc_html_e( 'Gemini API Key', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<input type="password" id="inteaili_gemini_api_key" name="inteaili_gemini_api_key" value="<?php echo esc_attr( get_option( 'inteaili_gemini_api_key' ) ); ?>" class="regular-text inteaili-input">
						<p class="description">
							<?php
							printf(
								/* translators: %s: Link to Google AI Studio dashboard. */
								esc_html__( 'Retrieve your API credentials from %s.', 'intelliagent-ai-lite' ),
								'<a href="' . esc_url( $inteaili_llm_providers['Gemini']['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Google AI Studio', 'intelliagent-ai-lite' ) . '</a>'
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inteaili_gemini_model"><?php esc_html_e( 'Gemini Model Selection', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<select id="inteaili_gemini_model" name="inteaili_gemini_model" class="regular-text inteaili-input">
							<option value="gemini-2.5-pro" <?php selected( get_option( 'inteaili_gemini_model' ), 'gemini-2.5-pro' ); ?>>
								<?php esc_html_e( 'Gemini 2.5 Pro (Recommended)', 'intelliagent-ai-lite' ); ?>
							</option>
							<option value="gemini-2.5-flash" <?php selected( get_option( 'inteaili_gemini_model' ), 'gemini-2.5-flash' ); ?>>
								<?php esc_html_e( 'Gemini 2.5 Flash (Stable - Optimized Speed)', 'intelliagent-ai-lite' ); ?>
							</option>
							<option value="gemini-3.1-pro-preview" <?php selected( get_option( 'inteaili_gemini_model' ), 'gemini-3.1-pro-preview' ); ?>>
								<?php esc_html_e( 'Gemini 3.1 Pro (Advanced Reasoning)', 'intelliagent-ai-lite' ); ?>
							</option>
							<option value="gemini-3.1-flash-lite-preview" <?php selected( get_option( 'inteaili_gemini_model' ), 'gemini-3.1-flash-lite-preview' ); ?>>
								<?php esc_html_e( 'Gemini 3.1 Flash-Lite (Fastest & Budget Friendly)', 'intelliagent-ai-lite' ); ?>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inteaili_gemini_temperature"><?php esc_html_e( 'Sampling Temperature', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<input type="number" id="inteaili_gemini_temperature" name="inteaili_gemini_temperature" value="<?php echo esc_attr( get_option( 'inteaili_gemini_temperature', '0.7' ) ); ?>" min="0" max="2" step="0.1" class="small-text inteaili-input">
						<p class="description"><?php esc_html_e( 'Fine-tune the creativity of the response. Use 0.0 for consistent, factual answers and 1.0 or higher for more creative or varied writing.', 'intelliagent-ai-lite' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php
submit_button(
	esc_html__( 'Save AI Configuration', 'intelliagent-ai-lite' ),
	'primary',
	'submit',
	true,
	array( 'id' => 'inteaili-main-submit' )
);
?>
</form>