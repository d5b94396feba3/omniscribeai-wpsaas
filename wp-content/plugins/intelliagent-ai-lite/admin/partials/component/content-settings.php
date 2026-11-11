<?php
/**
 * General Content Settings Template
 *
 * This template handles the configuration for the AI Assistant's basic
 * identity, including its name, primary language, and welcome messages.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials/Components
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* --- Fetch settings variables with Prefixes --- */
$inteaili_logo_id         = get_option( 'inteaili_logo_id' );
$inteaili_logo_url        = $inteaili_logo_id ? wp_get_attachment_url( $inteaili_logo_id ) : INTEAILI_URL . 'assets/images/default-avater.svg';
$inteaili_display_name    = get_option( 'inteaili_display_name', __( 'AI Assistant', 'intelliagent-ai-lite' ) );
$inteaili_welcome_msg     = get_option( 'inteaili_welcome_msg', __( "Hi there! I'm your friendly AI Assistant.\nHow can I help you today?", 'intelliagent-ai-lite' ) );
$inteaili_placeholder     = get_option( 'inteaili_placeholder', __( 'Type a message...', 'intelliagent-ai-lite' ) );
$inteaili_primary_color   = get_option( 'inteaili_primary_color', '#000000' );
$inteaili_theme_mode      = get_option( 'inteaili_theme_mode', 'light' );
$inteaili_system_language = get_option( 'inteaili_system_language', 'English' );
?>

<form method="post" action="options.php">
	<?php
	/**
	 * Settings Fields
	 * Output nonce, action, and option_page fields for a settings page.
	 */
	settings_fields( 'inteaili_content_settings' );
	?>
	
	<div class="postbox inteaili-box">
		<div class="postbox-header">
			<h2 class="hndle"><?php esc_html_e( 'General Content', 'intelliagent-ai-lite' ); ?></h2>
		</div>
		<div class="inside">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="inteaili_display_name"><?php esc_html_e( 'Display Name', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<input type="text" id="inteaili_display_name" name="inteaili_display_name" value="<?php echo esc_attr( $inteaili_display_name ); ?>" class="large-text inteaili-input admin-preview-trigger">
						<p class="description"><?php esc_html_e( 'This name appears at the top of the chat window and above AI responses.', 'intelliagent-ai-lite' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="inteaili_welcome_msg"><?php esc_html_e( 'Initial Welcome Message', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<textarea id="inteaili_welcome_msg" name="inteaili_welcome_msg" rows="3" class="large-text inteaili-input admin-preview-trigger"><?php echo esc_textarea( $inteaili_welcome_msg ); ?></textarea>
						<p class="description"><?php esc_html_e( 'The first message the user sees. Use new lines to create multiple chat bubbles.', 'intelliagent-ai-lite' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inteaili_placeholder"><?php esc_html_e( 'Message Placeholder', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<input type="text" id="inteaili_placeholder" name="inteaili_placeholder" value="<?php echo esc_attr( $inteaili_placeholder ); ?>" class="large-text inteaili-input admin-preview-trigger">
						<p class="description"><?php esc_html_e( 'The hint text shown in the input box before the user starts typing.', 'intelliagent-ai-lite' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="inteaili_show_branding"><?php esc_html_e( 'Display Attribution', 'intelliagent-ai-lite' ); ?></label>
					</th>
					<td>
						<label class="inteaili-switch">
							<input type="checkbox" id="inteaili_show_branding" name="inteaili_show_branding" value="1" <?php checked( 1, get_option( 'inteaili_show_branding', 0 ) ); ?> class="admin-preview-trigger">
							<span class="inteaili-slider round"></span>
						</label>
						<p class="description">
							<?php esc_html_e( 'Show "Powered by IntelliAgent AI" at the bottom of the chat widget.', 'intelliagent-ai-lite' ); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	<?php submit_button( esc_html__( 'Save Content Settings', 'intelliagent-ai-lite' ), 'primary', 'submit', true, array( 'id' => 'inteaili-main-submit' ) ); ?>
</form>