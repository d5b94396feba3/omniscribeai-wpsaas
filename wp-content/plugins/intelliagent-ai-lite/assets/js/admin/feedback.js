/**
 * Feedback JavaScript for IntelliAgent AI Lite
 *
 * Manages the feedback CRUD operations within the admin dashboard,
 * including modal management and AJAX data handling.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Feedback Admin Actions
     */
    $(function() {
        // Cache DOM elements for efficiency
        const $modal = $('#feedback-modal');
        const $feedbackForm = $('#feedback-form');
        const $feedbackId = $('#feedback-id');
        const $modalTitle = $('#modal-title');
        const $fQuery = $('#f-query');
        const $fResponse = $('#f-response');
        const $fRating = $('#f-rating');

        /**
         * Trigger Modal for New Feedback Entry
         */
        $(document).on('click', '#add-new-feedback', function() {
            $feedbackForm[0].reset();
            $feedbackId.val(0);
            $modalTitle.text(inteaili_admin_data.i18n_add_feedback || 'Add New Feedback');
            $modal.show();
        });

        /**
         * Fetch Feedback Data for Editing
         */
        $(document).on('click', '.edit-feedback', function() {
            const id = $(this).data('id');
            
            $.post(inteaili_admin_data.ajax_url, {
                action: 'inteaili_get_feedback', 
                id: id, 
                nonce: inteaili_admin_data.nonce 
            }, function(res) {
                if (res.success) {
                    $feedbackId.val(res.data.id);
                    $fQuery.val(res.data.user_query);
                    $fResponse.val(res.data.ai_response);
                    $fRating.val(res.data.rating);
                    $modalTitle.text(inteaili_admin_data.i18n_edit_feedback || 'Edit Feedback');
                    $modal.show();
                } else {
                    alert(res.data || 'Failed to retrieve feedback.');
                }
            });
        });

        /**
         * Handle Feedback Form Submission
         */
        $feedbackForm.on('submit', function(e) {
            e.preventDefault();
            
            const data = {
                action: 'inteaili_save_feedback',
                nonce: inteaili_admin_data.nonce,
                id: $feedbackId.val(),
                user_query: $fQuery.val(),
                ai_response: $fResponse.val(),
                rating: $fRating.val()
            };

            $.post(inteaili_admin_data.ajax_url, data, function() {
                location.reload();
            }).fail(function() {
                alert(inteaili_admin_data.i18n_server_error || 'Server error occurred.');
            });
        });

        /**
         * Handle Deletion of Feedback
         */
        $(document).on('click', '.delete-feedback', function() {
            const id = $(this).data('id');

            if (!confirm(inteaili_admin_data.i18n_confirm_delete_feedback || 'Delete this feedback?')) {
                return;
            }

            $.post(inteaili_admin_data.ajax_url, { 
                action: 'inteaili_delete_feedback', 
                id: id, 
                nonce: inteaili_admin_data.nonce 
            }, function() {
                location.reload();
            }).fail(function() {
                alert(inteaili_admin_data.i18n_server_error || 'Error deleting feedback.');
            });
        });
        
        /**
         * Close Modal Functionality
         */
        $(document).on('click', '.feedback-modal-close', function() {
            $modal.hide();
        });
    });

})(jQuery);