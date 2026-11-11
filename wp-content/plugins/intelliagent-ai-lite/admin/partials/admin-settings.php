<?php
/**
 * Settings Page Template
 *
 * This template handles the main settings interface, incorporating
 * tabbed navigation for content, style, sync, and model configurations.
 *
 * @package           IntelliAgent_AI
 * @subpackage        Admin/Partials
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/*
--- Data Preparation with Prefixes --- */
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$inteaili_settings_active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'content';
$inteaili_settings_page_url   = admin_url( 'admin.php?page=inteaili-settings' );
$inteaili_settings_last_sync  = get_option( 'inteaili_last_cron_sync' );
$inteaili_settings_next_sync  = wp_next_scheduled( 'inteaili_daily_sync_event' );
?>

<div class="wrap inteaili-ad-wrap">

	<?php
	/**
	 * Settings Header Arguments
	 *
	 * @var array $inteaili_header_args Configuration for the admin-header component.
	 */
	$inteaili_header_args = array(
		'title'    => __( 'Settings', 'intelliagent-ai-lite' ),
		'show_btn' => false,
		'btn_id'   => '',
		'btn_text' => __( 'add', 'intelliagent-ai-lite' ),
	);
	require INTEAILI_PATH . 'admin/partials/component/header/admin-header.php';
	?>

	<div id="inteaili-unsaved-bar" class="inteaili-unsaved-bar-fixed">
		<div class="inteaili-unsaved-content">
			<span class="dashicons dashicons-warning inteaili-warn-icon"></span>
			<span class="inteaili-warn-text"><?php esc_html_e( 'You have unsaved changes', 'intelliagent-ai-lite' ); ?></span>
		</div>
		<div class="inteaili-unsaved-actions">
			<button type="button" onclick="location.reload();" class="button inteaili-btn-discard"><?php esc_html_e( 'Discard', 'intelliagent-ai-lite' ); ?></button>
			<button type="button" id="inteaili-sticky-save-trigger" class="button button-primary inteaili-btn-save-sticky"><?php esc_html_e( 'Save Changes', 'intelliagent-ai-lite' ); ?></button>
		</div>
	</div>

	<nav class="nav-tab-wrapper">
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'content', $inteaili_settings_page_url ) ); ?>" class="nav-tab <?php echo esc_attr( 'content' === $inteaili_settings_active_tab ? 'nav-tab-active' : '' ); ?>">
			<?php esc_html_e( 'Content', 'intelliagent-ai-lite' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'style', $inteaili_settings_page_url ) ); ?>" class="nav-tab <?php echo esc_attr( 'style' === $inteaili_settings_active_tab ? 'nav-tab-active' : '' ); ?>">
			<?php esc_html_e( 'Style', 'intelliagent-ai-lite' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'kb-sync', $inteaili_settings_page_url ) ); ?>" class="nav-tab <?php echo esc_attr( 'kb-sync' === $inteaili_settings_active_tab ? 'nav-tab-active' : '' ); ?>">
			<?php esc_html_e( 'Website & Store Sync', 'intelliagent-ai-lite' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'ai-persona', $inteaili_settings_page_url ) ); ?>" class="nav-tab <?php echo esc_attr( 'ai-persona' === $inteaili_settings_active_tab ? 'nav-tab-active' : '' ); ?>">
			<?php esc_html_e( 'AI Persona', 'intelliagent-ai-lite' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'ai-engine', $inteaili_settings_page_url ) ); ?>" class="nav-tab <?php echo esc_attr( 'ai-engine' === $inteaili_settings_active_tab ? 'nav-tab-active' : '' ); ?>">
			<?php esc_html_e( 'AI Engine', 'intelliagent-ai-lite' ); ?>
		</a>
	</nav>

	<div class="inteaili-settings-main-layout">
		
		<div class="settings-container inteaili-settings-flex-grow">
			<?php
			if ( 'content' === $inteaili_settings_active_tab ) :
				include INTEAILI_PATH . 'admin/partials/component/content-settings.php';
			elseif ( 'style' === $inteaili_settings_active_tab ) :
				include INTEAILI_PATH . 'admin/partials/component/style-settings.php';
			elseif ( 'kb-sync' === $inteaili_settings_active_tab ) :
				include INTEAILI_PATH . 'admin/partials/component/knowledge-sync-settings.php';
			elseif ( 'ai-persona' === $inteaili_settings_active_tab ) :
				include INTEAILI_PATH . 'admin/partials/component/ai-persona-settings.php';
			elseif ( 'ai-engine' === $inteaili_settings_active_tab ) :
				include INTEAILI_PATH . 'admin/partials/component/ai-engine-settings.php';
			endif;
			?>
		</div>

		<?php if ( 'content' === $inteaili_settings_active_tab || 'style' === $inteaili_settings_active_tab ) : ?>
			<div class="inteaili-settings-preview-sidebar">
				<div id="inteaili-admin-preview-container" class="inteaili-preview-container-box">
					<?php
					$inteaili_is_admin_preview = true;
					include INTEAILI_PATH . 'public/partials/chat-template.php';
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>