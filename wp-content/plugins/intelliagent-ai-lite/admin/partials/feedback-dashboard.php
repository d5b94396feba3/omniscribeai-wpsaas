<?php
/**
 * Feedback & Training Management Template
 *
 * This template displays the AI training dashboard where user feedback
 * is managed to improve the AI's response accuracy.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* --- Data Preparation with Prefixes --- */
$inteaili_fb_data  = $this->get_feedback_data();
$inteaili_fb_stats = $inteaili_fb_data['stats'];
$inteaili_fb_list  = $inteaili_fb_data['logs'];

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$inteaili_fb_view_all     = isset( $_GET['view'] ) && 'all' === $_GET['view'];
$inteaili_fb_display_logs = $inteaili_fb_view_all ? $inteaili_fb_list : array_slice( $inteaili_fb_list, 0, 10 );
?>

<div class="wrap inteaili-ad-wrap">
	<?php
	/**
	 * Feedback Header Arguments
	 *
	 * @var array $inteaili_header_args Configuration for the admin-header component.
	 */
	$inteaili_header_args = array(
		'title'    => esc_html__( 'Feedback & Training', 'intelliagent-ai-lite' ),
		'show_btn' => true,
		'btn_id'   => 'add-new-feedback',
		'btn_text' => esc_html__( 'Add New Entry', 'intelliagent-ai-lite' ),
	);
	require INTEAILI_PATH . 'admin/partials/component/header/admin-header.php';
	?>

	<div class="inteaili-feedback-stats">
		<div class="admin-stat-card">
			<div class="stat-icon stat-icon-total"><i class="dashicons dashicons-database"></i></div>
			<div class="stat-content">
				<h3><?php echo (int) $inteaili_fb_stats['total']; ?></h3>
				<p><?php esc_html_e( 'Total Entries', 'intelliagent-ai-lite' ); ?></p>
			</div>
		</div>
		<div class="admin-stat-card">
			<div class="stat-icon stat-icon-helpful"><i class="dashicons dashicons-yes"></i></div>
			<div class="stat-content">
				<h3><?php echo (int) $inteaili_fb_stats['helpful']; ?></h3>
				<p><?php esc_html_e( 'Helpful (Like)', 'intelliagent-ai-lite' ); ?></p>
			</div>
		</div>
		<div class="admin-stat-card">
			<div class="stat-icon stat-icon-unhelpful"><i class="dashicons dashicons-no"></i></div>
			<div class="stat-content">
				<h3><?php echo (int) $inteaili_fb_stats['unhelpful']; ?></h3>
				<p><?php esc_html_e( 'Unhelpful (Dislike)', 'intelliagent-ai-lite' ); ?></p>
			</div>
		</div>
	</div>

	<div style="display: flex; gap: 30px; align-items: flex-start; margin-top: 0px;">
		
		<div class="admins-table" style="flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
				<h2 style="margin: 0;"><?php esc_html_e( 'Recent Feedback Data', 'intelliagent-ai-lite' ); ?></h2>
				<div style="display: flex; gap: 10px;">
					<?php if ( count( $inteaili_fb_list ) > 10 && ! $inteaili_fb_view_all ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'view', 'all' ) ); ?>" class="button">
							<?php esc_html_e( 'View All Feedback', 'intelliagent-ai-lite' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 30%;"><?php esc_html_e( 'User Question', 'intelliagent-ai-lite' ); ?></th>
						<th style="width: 45%;"><?php esc_html_e( 'Ideal AI Response', 'intelliagent-ai-lite' ); ?></th>
						<th style="width: 10%;"><?php esc_html_e( 'Rating', 'intelliagent-ai-lite' ); ?></th>
						<th style="text-align: right;"><?php esc_html_e( 'Actions', 'intelliagent-ai-lite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $inteaili_fb_display_logs ) ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'No training data recorded yet.', 'intelliagent-ai-lite' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $inteaili_fb_display_logs as $inteaili_f ) : ?>
							<tr>
								<td><strong><?php echo esc_html( wp_trim_words( wp_unslash( $inteaili_f['user_query'] ), 10 ) ); ?></strong></td>
								<td><?php echo esc_html( wp_trim_words( wp_unslash( $inteaili_f['ai_response'] ), 15 ) ); ?></td>
								<td>
									<?php if ( 'like' === $inteaili_f['rating'] ) : ?>
										<span class="dashicons dashicons-thumbs-up color-success" title="<?php esc_attr_e( 'Helpful', 'intelliagent-ai-lite' ); ?>"></span>
									<?php else : ?>
										<span class="dashicons dashicons-thumbs-down color-danger" title="<?php esc_attr_e( 'Unhelpful', 'intelliagent-ai-lite' ); ?>"></span>
									<?php endif; ?>
								</td>
								<td>
									<div class="inteaili-fd-as">
										<button type="button" class="button edit-feedback" data-id="<?php echo (int) $inteaili_f['id']; ?>" title="<?php esc_attr_e( 'Edit Entry', 'intelliagent-ai-lite' ); ?>">
											<i class="dashicons dashicons-edit"></i>
										</button>
										<button type="button" class="button delete-feedback inteaili-btn-delete" data-id="<?php echo (int) $inteaili_f['id']; ?>" title="<?php esc_attr_e( 'Delete Entry', 'intelliagent-ai-lite' ); ?>">
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
</div>

<div id="feedback-modal" class="inteaili-fd-m-o">
	<div class="inteaili-fd-m-c">
		<h3 id="modal-title"><?php esc_html_e( 'Feedback Management', 'intelliagent-ai-lite' ); ?></h3>
		<form id="feedback-form">
			<input type="hidden" id="feedback-id" name="id" value="0">
			
			<div class="inteaili-form-group">
				<label for="f-query"><?php esc_html_e( 'User Question', 'intelliagent-ai-lite' ); ?></label>
				<textarea id="f-query" rows="3" required placeholder="<?php esc_attr_e( 'e.g., What are your business hours?', 'intelliagent-ai-lite' ); ?>"></textarea>
			</div>

			<div class="inteaili-form-group">
				<label for="f-response"><?php esc_html_e( 'AI Response', 'intelliagent-ai-lite' ); ?></label>
				<textarea id="f-response" rows="3" required placeholder="<?php esc_attr_e( 'e.g., We are open from 9 AM to 5 PM, Monday to Friday.', 'intelliagent-ai-lite' ); ?>"></textarea>
			</div>

			<div class="inteaili-form-group">
				<label for="f-rating"><?php esc_html_e( 'Rating', 'intelliagent-ai-lite' ); ?></label>
				<select id="f-rating">
					<option value="like"><?php esc_html_e( 'Helpful (Like)', 'intelliagent-ai-lite' ); ?></option>
					<option value="dislike"><?php esc_html_e( 'Not Helpful (Dislike)', 'intelliagent-ai-lite' ); ?></option>
				</select>
				<p class="description">
					<?php esc_html_e( 'Positive ratings are used as examples for the AI to follow.', 'intelliagent-ai-lite' ); ?>
				</p>
			</div>

			<div class="inteaili-modal-footer">
				<button type="button" class="button" onclick="jQuery('#feedback-modal').fadeOut(200)"><?php esc_html_e( 'Cancel', 'intelliagent-ai-lite' ); ?></button>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Save Feedback', 'intelliagent-ai-lite' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>