<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<?php if ( $content->show ) : ?>
	<div id="cookie_policy_modal" class="moove-gdpr-tab-main" <?php echo $content->visibility; // phpcs:ignore ?> <?php echo gdpr_get_tabpanel_attributes( 'cookie_policy_modal' ); // phpcs:ignore ?>>
	<?php // Not an <h2>: role="heading" keeps this a heading for screen readers without adding H2s to every page's SEO outline. ?>
	<div class="tab-title" role="heading" aria-level="2"><span class="gdpr-tab-title-text"><?php echo esc_attr( $content->tab_title ); ?></span></div>
	<div class="moove-gdpr-tab-main-content">
		<?php echo $content->tab_content; // phpcs:ignore ?>
		<?php do_action( 'gdpr_modules_content_extension', $content, 'cookiepolicy' ); ?> 
	</div>
	<!--  .moove-gdpr-tab-main-content -->
	</div>
<?php endif; ?>
