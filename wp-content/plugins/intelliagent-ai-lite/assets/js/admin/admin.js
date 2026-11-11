/**
 * Admin JavaScript for IntelliAgent AI Lite
 *
 * Handles chat log interactions, AJAX deletions, exports, and UI toggles.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Admin Functionality
     */
    $(function() {
        // Cache frequent selectors
        const $chatModal = $('#chat-modal');
        const $chatModalContent = $('#chat-modal-content');
        const $chatModalLoader = $('#chat-modal-loader');
        const $chatModalTitle = $('#chat-modal-title');

        /**
         * Simple HTML Entity Encoder
         * @param {string} text
         * @return {string}
         */
        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        /**
         * View Chat History Log
         */
        $(document).on('click', '.view-chat-log', function() {
            const stripHtml = (html) => {
                const tmp = document.createElement("DIV");
                tmp.innerHTML = html;
                return tmp.textContent || tmp.innerText || "";
            };

            const history = $(this).data('history');
            let html = '';
            
            if (history) {
                const historyObj = (typeof history === 'string') ? JSON.parse(history) : history;
                
                $.each(historyObj, function(i, msg) {
                    const isUser = msg.role === 'user';
                    const label = isUser ? (inteaili_admin_data.i18n_visitor || 'Visitor') : (inteaili_admin_data.i18n_ai_agent || 'AI Agent');
                    const style = isUser ? 'background:#f0f7ff; border-left:4px solid #2271b1;' : 'background:#f0fdf4; border-left:4px solid #16a34a;';
                    const cleanContent = stripHtml(msg.content);

                    html += `<div style="margin-bottom:15px; padding:12px; border-radius:8px; ${style}">
                        <strong style="display:block; margin-bottom:5px;">${escapeHtml(label)}:</strong>
                        <div style="white-space: pre-wrap;">${escapeHtml(cleanContent)}</div>
                    </div>`;
                });
            }
            
            $chatModalContent.html(html);
            $chatModal.fadeIn(200);
        });


        /**
         * Delete Chat Log via AJAX
         */
        $(document).on('click', '.action-delete-chat', function(e) {
            e.preventDefault();
            
            if (!confirm(inteaili_admin_data.i18n_confirm_delete || 'Delete this conversation forever?')) {
                return;
            }
            
            const $btn = $(this);
            const $icon = $btn.find('.dashicons');
            const logId = $btn.data('id');
            
            // Force the icon to disappear
            $icon.css({
                'visibility': 'hidden',
                'width': '0',
                'margin': '0'
            });

            // Disable button and add the WP Spinner
            $btn.prop('disabled', true);
            
            // Remove any old spinners, then append fresh
            $btn.find('.spinner').remove();
            const $spinner = $('<span class="spinner is-active"></span>');
            $btn.append($spinner);

            // Force Spinner to center of the button
            $spinner.css({
                'float': 'none',
                'margin': '0',
                'visibility': 'visible',
                'display': 'inline-block',
                'vertical-align': 'middle'
            });

            $.post(inteaili_admin_data.ajax_url, {
                action: 'inteaili_delete_chat_log',
                id: logId,
                nonce: inteaili_admin_data.nonce
            }, function(response) {
                if (response.success) {
                    $btn.closest('tr').fadeOut(400, function() { 
                        $(this).remove(); 
                        const $countEl = $('.inteaili-stat-card h3').first();
                        if ($countEl.length) {
                            $countEl.text(parseInt($countEl.text()) - 1);
                        }
                    });
                } else {
                    alert('Error: ' + (response.data || 'Unknown error occurred'));
                    // RESET UI
                    $spinner.remove();
                    $icon.css({ 'visibility': 'visible', 'width': '', 'margin': '' });
                    $btn.prop('disabled', false);
                }
            }).fail(function() {
                alert(inteaili_admin_data.i18n_server_error || 'Server error.');
                // RESET UI
                $spinner.remove();
                $icon.css({ 'visibility': 'visible', 'width': '', 'margin': '' });
                $btn.prop('disabled', false);
            });
        });

        /**
         * UI Feedback for Settings Form Submission
         */
        $('form[action="options.php"]').on('submit', function() {
            const $submitBtn = $(this).find('input[type="submit"]');
            if ($submitBtn.length) {
                $submitBtn.val(inteaili_admin_data.i18n_saving || 'Saving...').prop('disabled', true);
            }
        });

        /**
         * Color Picker Interaction
         */
        const $colorInput = $('#inteaili_primary_color_input');
        const $colorHex   = $('#inteaili_primary_color_hex');
        const $colorReset = $('#reset_primary_color');

        $colorInput.on('input', function() {
            const selectedColor = $(this).val().toUpperCase();
            $colorHex.text(selectedColor);
            $('#inteaili_logo_preview').css('border-color', selectedColor);
        });

        $colorReset.on('click', function(e) {
            e.preventDefault();
            const defaultColor = '#000000';
            $colorInput.val(defaultColor);
            $colorHex.text(defaultColor);
            $('#inteaili_logo_preview').css('border-color', '#f3f4f6');
        });

        /**
         * Export Chat Log to CSV
         */
        $(document).on('click', '.action-export-chat', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const logId = $btn.attr('data-id');
            const $icon = $btn.find('.dashicons');
        
            $btn.prop('disabled', true).css('opacity', '0.5');
            $icon.addClass('spin');
        
            $.ajax({
                type: 'POST',
                url: inteaili_admin_data.ajax_url,
                data: {
                    action: 'inteaili_export_chat_log',
                    id: logId,
                    nonce: inteaili_admin_data.nonce
                },
                success: function(response) {
                    $btn.prop('disabled', false).css('opacity', '1');
                    $icon.removeClass('spin');
        
                    if (response.success) {
                        const blob = new Blob([response.data.csv_data], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement("a");
                        link.href = URL.createObjectURL(blob);
                        link.download = response.data.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).css('opacity', '1');
                    $icon.removeClass('spin');
                    alert(inteaili_admin_data.i18n_system_error || 'A system error occurred.');
                }
            });
        });

        /**
         * Toggle LLM Settings Visibility
         */
        function toggleLLMSettings() {
            const selected = $('#inteaili_llm_model_selector').val();
            $('.llm-settings-group').hide();
            $('#settings-group-' + selected).show();
        }

        $('#inteaili_llm_model_selector').on('change', toggleLLMSettings);
        
        // Run on load
        toggleLLMSettings();
    });

})(jQuery);