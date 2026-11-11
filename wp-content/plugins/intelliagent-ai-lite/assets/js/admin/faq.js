/**
 * FAQ JavaScript for IntelliAgent AI Lite
 *
 * Handles the CRUD operations for FAQs within the admin interface,
 * including modal toggling and AJAX submissions.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize FAQ Admin Functionality
     */
    $(function() {
        const $modal = $('#faq-modal');
        const $faqForm = $('#faq-form');
        const $faqId = $('#faq-id');
        const $faqTitle = $('#faq-title');
        const $faqQuestion = $('#faq-question');
        const $faqAnswer = $('#faq-answer');
        const $modalTitle = $('#faq-modal-title');

        /**
         * Open Modal for New FAQ
         */
        $('#add-new-faq').on('click', function() {
            $faqForm[0].reset();
            $faqId.val(0);
            $modalTitle.text(inteaili_admin_data.i18n_add_faq || 'Add New FAQ');
            $modal.fadeIn(200);
        });

        /**
         * Edit Existing FAQ - AJAX Retrieval
         */
        $(document).on('click', '.edit-faq', function() {
            const id = $(this).data('id');
            
            $.post(inteaili_admin_data.ajax_url, { 
                action: 'inteaili_get_faq', 
                id: id, 
                nonce: inteaili_admin_data.nonce 
            }, function(res) {
                if (res.success) {
                    $faqId.val(res.data.id);
                    $faqTitle.val(res.data.title);
                    $faqQuestion.val(res.data.question);
                    $faqAnswer.val(res.data.answer);
                    $modalTitle.text(inteaili_admin_data.i18n_edit_faq || 'Edit FAQ');
                    $modal.fadeIn(200);
                } else {
                    alert(res.data || 'Error fetching FAQ data.');
                }
            });
        });

        /**
         * Save FAQ (Create/Update)
         */
        $faqForm.on('submit', function(e) {
            e.preventDefault();
            
            const data = {
                action: 'inteaili_save_faq',
                nonce: inteaili_admin_data.nonce,
                id: $faqId.val(),
                title: $faqTitle.val(),
                question: $faqQuestion.val(),
                answer: $faqAnswer.val()
            };

            $.post(inteaili_admin_data.ajax_url, data, function() {
                location.reload();
            }).fail(function() {
                alert(inteaili_admin_data.i18n_server_error || 'Server error. Please try again.');
            });
        });

        /**
         * Delete FAQ with Confirmation
         */
        $(document).on('click', '.delete-faq', function() {
            const $btn = $(this);
            const id = $btn.data('id');

            if (!confirm(inteaili_admin_data.i18n_confirm_delete_faq || 'Are you sure you want to delete this FAQ?')) {
                return;
            }

            $.post(inteaili_admin_data.ajax_url, { 
                action: 'inteaili_delete_faq', 
                id: id, 
                nonce: inteaili_admin_data.nonce 
            }, function() {
                location.reload();
            }).fail(function() {
                alert(inteaili_admin_data.i18n_server_error || 'Server error.');
            });
        });
        
        /**
         * Close Modal on Click Outside or Close Button
         * (Added for better UX/Standard compliance)
         */
        $(document).on('click', '.faq-modal-close', function() {
            $modal.fadeOut(200);
        });
    });

})(jQuery);