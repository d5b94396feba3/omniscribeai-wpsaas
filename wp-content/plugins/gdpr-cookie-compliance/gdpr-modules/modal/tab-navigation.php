<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<li class="menu-item-on menu-item-privacy_overview menu-item-selected" role="presentation">
	<button data-href="#privacy_overview" id="gdpr-tab-privacy_overview" class="moove-gdpr-tab-nav" role="tab" aria-selected="true" aria-controls="privacy_overview" aria-label="<?php echo esc_attr( $content->overview->nav_label ); ?>">
	<span class="gdpr-nav-tab-title"><?php echo esc_attr( $content->overview->nav_label ); ?></span>
	</button>
</li>

<?php if ( $content->strictly->show ) : ?>
	<li class="menu-item-strict-necessary-cookies menu-item-off" role="presentation">
	<button data-href="#strict-necessary-cookies" id="gdpr-tab-strict-necessary-cookies" class="moove-gdpr-tab-nav" role="tab" aria-selected="false" aria-controls="strict-necessary-cookies" tabindex="-1" aria-label="<?php echo esc_attr( $content->strictly->nav_label ); ?>">
		<span class="gdpr-nav-tab-title"><?php echo esc_attr( $content->strictly->nav_label ); ?></span>
	</button>
	</li>
<?php endif; ?>


<?php if ( $content->third_party->show ) : ?>
	<li class="menu-item-off menu-item-third_party_cookies" role="presentation">
	<button data-href="#third_party_cookies" id="gdpr-tab-third_party_cookies" class="moove-gdpr-tab-nav" role="tab" aria-selected="false" aria-controls="third_party_cookies" tabindex="-1" aria-label="<?php echo esc_attr( $content->third_party->nav_label ); ?>">
		<span class="gdpr-nav-tab-title"><?php echo esc_attr( $content->third_party->nav_label ); ?></span>
	</button>
	</li>
<?php endif; ?>

<?php if ( $content->advanced->show ) : ?>
	<li class="menu-item-advanced-cookies menu-item-off" role="presentation">
	<button data-href="#advanced-cookies" id="gdpr-tab-advanced-cookies" class="moove-gdpr-tab-nav" role="tab" aria-selected="false" aria-controls="advanced-cookies" tabindex="-1" aria-label="<?php echo esc_attr( $content->advanced->nav_label ); ?>">
		<span class="gdpr-nav-tab-title"><?php echo esc_attr( $content->advanced->nav_label ); ?></span>
	</button>
	</li>
<?php endif; ?>

<?php do_action( 'tab_nav_category_extension' ); ?>

<?php if ( $content->cookiepolicy->show ) : ?>
	<li class="menu-item-moreinfo menu-item-off" role="presentation">
	<button data-href="#cookie_policy_modal" id="gdpr-tab-cookie_policy_modal" class="moove-gdpr-tab-nav" role="tab" aria-selected="false" aria-controls="cookie_policy_modal" tabindex="-1" aria-label="<?php echo esc_attr( $content->cookiepolicy->nav_label ); ?>">
		<span class="gdpr-nav-tab-title"><?php echo esc_attr( $content->cookiepolicy->nav_label ); ?></span>
	</button>
	</li>
<?php endif; ?>
