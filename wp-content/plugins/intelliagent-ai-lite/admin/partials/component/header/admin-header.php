<?php
/**
 * Header Component Template
 *
 * This reusable component renders the administrative header for all
 * IntelliAgent AI plugin pages, including the logo, title, and action buttons.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials/Components
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* --- Component Variables with Prefixes --- */
$inteaili_header_title = isset( $inteaili_header_args['title'] ) ? $inteaili_header_args['title'] : '';
$inteaili_header_logo  = esc_url( INTEAILI_URL . 'assets/images/plugin-logo.jpg' );
$inteaili_header_ver   = defined( 'INTEAILI_VERSION' ) ? INTEAILI_VERSION : '1.0.0';
$inteaili_header_name  = defined( 'INTEAILI_NAME' ) ? INTEAILI_NAME : 'IntelliAgent_AI_Lite';
?>

<div class="inteaili-head-wrap">
	<div class="inteaili-head">
		<div class="inteaili-h-left">
			<div class="inteaili-l-box">
				<img src="<?php echo esc_url( $inteaili_header_logo ); ?>" class="inteaili-logo" alt="<?php echo esc_attr( $inteaili_header_name ); ?>">
			</div>
			<div class="inteaili-t-box">
				<h1 class="wp-heading-inline">
					<span class="inteaili-b-name"><?php echo esc_html( $inteaili_header_name ); ?></span> 
					<span class="inteaili-sep">/</span>
					<span class="inteaili-p-title"><?php echo esc_html( $inteaili_header_title ); ?></span>
				</h1>
			</div>
			<?php if ( isset( $inteaili_header_args['show_btn'] ) && $inteaili_header_args['show_btn'] ) : ?>
				<button type="button" class="inteaili-h-btn" id="<?php echo esc_attr( $inteaili_header_args['btn_id'] ); ?>">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php echo esc_html( $inteaili_header_args['btn_text'] ); ?>
				</button>
			<?php endif; ?>
		</div>
		
		<div class="inteaili-h-right">
			<div class="inteaili-actions">
				<span class="inteaili-v-tag">v<?php echo esc_html( $inteaili_header_ver ); ?></span>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=inteaili-support' ) ); ?>" class="inteaili-icon-btn" title="<?php esc_attr_e( 'Support', 'intelliagent-ai-lite' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><path d="M12 17h.01"></path></svg>
				</a>
				<a href="https://aica-intelliagent.com/" target="_blank" rel="noopener noreferrer" class="inteaili-pro-btn">
					<span class="dashicons dashicons-star-filled"></span>
					<?php esc_html_e( 'Upgrade to Pro', 'intelliagent-ai-lite' ); ?>
				</a>
			</div>
		</div>
	</div>
	<div class="inteaili-clear"></div>
	<hr class="wp-header-end">
</div>