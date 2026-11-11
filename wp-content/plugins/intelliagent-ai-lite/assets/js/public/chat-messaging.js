/**
 * Chat Messaging & UI Interaction JavaScript for IntelliAgent AI Lite
 *
 * Handles the core chat loop, including sending messages via AJAX,
 * auto-resizing the input, rendering AI responses, and handling 
 * user feedback/copy actions.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Chat Messaging Functionality
     */
    $(function() {
        const chat = window.INTEAILI_Chat;
        const $input = $('#chat-input');
        const $menuEndChat = $('#menu-end-chat');
        const publicData = (typeof inteaili_public_data !== 'undefined') ? inteaili_public_data : { ajax_url: '', nonce: '' };

        /**
         * Auto-resize textarea based on content height
         */
        function autoResizeTextarea() {
            const el = $input[0];
            if (!el) {
                return;
            }
            el.style.height = 'auto';
            const newHeight = el.scrollHeight;
            el.style.height = newHeight + 'px';
            
            if (newHeight >= 150) {
                $input.addClass('is-scrolling');
            } else {
                $input.removeClass('is-scrolling');
            }
        }

        // Bind resize events
        $input.on('input propertychange cut paste drop keyup', autoResizeTextarea);

        // Bind send events
        $(document).on('click', '#chat-send', sendMessage);
        
        $input.on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { 
                e.preventDefault(); 
                sendMessage(); 
            }
        });

        /**
         * Process and Send Message
         */
        function sendMessage() {
            const text = $input.val().trim();
            if (!text) {
                return;
            }

            // Append User Bubble safely
            chat.$messages.append(`<div class="msg user"><div class="bubble">${$('<div>').text(text).html()}</div></div>`);
            
            // Reset input UI
            $input.val('');
            $input[0].style.height = '44px';
            $input.removeClass('is-scrolling');
            chat.scrollBottom();

            // Setup Typing Indicator
            const tId = 'typing-' + Date.now();
            const safeBotImg = encodeURI(chat.botImg);
            
            chat.$messages.append(`
                <div class="msg ai" id="${tId}">
                    <div class="msg-avatar-container"><img src="${safeBotImg}" class="msg-avatar" alt=""></div>
                    <div class="msg-content-wrapper">
                        <div class="msg-bot-name">${chat.botName}</div>
                        <div class="bubble typing-dots"><span></span><span></span><span></span></div>
                    </div>
                </div>`);
            
            chat.scrollBottom();
            $menuEndChat.removeClass('is-disabled');

            // AJAX Request to AI Endpoint
            $.ajax({
                type: 'POST',
                url: publicData.ajax_url,
                data: { 
                    action: 'inteaili_send_chat_message', 
                    message: text, 
                    chat_nonce: publicData.nonce 
                },
                success: function(res) {
                    $('#' + tId).remove();
                    if (res.success) {
                        const unix = Math.floor(Date.now() / 1000);
                        chat.$messages.append(renderAiBubble(res.data.reply, unix));
                        chat.scrollBottom();
                        chat.updateTimestamps();
                    } else {
                        console.error('AI Error:', res.data.message);
                    }
                },
                error: function() {
                    $('#' + tId).remove();
                }
            });
        }

        /**
         * Render HTML for AI Response Bubble
         * @param {string} reply 
         * @param {number} unix 
         * @return {string}
         */
        function renderAiBubble(reply, unix) {
            const i18n = publicData;
            return `
                <div class="msg ai">
                    <div class="msg-avatar-container"><img src="${encodeURI(chat.botImg)}" class="msg-avatar" alt=""></div>
                    <div class="msg-content-wrapper">
                        <div class="msg-bot-name">${chat.botName}</div>
                        <div class="bubble">${reply}</div>
                        <div class="msg-footer">
                            <span class="msg-time" data-time="${unix}">${i18n.just_now || 'just now'}</span>
                            <div class="msg-actions">
                                <button class="ai-action-btn copy-msg" title="${i18n.i18n_copy || 'Copy'}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                                <button class="ai-action-btn regen-msg" title="${i18n.i18n_regen || 'Regenerate'}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                </button>
                                <button class="ai-action-btn rate-msg like" data-rating="like" title="${i18n.i18n_helpful || 'Helpful'}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                                </button>
                                <button class="ai-action-btn rate-msg dislike" data-rating="dislike" title="${i18n.i18n_not_helpful || 'Not helpful'}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        /**
         * Handle Message Copy to Clipboard
         */
        $(document).on('click', '.copy-msg', function() {
            const $btn = $(this);
            const text = $btn.closest('.msg-content-wrapper').find('.bubble').text();
            navigator.clipboard.writeText(text);
            
            const originalSvg = $btn.html();
            // WP Checkmark SVG
            $btn.html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>');
            $btn.css('color', '#10b981'); 
        
            setTimeout(() => {
                $btn.html(originalSvg);
                $btn.css('color', ''); 
            }, 2000);
        });

        /**
         * Handle Helpful/Not Helpful Feedback
         */
        $(document).on('click', '.rate-msg', function() {
            const $btn = $(this);
            const $wrapper = $btn.closest('.msg-content-wrapper');
            
            $.post(publicData.ajax_url, {
                action: 'inteaili_submit_chat_feedback',
                nonce: publicData.nonce,
                query: $wrapper.closest('.msg.ai').prev('.msg.user').find('.bubble').text(),
                response: $wrapper.find('.bubble').text(),
                rating: $btn.data('rating')
            });
            
            $btn.parent().find('.rate-msg').removeClass('active-rating');
            $btn.addClass('active-rating');
        });

        /**
         * Handle Message Regeneration
         */
        $(document).on('click', '.regen-msg', function() {
            const $btn = $(this);
            const $aiMsg = $btn.closest('.msg.ai');
            const query = $aiMsg.prev('.msg.user').find('.bubble').text();
            if (!query) return;

            // Apply rotation to SVG
            $btn.addClass('inteaili-loading');
            
            $aiMsg.find('.bubble').html('<div class="typing-dots"><span></span><span></span><span></span></div>');
            
            $.post(publicData.ajax_url, { 
                action: 'inteaili_send_chat_message',
                message: query, 
                is_regen: true, 
                chat_nonce: publicData.nonce 
            }, function(res) {
                $btn.removeClass('inteaili-loading');
                if (res.success) {
                    $aiMsg.find('.bubble').html(res.data.reply);
                    chat.updateTimestamps();
                }
            });
        });
    });

})(jQuery);