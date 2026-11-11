<?php
/**
 * Visual Appearance Settings Template
 *
 * This template handles the customization of the chat widget's UI, including
 * avatar uploads, brand color selection, and theme mode (Light/Dark).
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
 * Output nonce, action, and option_page fields for style settings.
 */
settings_fields( 'inteaili_style_settings' );
?>
<div class="postbox inteaili-box">
	<div class="postbox-header">
		<h2 class="hndle"><?php esc_html_e( 'Visual Appearance', 'intelliagent-ai-lite' ); ?></h2>
	</div>
	<div class="inside">
		<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label><?php esc_html_e( 'Profile Image (Avatar)', 'intelliagent-ai-lite' ); ?></label>
			</th>
			<td>
				<?php
				/* --- Avatar Data Processing with Prefixes --- */
				$inteaili_logo_id  = get_option( 'inteaili_logo_id' );
				$inteaili_logo_url = $inteaili_logo_id ? wp_get_attachment_url( $inteaili_logo_id ) : '';
				?>
				<div id="logo-preview-wrap" style="margin-bottom: 12px;">
				<img id="inteaili_logo_preview" 
					src="<?php echo $inteaili_logo_url ? esc_url( $inteaili_logo_url ) : esc_url( INTEAILI_URL . 'assets/images/default-avater.svg' ); ?>" 
					style="max-width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 2px solid #f3f4f6; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
					alt="<?php esc_attr_e( 'AI Avatar Preview', 'intelliagent-ai-lite' ); ?>">
				</div>
				<input type="hidden" name="inteaili_logo_id" id="inteaili_logo_id" value="<?php echo esc_attr( $inteaili_logo_id ); ?>">
				
				<div style="margin-bottom: 10px;">
					<button type="button" class="button button-secondary" id="inteaili_upload_btn"><?php esc_html_e( 'Change Avatar', 'intelliagent-ai-lite' ); ?></button>
					<button type="button" class="button button-link-delete" id="inteaili_remove_btn" style="<?php echo ( $inteaili_logo_id ) ? '' : 'display:none;'; ?> margin-left: 10px;"><?php esc_html_e( 'Remove Image', 'intelliagent-ai-lite' ); ?></button>
				</div>
				
				<p class="description">
					<?php
					printf(
						/* translators: 1: Image formats, 2: Open strong tag, 3: Close strong tag */
						esc_html__( '%1$s, up to 1MB. Ideal size: %2$s535 x 535 pixels%3$s.', 'intelliagent-ai-lite' ),
						'JPG, PNG, or SVG',
						'<strong>',
						'</strong>'
					);
					?>
					<br>
					<?php esc_html_e( 'Used as the AI icon in chat responses and optionally on the chat trigger.', 'intelliagent-ai-lite' ); ?>
				</p>
			</td>
		</tr>
			<tr>
				<th scope="row">
					<label for="inteaili_bubble_icon"><?php esc_html_e( 'Chat Launcher Icon', 'intelliagent-ai-lite' ); ?></label>
				</th>
				<td>
					<select id="inteaili_bubble_icon" name="inteaili_bubble_icon" class="regular-text inteaili-input admin-preview-trigger">
						<option value="default" <?php selected( get_option( 'inteaili_bubble_icon', 'default' ), 'default' ); ?>><?php esc_html_e( 'Default Message Icon', 'intelliagent-ai-lite' ); ?></option>
						<option value="logo" <?php selected( get_option( 'inteaili_bubble_icon' ), 'logo' ); ?>><?php esc_html_e( 'Use Profile Image', 'intelliagent-ai-lite' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Select what displays on the floating button at the bottom of the screen.', 'intelliagent-ai-lite' ); ?></p>
				</td>
			</tr>
			<tr>
			<th scope="row">
				<label for="inteaili_primary_color_input"><?php esc_html_e( 'Primary Brand Color', 'intelliagent-ai-lite' ); ?></label>
			</th>
			<td>
				<?php $inteaili_primary_color = get_option( 'inteaili_primary_color', '#000000' ); ?>
				<div style="display: flex; align-items: center; gap: 12px;">
					<input type="color" 
						id="inteaili_primary_color_input" 
						name="inteaili_primary_color" 
						value="<?php echo esc_attr( $inteaili_primary_color ); ?>" 
						style="height:45px; width:80px; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer;" class="admin-preview-trigger">
					
					<code id="inteaili_primary_color_hex" style="font-size: 14px; padding: 5px 10px;"><?php echo esc_html( $inteaili_primary_color ); ?></code>
					
					<button type="button"
							id="reset_primary_color"
							class="button button-secondary"
							title="<?php esc_attr_e( 'Reset to Black', 'intelliagent-ai-lite' ); ?>" 
							style="display: flex; align-items: center; justify-content: center; padding: 0 8px; height: 32px;">
						<span class="dashicons dashicons-undo" style="font-size: 18px; width: 18px; height: 18px; margin-top: -15px;"></span>
					</button>
				</div>
				<p class="description"><?php esc_html_e( 'This color is used for the chat bubble launcher and user message backgrounds.', 'intelliagent-ai-lite' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label><?php esc_html_e( 'Widget Theme Mode', 'intelliagent-ai-lite' ); ?></label>
			</th>
			<td>
				<div class="inteaili-theme-toggle-group" style="display: flex; gap: 20px;">
					<?php
					$inteaili_current_theme = get_option( 'inteaili_theme_mode', 'light' );
					$inteaili_theme_modes   = array(
						'light' => __( 'Light Mode', 'intelliagent-ai-lite' ),
						'dark'  => __( 'Dark Mode', 'intelliagent-ai-lite' ),
					);
					foreach ( $inteaili_theme_modes as $inteaili_theme_value => $inteaili_theme_label ) :
						$inteaili_theme_is_active = ( $inteaili_current_theme === $inteaili_theme_value );
						?>
						<label class="inteaili-theme-card <?php echo $inteaili_theme_is_active ? 'is-active' : ''; ?>" style="flex: 1; max-width: 180px; cursor: pointer;">
							<input type="radio" name="inteaili_theme_mode" value="<?php echo esc_attr( $inteaili_theme_value ); ?>" <?php checked( $inteaili_current_theme, $inteaili_theme_value ); ?> class="admin-preview-trigger" style="display: none;">
							
							<div class="theme-card-inner">
								<div class="mini-preview-canvas theme-<?php echo esc_attr( $inteaili_theme_value ); ?>">
									<div class="mini-widget">
										<div class="mini-header"></div>
										<div class="mini-content">
											<div class="mini-bubble-ai"></div>
											<div class="mini-bubble-user"></div>
										</div>
										<div class="mini-footer"></div>
									</div>
								</div>
								
								<div class="theme-label-container">
									<div class="theme-label-text"><?php echo esc_html( $inteaili_theme_label ); ?></div>
								</div>
							</div>
						</label>
					<?php endforeach; ?>
				</div>
				<p class="description"><?php esc_html_e( 'Select the interface style. The preview on the right will update instantly.', 'intelliagent-ai-lite' ); ?></p>
			</td>
		</tr>
		</table>
	</div>
</div>
<?php
submit_button(
	esc_html__( 'Save Visual Style', 'intelliagent-ai-lite' ),
	'primary',
	'submit',
	true,
	array( 'id' => 'inteaili-main-submit' )
);
?>
</form>