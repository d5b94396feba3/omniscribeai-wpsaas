<?php
/**
 * AI Behavior & Persona Settings Template
 *
 * This template handles the configuration of the AI's core instructions,
 * personality, and rules. Includes an AI-powered auto-generation feature.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials/Components
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<form method="post" action="options.php">
	<?php
	/**
	 * Settings Fields
	 * Output nonce, action, and option_page fields for prompt settings.
	 */
	settings_fields( 'inteaili_ai_persona_settings' );
	?>
	
	<div class="postbox inteaili-box">
		<div class="postbox-header">
			<h2 class="hndle"><?php esc_html_e( 'AI Behavior & Persona', 'intelliagent-ai-lite' ); ?></h2>
		</div>
		<div class="inside">
			<div style="margin-bottom: 20px; padding: 0 10px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<label for="inteaili_ai_persona_area" style="font-weight: 700; font-size: 14px; color: #1e293b;">
						<?php esc_html_e( 'System Role Instructions', 'intelliagent-ai-lite' ); ?>
					</label>
				</div>

				<p class="description" style="margin-bottom: 15px;">
					<?php esc_html_e( "Define how the AI should act, its tone of voice, and any specific constraints it must follow. This acts as the 'brain' for all agent interactions.", 'intelliagent-ai-lite' ); ?>
				</p>
				
				<div id="prompt-gen-loader" style="display:none; background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 30px; text-align: center; margin-bottom: 15px;">
					<div class="inteaili-loader-circle" style="border-top-color: #6366f1;"></div>
					<p style="margin-top: 15px; font-weight: 600; color: #475569;">
						<?php esc_html_e( 'Analyzing website data to craft a custom persona...', 'intelliagent-ai-lite' ); ?>
					</p>
				</div>

				<?php
				/*
				--- Prompt Data Processing --- */
				// Default value set to a helpful placeholder.
				$inteaili_default_prompt = __( 'You are a helpful AI assistant for this website...', 'intelliagent-ai-lite' );
				$inteaili_system_prompt  = get_option( 'inteaili_ai_persona', $inteaili_default_prompt );
				?>
				<textarea id="inteaili_ai_persona_area" name="inteaili_ai_persona" rows="14" class="large-text inteaili-input" style="font-family: 'SFMono-Regular', Consolas, monospace; font-size: 13px; line-height: 1.6; background: #fafafa; transition: all 0.3s ease;" placeholder="<?php esc_attr_e( 'Enter system instructions here...', 'intelliagent-ai-lite' ); ?>"><?php echo esc_textarea( $inteaili_system_prompt ); ?></textarea>
				
				<p class="description" style="margin-top: 10px;">
					<?php esc_html_e( 'Tip: Leave this field empty to use the default AI Persona for the system.', 'intelliagent-ai-lite' ); ?>
				</p>
			</div>
		</div>
	</div>
	<?php
	submit_button(
		esc_html__( 'Update AI Persona', 'intelliagent-ai-lite' ),
		'primary',
		'submit',
		true,
		array( 'id' => 'inteaili-main-submit' )
	);
	?>
</form>