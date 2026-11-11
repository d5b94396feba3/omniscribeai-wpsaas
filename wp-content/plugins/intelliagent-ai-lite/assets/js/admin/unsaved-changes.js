/**
 * Unsaved Changes (US) JavaScript for IntelliAgent AI Lite
 *
 * Tracks modifications across the settings panel and manages 
 * the visibility of the "Unsaved Changes" floating bar.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Unsaved Changes Detection
     */
    $(function() {
        const i18n = (typeof inteaili_admin_data !== 'undefined') ? inteaili_admin_data : {};
        const $unsavedBar = $('#inteaili-unsaved-bar');
        let hasChanges = false;

        /**
         * Trigger the floating unsaved notice and highlight submit buttons.
         */
        function showUnsavedNotice() {
            if (!hasChanges) {
                hasChanges = true;
                $unsavedBar.css('display', 'flex').hide().fadeIn(300);
                $('.submit .button-primary').css('box-shadow', '0 0 0 3px rgba(59, 130, 246, 0.4)');
            }
        }

        /**
         * Track changes on theme card selections.
         */
        $(document).on('click', '.inteaili-theme-card', function() {
            showUnsavedNotice();
            $(this).find('.theme-card-inner').addClass('field-changed');
        });

        /**
         * Monitor standard inputs, selects, and textareas.
         */
        $(document).on('input change', '.inteaili-input, select, input[type="color"], textarea[name="inteaili_system_prompt"]', function() {
            $(this).addClass('field-changed');
            showUnsavedNotice();
        });

        /**
         * MutationObserver to detect hidden input changes from Media Uploader.
         */
        const fileObserver = new MutationObserver(function(mutations) {
            $.each(mutations, function(i, mutation) {
                if (mutation.type === "attributes" && mutation.attributeName === "value") {
                    showUnsavedNotice();
                }
            });
        });

        const logoInput = document.getElementById('inteaili_logo_id');
        const kbFileInput = document.getElementById('inteaili_knowledge_file_id');

        if (logoInput) {
            fileObserver.observe(logoInput, { attributes: true });
        }
        if (kbFileInput) {
            fileObserver.observe(kbFileInput, { attributes: true });
        }

        /**
         * UI resets and deletions also trigger the notice.
         */
        $(document).on('click', '.button-link-delete, #reset_primary_color, #inteaili_remove_btn, #inteaili_file_remove_btn', function() {
            showUnsavedNotice();
        });

        /**
         * Handle AI Prompt Generation specific change detection.
         */
        $(document).on('click', '#btn-generate-ai-prompt', function() {
            setTimeout(function() {
                showUnsavedNotice();
                $('#inteaili_ai_persona_area').addClass('field-changed');
            }, 1000); 
        });

        /**
         * Handle the Sticky Save Bar trigger.
         */
        $(document).on('click', '#inteaili-sticky-save-trigger', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $visibleForm = $('form[action="options.php"]:visible');
            
            if ($visibleForm.length > 0) {
                const savingLabel = i18n.i18n_saving || 'Saving...';
                $btn.text(savingLabel).prop('disabled', true);
                
                const nativeForm = $visibleForm[0];
                
                // Use requestSubmit to trigger native validation/listeners, fallback to submit.
                if (typeof nativeForm.requestSubmit === 'function') {
                    nativeForm.requestSubmit();
                } else {
                    nativeForm.submit();
                }
            } else {
                const errorMsg = i18n.i18n_form_error || 'Error: Could not find the settings form.';
                console.error('AICA: No visible settings form found to submit.');
                alert(errorMsg);
            }
        });
    });

})(jQuery);