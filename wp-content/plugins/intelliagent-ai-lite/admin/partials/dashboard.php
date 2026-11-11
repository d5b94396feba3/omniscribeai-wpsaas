<?php
/**
 * Admin Dashboard Template
 *
 * This file is responsible for displaying the main admin interface,
 * including chat statistics, recent logs, and sidebar widgets.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* --- Data Preparation --- */
$inteaili_logo_url    = esc_url( INTEAILI_URL . 'assets/images/LogoAndName.svg' );
$inteaili_chat_logs   = $this->get_all_chat_logs();
$inteaili_stats       = $this->get_chat_stats();
$inteaili_total_chats = isset( $inteaili_stats['total'] ) ? $inteaili_stats['total'] : 0;


// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$inteaili_view_all     = isset( $_GET['view'] ) && 'all' === $_GET['view'];
$inteaili_display_logs = $inteaili_view_all ? $inteaili_chat_logs : array_slice( $inteaili_chat_logs, 0, 10 );
$inteaili_cron_enabled = ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );
$inteaili_next_cron    = wp_next_scheduled( 'inteaili_daily_sync_event' );
$inteaili_last_sync    = get_option( 'inteaili_last_cron_sync' );
?>
<div class="wrap">
	<?php
	/**
	 * Dashboard Header Arguments
	 *
	 * @var array $inteaili_header_args Configuration for the admin-header component.
	 */
	$inteaili_header_args = array(
		'title'    => esc_html__( 'Dashboard', 'intelliagent-ai-lite' ),
		'show_btn' => false,
	);
	require INTEAILI_PATH . 'admin/partials/component/header/admin-header.php';
	?>

	<div class="inteaili-dash-top-layout">
		<div class="inteaili-dash-main-content">
			<div class="inteaili-dash-stats">
				<div class="inteaili-stat-card">
					<div class="inteaili-stat-icon icon-blue"><i class="dashicons dashicons-format-chat"></i></div>
					<div class="inteaili-stat-content">
						<h3><?php echo esc_html( $inteaili_total_chats ); ?></h3>
						<p><?php esc_html_e( 'Total Conversations', 'intelliagent-ai-lite' ); ?></p>
					</div>
				</div>
				<div class="inteaili-stat-card">
					<div class="inteaili-stat-icon icon-green"><i class="dashicons dashicons-clock"></i></div>
					<div class="inteaili-stat-content">
						<h3><?php echo esc_html( $inteaili_stats['recent'] ); ?></h3>
						<p><?php esc_html_e( 'Active (Last 7 Days)', 'intelliagent-ai-lite' ); ?></p>
					</div>
				</div>
				<div class="inteaili-stat-card">
					<div class="inteaili-stat-icon icon-orange"><i class="dashicons dashicons-performance"></i></div>
					<div class="inteaili-stat-content">
						<h3><?php echo esc_html( get_option( 'inteaili_llm_model', 'Gemini' ) ); ?></h3>
						<p><?php esc_html_e( 'AI Engine', 'intelliagent-ai-lite' ); ?></p>
					</div>
				</div>
			</div>

			<div class="inteaili-dash-table-container">
				<div class="inteaili-table-header">
					<h2><?php esc_html_e( 'Recent Conversations', 'intelliagent-ai-lite' ); ?></h2>
					<div class="inteaili-table-actions">
						<button class="button button-secondary action-export-chat inteaili-flex-btn" data-id="0">
							<i class="dashicons dashicons-download"></i> <?php esc_html_e( 'Export All to CSV', 'intelliagent-ai-lite' ); ?>
						</button>
						<?php if ( count( $inteaili_chat_logs ) > 10 && ! $inteaili_view_all ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'view', 'all' ) ); ?>" class="button inteaili-flex-btn"><?php esc_html_e( 'View All History', 'intelliagent-ai-lite' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th class="col-session"><?php esc_html_e( 'Session ID', 'intelliagent-ai-lite' ); ?></th>
							<th class="col-preview"><?php esc_html_e( 'Last Message Preview', 'intelliagent-ai-lite' ); ?></th>
							<th><?php esc_html_e( 'Last Active', 'intelliagent-ai-lite' ); ?></th>
							<th class="col-actions"><?php esc_html_e( 'Actions', 'intelliagent-ai-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $inteaili_display_logs ) ) : ?>
							<tr><td colspan="4"><?php esc_html_e( 'No conversations recorded yet.', 'intelliagent-ai-lite' ); ?></td></tr>
						<?php else : ?>
							<?php
							foreach ( $inteaili_display_logs as $inteaili_log ) :
								$inteaili_history   = json_decode( $inteaili_log['full_history'], true );
								$inteaili_first_msg = is_array( $inteaili_history ) ? $inteaili_history[0]['content'] : esc_html__( 'No history', 'intelliagent-ai-lite' );
								$inteaili_last_msg  = is_array( $inteaili_history ) ? end( $inteaili_history ) : array( 'content' => '...' );
								?>
								<tr>
									<td>
										<strong>
											<?php
											/* translators: %d = chat session ID */
											printf( esc_html__( 'Chat #%d', 'intelliagent-ai-lite' ), (int) $inteaili_log['id'] );
											?>
										</strong>
									</td>
									<td>
										<div class="inteaili-msg-start">
											<strong><?php esc_html_e( 'Start:', 'intelliagent-ai-lite' ); ?></strong> <?php echo esc_html( wp_trim_words( $inteaili_first_msg, 8 ) ); ?>
										</div>
										<em class="inteaili-msg-last">
											<strong><?php esc_html_e( 'Last:', 'intelliagent-ai-lite' ); ?></strong> <?php echo esc_html( wp_trim_words( $inteaili_last_msg['content'], 10 ) ); ?>
										</em>
									</td>
									<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' - g:i A', strtotime( $inteaili_log['last_updated'] ) ) ); ?></td>
									<td class="col-actions">
										<div class="inteaili-action-buttons">
											<button class="button view-chat-log" data-history='<?php echo esc_attr( $inteaili_log['full_history'] ); ?>' title="<?php esc_attr_e( 'View', 'intelliagent-ai-lite' ); ?>">
												<i class="dashicons dashicons-visibility"></i>
											</button>
											<button class="button action-export-chat" data-id="<?php echo esc_attr( $inteaili_log['id'] ); ?>" title="<?php esc_attr_e( 'Export CSV', 'intelliagent-ai-lite' ); ?>">
												<i class="dashicons dashicons-download"></i>
											</button>
											<button class="button action-delete-chat inteaili-btn-delete" data-id="<?php echo esc_attr( $inteaili_log['id'] ); ?>" title="<?php esc_attr_e( 'Delete', 'intelliagent-ai-lite' ); ?>">
												<i class="dashicons dashicons-trash"></i>
											</button>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="inteaili-dash-sidebar">
			<div class="postbox inteaili-dash-widget">
				<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( 'Knowledge Sync Status', 'intelliagent-ai-lite' ); ?></h2></div>
				<div class="inside">
					<ul class="inteaili-status-list" style="margin-bottom:15px;">
						<li>
							<span><?php esc_html_e( 'Sync Engine:', 'intelliagent-ai-lite' ); ?></span>
							<span class="status-tag tag-green"><span class="inteaili-pulse"></span><?php esc_html_e( 'Active', 'intelliagent-ai-lite' ); ?></span>
						</li>
						<li>
							<span><?php esc_html_e( 'Last Sync:', 'intelliagent-ai-lite' ); ?></span>
							<span class="status-value">
								<?php
								echo $inteaili_last_sync
									? esc_html( sprintf( /* translators: %s = time ago */ __( '%s ago', 'intelliagent-ai-lite' ), human_time_diff( $inteaili_last_sync, current_time( 'timestamp' ) ) ) )
									: esc_html__( 'Never', 'intelliagent-ai-lite' );
								?>
							</span>
						</li>
					</ul>
					<div class="inteaili-quick-btns">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=inteaili-settings&tab=kb-sync' ) ); ?>" class="button button-primary inteaili-full-width inteaili-flex-btn">
							<i class="dashicons dashicons-update"></i> <?php esc_html_e( 'Sync Knowledge Base', 'intelliagent-ai-lite' ); ?>
						</a>
					</div>
				</div>
			</div>

			<div class="postbox inteaili-dash-widget">
				<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( 'Manual Training', 'intelliagent-ai-lite' ); ?></h2></div>
				<div class="inside">
					<div class="inteaili-quick-btns">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=inteaili-feedback' ) ); ?>" class="button button-secondary inteaili-full-width inteaili-flex-btn">
							<i class="dashicons dashicons-plus"></i> <?php esc_html_e( 'Add AI Feedback', 'intelliagent-ai-lite' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=inteaili-faq' ) ); ?>" class="button button-secondary inteaili-full-width inteaili-flex-btn">
							<i class="dashicons dashicons-plus"></i> <?php esc_html_e( 'Manage FAQ Data', 'intelliagent-ai-lite' ); ?>
						</a>
					</div>
				</div>
			</div>

			<div class="postbox inteaili-dash-widget">
				<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( 'AI Identity', 'intelliagent-ai-lite' ); ?></h2></div>
				<div class="inside">
					<p class="description"><?php esc_html_e( 'Define how your AI responds to customers.', 'intelliagent-ai-lite' ); ?></p>
					<div class="inteaili-quick-btns">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=inteaili-settings&tab=ai-persona' ) ); ?>" class="button button-secondary inteaili-full-width inteaili-flex-btn">
							<i class="dashicons dashicons-admin-settings"></i> <?php esc_html_e( 'Edit AI Persona', 'intelliagent-ai-lite' ); ?>
						</a>
					</div>
				</div>
			</div>

			<div class="postbox inteaili-dash-widget">
				<div class="postbox-header"><h2 class="hndle"><?php esc_html_e( 'System Health', 'intelliagent-ai-lite' ); ?></h2></div>
				<div class="inside">
					<ul class="inteaili-status-list">
						<li>
							<span><?php esc_html_e( 'WP Cron:', 'intelliagent-ai-lite' ); ?></span>
							<span class="status-tag <?php echo $inteaili_cron_enabled ? 'tag-green' : 'tag-red'; ?>">
								<?php echo $inteaili_cron_enabled ? esc_html__( 'Enabled', 'intelliagent-ai-lite' ) : esc_html__( 'Disabled', 'intelliagent-ai-lite' ); ?>
							</span>
						</li>
						<li>
							<span><?php esc_html_e( 'Next Task:', 'intelliagent-ai-lite' ); ?></span>
							<span class="status-value">
								<?php echo $inteaili_next_cron ? esc_html( wp_date( get_option( 'time_format' ), $inteaili_next_cron ) ) : esc_html__( 'Not Scheduled', 'intelliagent-ai-lite' ); ?>
							</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="chat-modal" class="inteaili-modal-overlay">
	<div class="inteaili-modal-box">
		<h3 id="chat-modal-title" class="inteaili-modal-title"><?php esc_html_e( 'Conversation Transcript', 'intelliagent-ai-lite' ); ?></h3>
		<div id="chat-modal-loader" class="inteaili-modal-loader">
			<div class="inteaili-loader-circle"></div>
			<p><?php esc_html_e( 'Fetching conversation...', 'intelliagent-ai-lite' ); ?></p>
		</div>
		<div id="chat-modal-content" class="inteaili-modal-content"></div>
		<div class="inteaili-modal-footer">
			<button onclick="jQuery('#chat-modal').hide()" class="button button-primary"><?php esc_html_e( 'Close Window', 'intelliagent-ai-lite' ); ?></button>
		</div>
	</div>
</div>