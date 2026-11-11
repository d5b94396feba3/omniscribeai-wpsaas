/**
 * Chat Options & Session Management for IntelliAgent AI Lite
 *
 * Handles menu interactions, start-fresh logic, and session-end 
 * confirmations for the public chat widget.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Chat Menu & Session Actions
     */
    $(function() {
        // Cache external objects and core selectors
        const chat = window.INTEAILI_Chat;
        const $menu = $('#chat-options-popup');
        const $overlay = $('#chat-confirm-overlay');
        const $endChatBtn = $('#menu-end-chat');
        const $confirmExecute = $('#confirm-execute');
        const $confirmTitle = $('#confirm-title');
        const $confirmDesc = $('#confirm-desc');
        
        const publicData = (typeof inteaili_public_data !== 'undefined') ? inteaili_public_data : { ajax_url: '', nonce: '' };
        
        /** @type {string} Stores the action mode ('clear' or 'end') */
        let mode = '';

        /**
         * Update visibility/state of the "End Chat" button 
         * based on conversation existence.
         */
        function updateEndChatVisibility() {
            const userMsgCount = chat.$messages.find('.msg.user').length;
            if (userMsgCount > 0) {
                $endChatBtn.removeClass('is-disabled');
            } else {
                $endChatBtn.addClass('is-disabled');
            }
        }

        /**
         * Toggle Chat Options Menu
         */
        $(document).on('click', '#chat-menu-btn', function(e) { 
            e.stopPropagation(); 
            updateEndChatVisibility(); 
            $menu.toggleClass('hidden'); 
        });

        /**
         * Close Menu on Outside Click
         */
        $(document).on('click', function() {
            $menu.addClass('hidden');
        });

        /**
         * Start Fresh Action Trigger
         */
        $(document).on('click', '#menu-start-new', function() {
            mode = 'clear';
            const title = publicData.i18n_start_fresh || 'Start fresh?';
            const desc = publicData.i18n_start_fresh_desc || 'This will clear your current messages so you can start a new conversation.';
            showOverlay(title, desc);
        });

        /**
         * End Chat Action Trigger
         */
        $(document).on('click', '#menu-end-chat', function() {
            if ($(this).hasClass('is-disabled')) {
                return;
            }
            mode = 'end';
            const title = publicData.i18n_end_chat_title || 'End this chat?';
            const desc = publicData.i18n_end_chat_desc || 'This will clear your current messages and start a new conversation again.';
            showOverlay(title, desc);
        });

        /**
         * Display the Confirmation Overlay
         * * @param {string} title 
         * @param {string} desc 
         */
        function showOverlay(title, desc) {
            $menu.addClass('hidden');
            $confirmTitle.text(title);
            $confirmDesc.text(desc);
            $overlay.removeClass('hidden').hide().fadeIn(200);
        }

        /**
         * Cancel the Confirmation Overlay
         */
        $(document).on('click', '#confirm-cancel', function() {
            $overlay.fadeOut(200);
        });

        /**
         * Execute the Confirmed Action via AJAX
         */
        $confirmExecute.on('click', function() {
            const $btn = $(this);
            const originalText = $btn.text();
            
            $btn.prop('disabled', true).text(publicData.i18n_processing || 'Processing...');
            
            const action = 'inteaili_clear_chat_history';

            $.post(publicData.ajax_url, { 
                action: action, 
                nonce: publicData.nonce
            }, function(res) {
                $btn.prop('disabled', false).text(originalText);
                
                if (res.success) {
                    // Reset UI to initial state
                    chat.$messages.empty().append(chat.$welcomeHtml.clone());
                    updateEndChatVisibility();
                    $overlay.fadeOut(200);
                    
                    if (mode === 'end') {
                        chat.toggleChat();
                    }
                }
            }).fail(function() {
                $btn.prop('disabled', false).text(originalText);
                alert(publicData.i18n_server_error || 'Server error occurred.');
            });
        });
    });

})(jQuery);