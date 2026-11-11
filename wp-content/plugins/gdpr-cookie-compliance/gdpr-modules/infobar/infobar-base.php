<?php
/**
 * Infobar Base File Doc Comment
 *
 * @category Infobar Base
 * @package   gdpr-cookie-compliance
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( $content->show ) :
	?>
	<!--copyscapeskip-->
	<aside id="moove_gdpr_cookie_info_bar" class="<?php echo esc_attr( $content->class ); ?>" aria-labelledby="moove_gdpr_cookie_info_bar_title" tabindex="-1" style="display: none;">
	<div class="moove-gdpr-info-bar-container">
		<?php // Visually hidden: gives screen reader users a heading to find the banner by, without changing its design. role="heading" rather than <h2>, so it isn't added to every page's SEO outline. ?>
		<div id="moove_gdpr_cookie_info_bar_title" class="gdpr-sr-only" role="heading" aria-level="2"><?php esc_html_e( 'GDPR Cookie Banner', 'gdpr-cookie-compliance' ); ?></div>
		<div class="moove-gdpr-info-bar-content">
		<?php echo gdpr_get_module( 'infobar-content' ); // phpcs:ignore ?>
		<?php echo gdpr_get_module( 'infobar-buttons' ); // phpcs:ignore ?>
		</div>
		<!-- moove-gdpr-info-bar-content -->
	</div>
	<!-- moove-gdpr-info-bar-container -->
	</aside>
	<!-- #moove_gdpr_cookie_info_bar -->
	<!--/copyscapeskip-->
<?php endif; ?>
