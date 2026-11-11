<?php
/**
 * FAQ Management Template
 *
 * This template handles the administration interface for creating,
 * editing, and deleting verified FAQ entries used by the AI.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Admin/Partials
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* --- Data Preparation with Prefixes --- */
$inteaili_faq_data        = $this->get_faq_data();
$inteaili_faq_logs        = $inteaili_faq_data['logs'];
$inteaili_faq_total_count = $inteaili_faq_data['total'];


// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$inteaili_faq_view_all     = isset( $_GET['view'] ) && 'all' === $_GET['view'];
$inteaili_faq_display_list = $inteaili_faq_view_all ? $inteaili_faq_logs : array_slice( $inteaili_faq_logs, 0, 10 );
?>

<div class="wrap inteaili-ad-wrap">
<?php
/**
 * FAQ Header Arguments
 *
 * @var array $inteaili_header_args Configuration for the admin-header component.
 */
$inteaili_header_args = array(
	'title'    => esc_html__( 'FAQ Management', 'intelliagent-ai-lite' ),
	'show_btn' => true,
	'btn_id'   => 'add-new-faq',
	'btn_text' => esc_html__( 'Add New FAQ', 'intelliagent-ai-lite' ),
);
require INTEAILI_PATH . 'admin/partials/component/header/admin-header.php';
?>

	<div class="inteaili-faq-stats">
		<div class="inteaili-faq-stat-card">
			<div class="inteaili-faq-stat-icon"><i class="dashicons dashicons-list-view"></i></div>
			<div class="inteaili-faq-s-c">
				<h3><?php echo (int) $inteaili_faq_total_count; ?></h3>
				<p><?php esc_html_e( 'Total FAQs', 'intelliagent-ai-lite' ); ?></p>
			</div>
		</div>
	</div>

	<div class="admins-table" style="margin-top: 30px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
			<h2 style="margin: 0;"><?php esc_html_e( 'Verified FAQ Database', 'intelliagent-ai-lite' ); ?></h2>
			<?php if ( count( $inteaili_faq_logs ) > 10 && ! $inteaili_faq_view_all ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'view', 'all' ) ); ?>" class="button"><?php esc_html_e( 'View All FAQs', 'intelliagent-ai-lite' ); ?></a>
			<?php endif; ?>
		</div>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 25%;"><?php esc_html_e( 'Title', 'intelliagent-ai-lite' ); ?></th>
					<th style="width: 30%;"><?php esc_html_e( 'Question', 'intelliagent-ai-lite' ); ?></th>
					<th style="width: 35%;"><?php esc_html_e( 'Answer', 'intelliagent-ai-lite' ); ?></th>
					<th style="text-align: right;"><?php esc_html_e( 'Actions', 'intelliagent-ai-lite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $inteaili_faq_display_list ) ) : ?>
					<tr><td colspan="4"><?php esc_html_e( 'No FAQs found.', 'intelliagent-ai-lite' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $inteaili_faq_display_list as $inteaili_faq_item ) : ?>
					<tr>
						<td><strong><?php echo esc_html( wp_unslash( $inteaili_faq_item['title'] ) ); ?></strong></td>
						<td><?php echo esc_html( wp_trim_words( wp_unslash( $inteaili_faq_item['question'] ), 10 ) ); ?></td>
						<td><?php echo esc_html( wp_trim_words( wp_unslash( $inteaili_faq_item['answer'] ), 15 ) ); ?></td>

						<td>
							<div class="inteaili-faq-as">
								<button type="button" class="button edit-faq" data-id="<?php echo esc_attr( $inteaili_faq_item['id'] ); ?>" title="<?php esc_attr_e( 'Edit FAQ', 'intelliagent-ai-lite' ); ?>">
									<i class="dashicons dashicons-edit"></i>
								</button>
								<button type="button" class="button delete-faq inteaili-btn-d-faq" data-id="<?php echo (int) $inteaili_faq_item['id']; ?>" title="<?php esc_attr_e( 'Delete FAQ', 'intelliagent-ai-lite' ); ?>">
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

<div id="faq-modal" class="inteaili-faq-m-o">
	<div class="inteaili-faq-m-c">
		<h3 id="faq-modal-title" class="inteaili-faq-m-h"><?php esc_html_e( 'Manage FAQ Entry', 'intelliagent-ai-lite' ); ?></h3>
		<form id="faq-form">
			<input type="hidden" id="faq-id" name="id" value="0">
			
			<div class="inteaili-faq-f-g">
				<label for="faq-title"><?php esc_html_e( 'Internal Title', 'intelliagent-ai-lite' ); ?></label>
				<input type="text" id="faq-title" required placeholder="<?php esc_attr_e( 'e.g. Refund Policy Info', 'intelliagent-ai-lite' ); ?>">
			</div>

			<div class="inteaili-faq-f-g">
				<label for="faq-question"><?php esc_html_e( 'Question', 'intelliagent-ai-lite' ); ?></label>
				<textarea id="faq-question" rows="2" required placeholder="<?php esc_attr_e( 'e.g. Do you offer refunds if I am not satisfied?', 'intelliagent-ai-lite' ); ?>"></textarea>
			</div>

			<div class="inteaili-faq-f-g">
				<label for="faq-answer"><?php esc_html_e( 'Answer', 'intelliagent-ai-lite' ); ?></label>
				<textarea id="faq-answer" rows="4" required placeholder="<?php esc_attr_e( 'e.g. Yes, we offer a 14-day money-back guarantee for all our premium plans.', 'intelliagent-ai-lite' ); ?>"></textarea>
			</div>

			<div class="inteaili-faq-m-f">
				<button type="button" class="button" onclick="jQuery('#faq-modal').fadeOut(200)"><?php esc_html_e( 'Cancel', 'intelliagent-ai-lite' ); ?></button>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Save FAQ', 'intelliagent-ai-lite' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>