<?php
/**
 * Support & Features Dashboard Template
 *
 * This template provides the user with quick access to documentation,
 * technical support, feature requests, and direct contact information.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap inteaili-ad-wrap">
	<?php
	/**
	 * Support Header Arguments
	 *
	 * @var array $inteaili_header_args Configuration for the admin-header component.
	 */
	$inteaili_header_args = array(
		'title'    => esc_html__( 'Support & Features', 'intelliagent-ai-lite' ),
		'show_btn' => false,
	);
	require INTEAILI_PATH . 'admin/partials/component/header/admin-header.php';
	?>

	<div class="inteaili-support-grid">
		
		<div class="postbox inteaili-support-card">
			<div class="inside">
				<span class="dashicons dashicons-text-page icon-docs"></span>
				<h3><?php esc_html_e( 'Documentation', 'intelliagent-ai-lite' ); ?></h3>
				<p><?php esc_html_e( 'Find guides on setup, syncing knowledge, and training your AI agent.', 'intelliagent-ai-lite' ); ?></p>
				<a href="<?php echo esc_url( 'https://docs.aica-intelliagent.com/' ); ?>" target="_blank" class="button button-secondary">
					<?php esc_html_e( 'Read Documentation', 'intelliagent-ai-lite' ); ?>
				</a>
			</div>
		</div>

		<div class="postbox inteaili-support-card">
			<div class="inside">
				<span class="dashicons dashicons-sos icon-support"></span>
				<h3><?php esc_html_e( 'Technical Support', 'intelliagent-ai-lite' ); ?></h3>
				<p><?php esc_html_e( 'Facing technical issues? Submit a support request on our support form.', 'intelliagent-ai-lite' ); ?></p>
				<a href="<?php echo esc_url( 'https://aica-intelliagent.com/support/' ); ?>" target="_blank" class="button button-secondary">
					<?php esc_html_e( 'Get Help', 'intelliagent-ai-lite' ); ?>
				</a>
			</div>
		</div>

		<div class="postbox inteaili-support-card">
			<div class="inside">
				<span class="dashicons dashicons-lightbulb icon-features"></span>
				<h3><?php esc_html_e( 'Feature Requests', 'intelliagent-ai-lite' ); ?></h3>
				<p><?php esc_html_e( 'Suggest new capabilities or integrations you would like to see in IntelliAgent AI.', 'intelliagent-ai-lite' ); ?></p>
				<a href="<?php echo esc_url( 'https://aica-intelliagent.com/customization-request/' ); ?>" target="_blank" class="button button-secondary">
					<?php esc_html_e( 'Request Feature', 'intelliagent-ai-lite' ); ?>
				</a>
			</div>
		</div>

	</div>

	<div class="postbox inteaili-contact-banner">
		<div class="inside">
			<div class="banner-content">
				<h3><?php esc_html_e( 'Direct Email Support', 'intelliagent-ai-lite' ); ?></h3>
				<p>
					<?php esc_html_e( 'For partnership inquiries or urgent matters, email us directly at:', 'intelliagent-ai-lite' ); ?> 
					<a href="<?php echo esc_url( 'mailto:support@aica-intelliagent.com' ); ?>" class="inteaili-mailto-link">support@aica-intelliagent.com</a>
				</p>
			</div>
			<div class="banner-action">
				<a href="<?php echo esc_url( 'https://aica-intelliagent.com/contact/' ); ?>" target="_blank" class="button button-primary button-large">
					<?php esc_html_e( 'Contact Us Now', 'intelliagent-ai-lite' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>