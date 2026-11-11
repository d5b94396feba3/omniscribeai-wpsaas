<?php
/**
 * Status Dashboard Template for AICA IntelliAgent.
 *
 * This file is responsible for rendering the diagnostic report interface,
 * displaying environment checks, AI knowledge status, and server metrics.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* --- Data Preparation --- */
$inteaili_report_data  = $report;
$inteaili_any_failures = false;
$inteaili_kb_failures  = false;
?>
<div class="wrap inteaili-ad-wrap">
	<?php
	/**
	 * Status Header Arguments
	 *
	 * @var array $inteaili_header_args Configuration for the admin-header component.
	 */
	$inteaili_header_args = array(
		'title'    => esc_html__( 'Status', 'intelliagent-ai-lite' ),
		'show_btn' => false,
	);
	require INTEAILI_PATH . 'admin/partials/component/header/admin-header.php';
	?>

	<div class="inteaili-status-actions">
		<button type="button" class="button button-secondary inteaili-copy-report-btn" id="inteaili-copy-diagnostics">
			<span class="dashicons dashicons-clipboard"></span>
			<?php esc_html_e( 'Copy System Report', 'intelliagent-ai-lite' ); ?>
		</button>
	</div>

	<?php /** Section 1: AI Knowledge & Training */ ?>
	<?php if ( isset( $inteaili_report_data['knowledge_status'] ) ) : ?>
		<div class="inteaili-status-header inteaili-status-kb">
			<h2><?php echo esc_html( $inteaili_report_data['knowledge_status']['label'] ); ?></h2>
			<p class="description"><?php esc_html_e( 'Status of synchronized content and training data.', 'intelliagent-ai-lite' ); ?></p>
		</div>

		<table class="inteaili-status-table">
			<thead>
				<tr>
					<th style="width: 50%;"><?php esc_html_e( 'Metric', 'intelliagent-ai-lite' ); ?></th>
					<th style="width: 50%;"><?php esc_html_e( 'Status', 'intelliagent-ai-lite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $inteaili_report_data['knowledge_status']['items'] as $inteaili_k_item ) :
					$inteaili_val     = $inteaili_k_item['value'];
					$inteaili_is_fail = ( stripos( $inteaili_val, 'Not' ) !== false || stripos( $inteaili_val, 'Missing' ) !== false );
					if ( $inteaili_is_fail ) {
						$inteaili_kb_failures = true;
					}
					$inteaili_status_class = $inteaili_is_fail ? 'text-failed' : 'text-pass';
					?>
					<tr>
						<td><strong><?php echo esc_html( $inteaili_k_item['label'] ); ?></strong></td>
						<td><span class="<?php echo esc_attr( $inteaili_status_class ); ?>"><?php echo esc_html( $inteaili_val ); ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $inteaili_kb_failures ) : ?>
		<div class="inteaili-requirement-note note-optional">
			<span class="dashicons dashicons-info" aria-hidden="true"></span>
			<p><strong><?php esc_html_e( 'Recommended Action:', 'intelliagent-ai-lite' ); ?></strong> <?php esc_html_e( 'The knowledge base is partially configured. Synchronize website content or upload training documents to ensure the AI provides accurate responses.', 'intelliagent-ai-lite' ); ?></p>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php /** Section 2: Environment & System Details */ ?>
	<div class="inteaili-status-header">
		<h2><?php esc_html_e( 'Environment & System', 'intelliagent-ai-lite' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Server requirements and plugin configuration for optimal performance.', 'intelliagent-ai-lite' ); ?></p>
	</div>

	<table class="inteaili-status-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Requirement / Metric', 'intelliagent-ai-lite' ); ?></th>
				<th><?php esc_html_e( 'Status', 'intelliagent-ai-lite' ); ?></th>
				<th><?php esc_html_e( 'Details / Value', 'intelliagent-ai-lite' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			/** 1. System Info Items */
			if ( isset( $inteaili_report_data['info']['items'] ) ) :
				foreach ( $inteaili_report_data['info']['items'] as $inteaili_info ) :
					$inteaili_val     = $inteaili_info['value'];
					$inteaili_is_fail = ( stripos( $inteaili_val, 'Missing' ) !== false || stripos( $inteaili_val, 'Not' ) !== false || stripos( $inteaili_val, 'Key Missing' ) !== false );
					if ( $inteaili_is_fail ) {
						$inteaili_any_failures = true;
					}

					$inteaili_status_label = $inteaili_is_fail ? esc_html__( 'Action Required', 'intelliagent-ai-lite' ) : esc_html__( 'Pass', 'intelliagent-ai-lite' );
					$inteaili_status_class = $inteaili_is_fail ? 'failed' : 'pass';
					?>
					<tr>
						<td><strong><?php echo esc_html( $inteaili_info['label'] ); ?></strong></td>
						<td>
							<span class="inteaili-status-dot status-<?php echo esc_attr( $inteaili_status_class ); ?>"></span>
							<span class="text-<?php echo esc_attr( $inteaili_status_class ); ?>"><?php echo esc_html( $inteaili_status_label ); ?></span>
						</td>
						<td><?php echo esc_html( $inteaili_val ); ?></td>
					</tr>
					<?php
				endforeach;
			endif;
			?>

			<?php
			/** 2. WooCommerce Status */
			$inteaili_woo_active = class_exists( 'WooCommerce' );
			if ( ! $inteaili_woo_active ) {
				$inteaili_any_failures = true;
			}
			?>
			<tr>
				<td><strong><?php esc_html_e( 'WooCommerce', 'intelliagent-ai-lite' ); ?></strong></td>
				<td>
					<span class="inteaili-status-dot status-<?php echo $inteaili_woo_active ? 'pass' : 'failed'; ?>"></span>
					<span class="text-<?php echo $inteaili_woo_active ? 'pass' : 'failed'; ?>">
						<?php echo $inteaili_woo_active ? esc_html__( 'Pass', 'intelliagent-ai-lite' ) : esc_html__( 'Action Required', 'intelliagent-ai-lite' ); ?>
					</span>
				</td>
				<td><?php echo $inteaili_woo_active ? esc_html__( 'Active', 'intelliagent-ai-lite' ) : esc_html__( 'Not detected', 'intelliagent-ai-lite' ); ?></td>
			</tr>

			<?php
			/** 2.1 WooCommerce Version */
			if ( isset( $inteaili_report_data['environment']['items']['woo_version'] ) ) :
				$inteaili_woo_ver_item = $inteaili_report_data['environment']['items']['woo_version'];
				unset( $inteaili_report_data['environment']['items']['woo_version'] );
				$inteaili_is_pass = ( 'pass' === $inteaili_woo_ver_item['status'] );
				if ( ! $inteaili_is_pass ) {
					$inteaili_any_failures = true;
				}
				?>
				<tr>
					<td><strong><?php echo esc_html( $inteaili_woo_ver_item['label'] ); ?></strong></td>
					<td>
						<span class="inteaili-status-dot status-<?php echo esc_attr( $inteaili_woo_ver_item['status'] ); ?>"></span>
						<span class="text-<?php echo $inteaili_is_pass ? 'pass' : 'failed'; ?>">
							<?php echo $inteaili_is_pass ? esc_html__( 'Pass', 'intelliagent-ai-lite' ) : esc_html__( 'Action Required', 'intelliagent-ai-lite' ); ?>
						</span>
					</td>
					<td><?php echo esc_html( $inteaili_woo_ver_item['details'] ); ?></td>
				</tr>
			<?php endif; ?>

			<?php
			/** 3. Environment Items (Remaining items like WP Version, PHP, etc.) */
			foreach ( $inteaili_report_data['environment']['items'] as $inteaili_key => $inteaili_item ) :
				$inteaili_is_pass = ( 'pass' === $inteaili_item['status'] );
				if ( ! $inteaili_is_pass ) {
					$inteaili_any_failures = true;
				}
				?>
				<tr>
					<td><strong><?php echo esc_html( $inteaili_item['label'] ); ?></strong></td>
					<td>
						<span class="inteaili-status-dot status-<?php echo esc_attr( $inteaili_item['status'] ); ?>"></span>
						<span class="text-<?php echo $inteaili_is_pass ? 'pass' : 'failed'; ?>">
							<?php echo $inteaili_is_pass ? esc_html__( 'Pass', 'intelliagent-ai-lite' ) : esc_html__( 'Action Required', 'intelliagent-ai-lite' ); ?>
						</span>
					</td>
					<td><?php echo esc_html( $inteaili_item['details'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $inteaili_any_failures ) : ?>
	<div class="inteaili-requirement-note">
		<span class="dashicons dashicons-warning" aria-hidden="true"></span>
		<p><strong><?php esc_html_e( 'Configuration Alert:', 'intelliagent-ai-lite' ); ?></strong> <?php esc_html_e( 'One or more critical system requirements have not been met. Review the highlighted status items above to ensure full plugin compatibility and performance.', 'intelliagent-ai-lite' ); ?></p>
	</div>
	<?php endif; ?>
</div>