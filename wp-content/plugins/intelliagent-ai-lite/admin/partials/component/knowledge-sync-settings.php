<?php
/**
 * Website Knowledge Sync Template
 *
 * This template manages the crawling and synchronization of website pages
 * into the AI's knowledge base. It includes a real-time progress bar and
 * status indicators for the automated sync engine.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials/Components
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="postbox inteaili-box knowledge-sync-wrapper">
	<div class="postbox-header inteaili-toggle-sync" id="toggle-sync-table">
		<h2 class="hndle"><?php esc_html_e( 'Knowledge Base & Store Sync', 'intelliagent-ai-lite' ); ?></h2>
		<div class="inteaili-header-badges">
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<span class="status-badge inteaili-woo-active-badge">
					<span class="dashicons dashicons-cart"></span>
					<?php esc_html_e( 'WooCommerce Detected', 'intelliagent-ai-lite' ); ?>
				</span>
			<?php else : ?>
				<span class="status-badge inteaili-woo-inactive-badge">
					<span class="dashicons dashicons-cart"></span>
					<?php esc_html_e( 'WooCommerce Not Detected', 'intelliagent-ai-lite' ); ?>
				</span>
			<?php endif; ?>

			<span id="sync-summary-badge" class="status-badge inteaili-summary-badge">
				<?php esc_html_e( 'Discovering...', 'intelliagent-ai-lite' ); ?>
			</span>
			<span class="dashicons dashicons-arrow-down-alt2 toggle-icon"></span>
		</div>
	</div>

	<div class="inside inteaili-sync-content" id="sync-collapsible-content">
		
		<div id="discovery-loader" class="inteaili-sync-loader">
			<div class="inteaili-loader-circle"></div>
			<p><?php esc_html_e( 'Scanning Pages & Product Catalog...', 'intelliagent-ai-lite' ); ?></p>
		</div>

		<div id="sync-progress-bar" class="inteaili-progress-container">
			<div class="inteaili-progress-info">
				<span id="sync-msg" class="inteaili-progress-msg"><?php esc_html_e( 'Analyzing Content...', 'intelliagent-ai-lite' ); ?></span>
				<span id="sync-percent" class="inteaili-progress-percent">0%</span>
			</div>
			<div class="admin-progress-bar">
				<div id="sync-fill" class="admin-progress-fill"></div>
			</div>
		</div>

		<div id="sync-success-msg" class="inteaili-sync-success">
			<div class="inteaili-success-inner">
				<span class="dashicons dashicons-yes-alt"></span>
				<span><?php esc_html_e( 'Scrape Successful! Knowledge base is now synchronized.', 'intelliagent-ai-lite' ); ?></span>
			</div>
		</div>

		<div id="sync-data-container" class="inteaili-data-container">
			<div class="sync-table-scroll">
				<table class="wp-list-table widefat fixed striped inteaili-sync-table">
					<thead>
						<tr>
							<th class="col-source"><?php esc_html_e( 'Source Name', 'intelliagent-ai-lite' ); ?></th>
							<th class="col-type"><?php esc_html_e( 'Type', 'intelliagent-ai-lite' ); ?></th>
							<th class="col-status"><?php esc_html_e( 'Sync Status', 'intelliagent-ai-lite' ); ?></th>
						</tr>
					</thead>
					<tbody id="sync-table-body"></tbody>
				</table>
			</div>
			
			<div class="sync-actions-footer">
				<button type="button" id="run-bulk-sync" class="button button-hero button-primary"></button>
				<button type="button" id="start-discovery-refresh" class="button button-secondary">
					<?php esc_html_e( 'Re-scan Website', 'intelliagent-ai-lite' ); ?>
				</button>
			</div>
		</div>
	</div>
	
	<div class="sync-status-card">
		<div class="inteaili-card-left">
			<div class="status-indicator">
				<span class="dashicons dashicons-update"></span>
				<span class="indicator-dot"></span>
			</div>
			<div class="inteaili-card-text">
				<h4><?php esc_html_e( 'Automated Sync Engine', 'intelliagent-ai-lite' ); ?></h4>
				<p>
					<?php
					if ( ! empty( $inteaili_settings_last_sync ) ) {
						printf(
							/* translators: %s: relative time ago */
							esc_html__( 'Last backup completed %s ago', 'intelliagent-ai-lite' ),
							esc_html( human_time_diff( $inteaili_settings_last_sync, current_time( 'timestamp' ) ) )
						);
					} else {
						esc_html_e( 'No background sync recorded yet', 'intelliagent-ai-lite' );
					}
					?>
				</p>
			</div>
		</div>

		<div class="inteaili-card-right">
			<div class="inteaili-next-label"><?php esc_html_e( 'Next Scheduled Run', 'intelliagent-ai-lite' ); ?></div>
			<div class="inteaili-next-date">
				<?php
				if ( ! empty( $inteaili_settings_next_sync ) ) {
					printf( '%s', esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $inteaili_settings_next_sync ) ) );
				} else {
					esc_html_e( 'Paused / Not Scheduled', 'intelliagent-ai-lite' );
				}
				?>
			</div>
		</div>
	</div>
</div>