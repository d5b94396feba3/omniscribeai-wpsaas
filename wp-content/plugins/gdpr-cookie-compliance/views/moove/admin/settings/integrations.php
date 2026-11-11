<?php
/**
 * Integrations Doc Comment
 *
 * @category  Views
 * @package   gdpr-cookie-compliance
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

	$gdpr_default_content = new Moove_GDPR_Content();
	$option_name          = $gdpr_default_content->moove_gdpr_get_option_name();
	$gdpr_options         = get_option( $option_name );
	$wpml_lang            = $gdpr_default_content->moove_gdpr_get_wpml_lang();
	$gdpr_options         = is_array( $gdpr_options ) ? $gdpr_options : array();

if ( isset( $_POST ) && isset( $_POST['moove_gdpr_nonce'] ) ) :
	$nonce = sanitize_key( $_POST['moove_gdpr_nonce'] );
	if ( ! wp_verify_nonce( $nonce, 'moove_gdpr_nonce_field' ) ) :
		die( 'Security check' );
	else :
		$gdin_saved_values  = isset( $gdpr_options['gdin_values'] ) ? json_decode( $gdpr_options['gdin_values'], true ) : array();
		$gdin_saved_values  = is_array( $gdin_saved_values ) ? $gdin_saved_values : array();
		$gdin_known_modules = gdpr_get_integration_modules( $gdpr_options, $gdin_saved_values );

		/**
		 * Locked rows post nothing, so their stored settings would be wiped on save.
		 * Carry them over instead - a lapsed licence should not destroy the config.
		 */
		$gdin_preserved = array();
		foreach ( $gdin_known_modules as $_gdin_slug => $_gdin_module ) :
			if ( ! empty( $_gdin_module['available'] ) ) :
				continue;
			endif;
			if ( isset( $gdin_saved_values[ $_gdin_slug ] ) ) :
				$gdin_preserved[ $_gdin_slug ] = $gdin_saved_values[ $_gdin_slug ];
			endif;
			if ( isset( $gdin_saved_values[ $_gdin_slug . '_id' ] ) ) :
				$gdin_preserved[ $_gdin_slug . '_id' ] = $gdin_saved_values[ $_gdin_slug . '_id' ];
			endif;
		endforeach;

		if ( isset( $_POST['gdpr_integrations'] ) && is_array( $_POST['gdpr_integrations'] ) ) :
			$gdpr_gdin_values = $gdin_preserved;

			$third_party_allowed = isset( $gdpr_options['moove_gdpr_third_party_cookies_enable'] ) && intval( $gdpr_options['moove_gdpr_third_party_cookies_enable'] ) === 1;
			$advanced_allowed    = isset( $gdpr_options['moove_gdpr_advanced_cookies_enable'] ) && intval( $gdpr_options['moove_gdpr_advanced_cookies_enable'] ) === 1;
			foreach ( $_POST['gdpr_integrations'] as $_gdin_slug ) : // phpcs:ignore
				$gdin_slug   = sanitize_text_field( wp_unslash( $_gdin_slug ) );
				$tracking_id = isset( $_POST[ 'gdpr_integrations_' . $gdin_slug . '_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'gdpr_integrations_' . $gdin_slug . '_id' ] ) ) : false;

				// A locked premium teaser must never be persisted, whatever was posted.
				if ( isset( $gdin_known_modules[ $gdin_slug ] ) && empty( $gdin_known_modules[ $gdin_slug ]['available'] ) ) :
					continue;
				endif;

				// Blocker modules suppress a third-party script and have no tracking ID.
				$gdin_needs_id = isset( $gdin_known_modules[ $gdin_slug ] ) ? gdpr_integration_module_needs_id( $gdin_known_modules[ $gdin_slug ] ) : true;

				if ( $tracking_id || ! $gdin_needs_id ) :
					$gdin_value = isset( $_POST[ 'gdpr_integrations_' . $gdin_slug ] ) && intval( $_POST[ 'gdpr_integrations_' . $gdin_slug ] ) ? intval( $_POST[ 'gdpr_integrations_' . $gdin_slug ] ) : 2;
					if ( 2 === $gdin_value && ! $third_party_allowed ) :
						$gdpr_options['moove_gdpr_third_party_cookies_enable'] = 1;
					endif;

					if ( 3 === $gdin_value && ! $advanced_allowed ) :
						$gdpr_options['moove_gdpr_advanced_cookies_enable'] = 1;
					endif;

					$gdpr_gdin_values[ $gdin_slug ] = $gdin_value;
					if ( $gdin_needs_id ) :
						$gdpr_gdin_values[ $gdin_slug . '_id' ] = $tracking_id;
					endif;
				endif;
			endforeach;
			$gdpr_options['gdin_values'] = json_encode( $gdpr_gdin_values ); // phpcs:ignore
		else :
			$gdpr_options['gdin_values'] = json_encode( $gdin_preserved ); // phpcs:ignore
		endif;

		update_option( $option_name, $gdpr_options );
		$gdpr_options = get_option( $option_name );
		if ( ! empty( $gdpr_gdin_values ) && isset( $gdpr_gdin_values['gtm4wp'] ) ) :
			if ( defined( 'GTM4WP_OPTIONS' ) ) :
				$storedoptions = (array) get_option( GTM4WP_OPTIONS );
				if ( defined( 'GTM4WP_OPTION_GTM_PLACEMENT' ) && defined( 'GTM4WP_PLACEMENT_OFF' ) ) :
					$storedoptions[ GTM4WP_OPTION_GTM_PLACEMENT ] = GTM4WP_PLACEMENT_OFF;
					update_option( GTM4WP_OPTIONS, $storedoptions );
			endif;
		endif;
		endif;

		do_action( 'gdpr_cookie_filter_settings' );
		?>
		<script>
			jQuery('#moove-gdpr-setting-error-settings_updated').show();
		</script>
		<?php
	endif;
endif;

$nav_label_1 = isset( $gdpr_options[ 'moove_gdpr_strictly_necessary_cookies_tab_title' . $wpml_lang ] ) && $gdpr_options[ 'moove_gdpr_strictly_necessary_cookies_tab_title' . $wpml_lang ] ? $gdpr_options[ 'moove_gdpr_strictly_necessary_cookies_tab_title' . $wpml_lang ] : __( 'Necessary', 'gdpr-cookie-compliance' );

$nav_label_2 = isset( $gdpr_options[ 'moove_gdpr_performance_cookies_tab_title' . $wpml_lang ] ) && $gdpr_options[ 'moove_gdpr_performance_cookies_tab_title' . $wpml_lang ] ? $gdpr_options[ 'moove_gdpr_performance_cookies_tab_title' . $wpml_lang ] : __( 'Analytics', 'gdpr-cookie-compliance' );

$nav_label_3 = isset( $gdpr_options[ 'moove_gdpr_advanced_cookies_tab_title' . $wpml_lang ] ) && $gdpr_options[ 'moove_gdpr_advanced_cookies_tab_title' . $wpml_lang ] ? $gdpr_options[ 'moove_gdpr_advanced_cookies_tab_title' . $wpml_lang ] : __( 'Marketing', 'gdpr-cookie-compliance' );

$nav_label_4 = isset( $gdpr_options[ 'moove_gdpr_performance_ccat_tab_title' . $wpml_lang ] ) && $gdpr_options[ 'moove_gdpr_performance_ccat_tab_title' . $wpml_lang ] ? $gdpr_options[ 'moove_gdpr_performance_ccat_tab_title' . $wpml_lang ] : __( 'Performance', 'gdpr-cookie-compliance' );

$nav_label_5 = isset( $gdpr_options[ 'moove_gdpr_preference_ccat_tab_title' . $wpml_lang ] ) && $gdpr_options[ 'moove_gdpr_preference_ccat_tab_title' . $wpml_lang ] ? $gdpr_options[ 'moove_gdpr_preference_ccat_tab_title' . $wpml_lang ] : __( 'Preferences', 'gdpr-cookie-compliance' );
?>
	<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>?page=moove-gdpr&tab=integrations&gcat=integrations" method="post">
	<?php wp_nonce_field( 'moove_gdpr_nonce_field', 'moove_gdpr_nonce' ); ?>
	<h2><?php echo $wpml_lang ? esc_html__( 'Integrations for all languages', 'gdpr-cookie-compliance' ) : esc_html__( 'Integrations', 'gdpr-cookie-compliance' ); ?></h2>
	<h4 class="description">
		<?php esc_html_e( 'Connect your GA/GTM or Meta Pixel tracking easily to the cookie categories used by this plugin.', 'gdpr-cookie-compliance' ); ?> 
		<br>
		<?php esc_html_e( 'Using the settings below, you can ensure that the tracking scripts will be loaded only if your website visitors accept the selected cookie category.', 'gdpr-cookie-compliance' ); ?>
		<br/> 
		<?php esc_html_e( 'Please ensure that you remove any hard-coded GA/GTM or Facebook/Meta Pixel scripts from your website theme or other plugins (including any other parts of this plugin) to prevent duplicate activation of the same scripts.', 'gdpr-cookie-compliance' ); ?>
		<?php if ( $wpml_lang ) : ?>
		<br/>
			<?php esc_html_e( 'If you enable these integrations, they will be applied to all your language variations.' ); ?>
		<?php endif; ?>
	</h4>
	<table class="form-table">
		<tbody>

		<tr>
			<td colspan="2" style="padding: 0;">
			<table class="gdpr-gsk-table">
				<thead>
				<tr>
					<th>Integration</th>
					<th>Status</th>
					<th>Tracking ID</th>
					<th>Cookie Category</th>
				</tr>
				</thead>
				<tbody>
				<?php
					$gdin_values  = isset( $gdpr_options['gdin_values'] ) ? json_decode( $gdpr_options['gdin_values'], true ) : array();
					$gdin_modules = gdpr_get_integration_modules( $gdpr_options, $gdin_values );

				if ( ! empty( $gdin_modules ) && is_array( $gdin_modules ) ) :
					foreach ( $gdin_modules as $_gdin_module_slug => $_gdin_module ) :
						?>
						<?php $gdin_locked = empty( $_gdin_module['available'] ); ?>
						<tr class="<?php echo $gdin_locked ? 'gdpr-integration-locked' : ''; ?>">
							<td>
							<strong><?php echo esc_attr( $_gdin_module['name'] ); ?></strong><?php echo gdpr_integration_module_badge( $_gdin_module ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br>
							<small><?php echo esc_attr( $_gdin_module['desc'] ); ?></small>
							<?php if ( $gdin_locked ) : ?>
							<br><a href="<?php echo esc_url( gdpr_get_premium_upgrade_url() ); ?>" target="_blank" rel="noopener" class="gdpr_admin_link gdpr-integration-upgrade"><?php esc_html_e( 'Upgrade to unlock', 'gdpr-cookie-compliance' ); ?> &rsaquo;</a>
							<?php endif; ?>
							</td>
							<td>
							<label class="gdpr-checkbox-toggle">
							<input type="checkbox" id="gdpr_integrations_<?php echo esc_attr( $_gdin_module_slug ); ?>" name="gdpr_integrations[]" <?php echo $_gdin_module['status'] ? 'checked' : ''; ?> <?php echo $gdin_locked ? 'disabled' : ''; ?> id="gdpr_integrations" value="<?php echo esc_attr( $_gdin_module_slug ); ?>" >
							<span class="gdpr-checkbox-slider" data-enable="<?php esc_html_e( 'Enabled', 'gdpr-cookie-compliance' ); ?>" data-disable="<?php esc_html_e( 'Disabled', 'gdpr-cookie-compliance' ); ?>"></span>
							</label>
							</td>
							<td>
							<?php if ( gdpr_integration_module_needs_id( $_gdin_module ) ) : ?>
							<div class="gdpr-conditional-field" data-dependency="#gdpr_integrations_<?php echo esc_attr( $_gdin_module_slug ); ?>">
								<input type="text" class="regular-text" <?php echo isset( $_gdin_module['atts'] ) && isset( $_gdin_module['atts']['input'] ) ? $_gdin_module['atts']['input'] : ''; ?> name="gdpr_integrations_<?php echo esc_attr( $_gdin_module_slug ); ?>_id" placeholder="<?php echo $_gdin_module['id_format']; ?>" value="<?php echo isset( $_gdin_module['tacking_id'] ) && $_gdin_module['tacking_id'] ? esc_attr( $_gdin_module['tacking_id'] ) : ''; ?>">
								<?php if ( isset( $_gdin_module['atts'] ) && isset( $_gdin_module['atts']['input'] ) && 'disabled' === $_gdin_module['atts']['input'] ) : ?>
								<input type="hidden" name="gdpr_integrations_<?php echo esc_attr( $_gdin_module_slug ); ?>_id" value="<?php echo isset( $_gdin_module['tacking_id'] ) && $_gdin_module['tacking_id'] ? esc_attr( $_gdin_module['tacking_id'] ) : ''; ?>">
								<?php endif; ?>
							</div>
							<!-- .gdpr-conditional-field -->
							<?php else : ?>
							<small class="description">&mdash;</small>
							<?php endif; ?>
							</td>
							<td>
							<?php if ( $gdin_locked ) : ?>
							<small class="description">&mdash;</small>
							<?php else : ?>
							<div class="gdpr-conditional-field" data-dependency="#gdpr_integrations_<?php echo esc_attr( $_gdin_module_slug ); ?>">
								<?php
									// Modules may opt into extra categories - e.g. a blocker whose
									// script the site owner considers strictly necessary.
									$gdin_cookie_cats = isset( $_gdin_module['cookie_cats'] ) && is_array( $_gdin_module['cookie_cats'] ) ? $_gdin_module['cookie_cats'] : array( 2, 3, 4, 5 );
								?>
								<select name="gdpr_integrations_<?php echo esc_attr( $_gdin_module_slug ); ?>" id="gdpr_integrations_<?php echo esc_attr( $_gdin_module_slug ); ?>">
								<?php if ( in_array( 1, $gdin_cookie_cats, true ) ) : ?>
								<option value="1" <?php echo isset( $_gdin_module['cookie_cat'] ) && intval( $_gdin_module['cookie_cat'] ) === 1 ? 'selected' : ''; ?> ><?php echo esc_attr( $nav_label_1 ); ?></option>
								<?php endif; ?>
								<option value="2" <?php echo isset( $_gdin_module['cookie_cat'] ) && intval( $_gdin_module['cookie_cat'] ) === 2 ? 'selected' : ''; ?> ><?php echo esc_attr( $nav_label_2 ); ?></option>
								<option value="3" <?php echo isset( $_gdin_module['cookie_cat'] ) && intval( $_gdin_module['cookie_cat'] ) === 3 ? 'selected' : ''; ?>><?php echo esc_attr( $nav_label_3 ); ?></option>

								<?php $disabled = ! defined( 'GDPR_ADDON_VERSION' ); ?>

								<option value="4" <?php echo isset( $_gdin_module['cookie_cat'] ) && intval( $_gdin_module['cookie_cat'] ) === 4 ? 'selected' : ''; ?> <?php echo $disabled ? 'disabled' : ''; ?> ><?php echo esc_attr( $nav_label_4 ); ?></option>

								<option value="5" <?php echo isset( $_gdin_module['cookie_cat'] ) && intval( $_gdin_module['cookie_cat'] ) === 5 ? 'selected' : ''; ?> <?php echo $disabled ? 'disabled' : ''; ?>><?php echo esc_attr( $nav_label_5 ); ?></option>


								</select>
								<!-- # -->
							</div>
							<!-- .gdpr-conditional-field -->
							<?php endif; ?>
							</td>
						</tr>
						<?php
						endforeach;
					?>
					<?php
					else :
						?>
						<tr>
						<td colspan="8"><?php esc_html_e( 'No integration modules found.', 'gdpr-cookie-compliance' ); ?></td>
						</tr>
						<?php
					endif;

					?>
				</tbody>
			</table>
			</td>
		</tr>
		</tbody>
	</table>

	<?php do_action( 'gdpr_below_integrations_table' ); ?>
	<br>
	<hr />
	<br />
	<button type="submit" class="button button-primary"><?php esc_html_e( 'Save changes', 'gdpr-cookie-compliance' ); ?></button>
	<?php do_action( 'gdpr_cc_banner_buttons_settings' ); ?>
	</form>
