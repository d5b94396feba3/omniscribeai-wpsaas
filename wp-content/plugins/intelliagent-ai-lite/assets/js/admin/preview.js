/**
 * Preview JavaScript for IntelliAgent AI Lite
 *
 * Real-time synchronization between admin settings and the 
 * chatbot preview widget.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Preview Functionality
     */
    $(function() {

        /**
         * Helper to prevent XSS in preview content
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
         * Syncs the Admin Preview widget with form inputs.
         * Attached to window for access by the Media Uploader script.
         */
        window.syncAdminPreview = function() {
            // Display Name
            const $nameInput = $('input[name="inteaili_display_name"]');
            const name = $nameInput.length ? $nameInput.val() : (localStorage.getItem('inteaili_preview_name') || 'AI Assistant');
            if ($nameInput.length) {
                localStorage.setItem('inteaili_preview_name', name);
            }

            // Welcome Message
            const $welcomeInput = $('textarea[name="inteaili_welcome_msg"]');
            const welcomeText = $welcomeInput.length ? $welcomeInput.val() : (localStorage.getItem('inteaili_preview_welcome') || '');
            if ($welcomeInput.length) {
                localStorage.setItem('inteaili_preview_welcome', welcomeText);
            }

            // Input Placeholder
            const $placeholderInput = $('input[name="inteaili_placeholder"]');
            const placeholder = $placeholderInput.length ? $placeholderInput.val() : (localStorage.getItem('inteaili_preview_placeholder') || 'Message...');
            if ($placeholderInput.length) {
                localStorage.setItem('inteaili_preview_placeholder', placeholder);
            }

            // Branding & Visuals
            const brandColor = $('#inteaili_primary_color_input').val() || '#000000';
            const iconSelect = $('select[name="inteaili_bubble_icon"]').val();

            let avatarUrl = $('#inteaili_logo_preview').attr('src');
            if (!avatarUrl || avatarUrl === '' || avatarUrl.includes('default-avater.svg')) {
                avatarUrl = (typeof inteaili_admin_assets !== 'undefined') ? inteaili_admin_assets.default_avatar : '';
            }

            // Theme Mode (Light/Dark)
            const $themeInput = $('input[name="inteaili_theme_mode"]:checked');
            const themeMode = $themeInput.length ? $themeInput.val() : (localStorage.getItem('inteaili_preview_theme') || 'light');
            if ($themeInput.length) {
                localStorage.setItem('inteaili_preview_theme', themeMode);
            }

            // Update Preview Selectors
            const $preview = $('#inteaili-admin-preview-container');
            const $wrapper = $preview.find('#inteaili-wrapper'); 

            if (!$wrapper.length) {
                return;
            }

            // Apply Theme and Text
            $wrapper.removeClass('theme-light theme-dark').addClass('theme-' + themeMode);
            $preview.find('.header-name, .msg-bot-name').text(name);
            $preview.find('#chat-input').attr('placeholder', placeholder);
            
            // Process Welcome Message Lines
            const lines = welcomeText.split('\n').filter(l => l.trim() !== "");
            let welcomeHtml = '';
            
            $.each(lines, function(index, line) {
                const safeLine = escapeHtml(line);
                const safeName = escapeHtml(name);
                const safeAvatar = encodeURI(avatarUrl);

                welcomeHtml += `
                    <div class="msg ai welcome-msg">
                        <div class="msg-avatar-container">
                            ${index === 0 ? `<img src="${safeAvatar}" class="msg-avatar" alt="">` : ''}
                        </div>
                        <div class="msg-content-wrapper">
                            ${index === 0 ? `<div class="msg-bot-name">${safeName}</div>` : ''}
                            <div class="bubble">${safeLine}</div>
                        </div>
                    </div>`;
            });
            
            $preview.find('.welcome-msg').remove();
            $preview.find('#chat-messages').prepend(welcomeHtml);

            // Apply Dynamic Styles
            $preview.find('#ai-chat-bubble, .user .bubble').css('background-color', brandColor);
            $preview.find('.header-logo, .msg-avatar').attr('src', avatarUrl);

            // Launcher Icon Logic
            const $iconContainer = $preview.find('.launcher-img-container');
            if (iconSelect === 'logo') {
                $iconContainer.removeClass('is-default-icon').html(`<img src="${encodeURI(avatarUrl)}" class="launcher-img" alt="">`);
            } else {
                $iconContainer.addClass('is-default-icon').html('<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>');
            }
        };

        /**
         * Event Listeners for Live Preview Triggers
         */
        $(document).on('input change', '.admin-preview-trigger, #inteaili_primary_color_input, select[name="inteaili_bubble_icon"], input[name="inteaili_theme_mode"]', function() {
            window.syncAdminPreview();
        });

        $(document).on('change', 'input[name="inteaili_theme_mode"]', function() {
            const $card = $(this).closest('.inteaili-theme-card');
            $('.inteaili-theme-card').removeClass('is-active');
            $card.addClass('is-active');
            window.syncAdminPreview();
        });

        // Run on initial load
        window.syncAdminPreview();
    });

})(jQuery);