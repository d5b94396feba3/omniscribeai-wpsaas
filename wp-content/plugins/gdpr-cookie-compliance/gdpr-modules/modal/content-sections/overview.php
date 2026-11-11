<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<div id="privacy_overview" class="moove-gdpr-tab-main" <?php echo gdpr_get_tabpanel_attributes( 'privacy_overview' ); // phpcs:ignore ?>>
	<?php if ( $content->tab_title ) : ?>
	<?php // Not an <h2>: role="heading" keeps this a heading for screen readers without adding H2s to every page's SEO outline. ?>
	<div class="tab-title" role="heading" aria-level="2"><span class="gdpr-tab-title-text"><?php echo esc_attr( $content->tab_title ); ?></span></div>
	<?php endif; ?>
	<div class="moove-gdpr-tab-main-content">
	<?php echo $content->tab_content; // phpcs:ignore ?>
	<?php do_action( 'gdpr_modules_content_extension', $content, 'overview' ); ?>
	</div>
	<!--  .moove-gdpr-tab-main-content -->

</div>
<!-- #privacy_overview -->
