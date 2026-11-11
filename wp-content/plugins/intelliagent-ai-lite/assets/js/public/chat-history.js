/**
 * Recent Chats History JavaScript for IntelliAgent AI Lite
 *
 * Handles the retrieval and display of past chat sessions, allowing
 * users to switch between historical conversations.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Recent Chats Functionality
     */
    $(function() {
        // Cache the global chat handler and data objects
        const chat = window.INTEAILI_Chat;
        const $recentView = $('#recent-chats-view');
        const $recentList = $('#recent-chats-list');
        const $menuPopup = $('#chat-options-popup');
        const publicData = (typeof inteaili_public_data !== 'undefined') ? inteaili_public_data : { ajax_url: '', nonce: '' };
        
        /** @type {Object} Storage for retrieved chat history objects */
        let recentData = {};

        /**
         * Helper to prevent XSS in chat previews
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
         * Trigger Recent Chats View via Menu
         */
        $(document).on('click', '#menu-view-recent', function(e) {
            e.stopPropagation();
            $menuPopup.addClass('hidden');
            $recentView.removeClass('hidden').show();
            
            const loadingMsg = publicData.i18n_loading_history || 'Loading history...';
            $recentList.html(`<div class="recent-loading"><i class="fa fa-spinner fa-spin fa-2x"></i><span>${loadingMsg}</span></div>`);
            
            // Fetch recent chats from server
            $.post(publicData.ajax_url, { 
                action: 'inteaili_get_recent_chats', 
                nonce: publicData.nonce 
            }, function(res) {
                if (res.success && res.data.length > 0) {
                    let html = '';
                    recentData = {};
                    
                    $.each(res.data, function(i, item) {
                        recentData[item.id] = item.history;
                        html += `<div class="recent-chat-item" data-id="${escapeHtml(item.id)}">
                            <span class="chat-date">${escapeHtml(item.date)}</span>
                            <span class="chat-preview">${escapeHtml(item.preview)}</span>
                        </div>`;
                    });
                    $recentList.html(html);
                } else {
                    const noChatsMsg = publicData.i18n_no_recent || 'No recent chats found.';
                    $recentList.html(`<div class="recent-loading">${noChatsMsg}</div>`);
                }
            }).fail(function() {
                const errorMsg = publicData.i18n_server_error || 'Error loading history.';
                $recentList.html(`<div class="recent-loading">${errorMsg}</div>`);
            });
        });

        /**
         * Return to Active Chat View
         */
        $(document).on('click', '#back-to-chat', function() {
            $recentView.hide().addClass('hidden');
        });

        /**
         * Load Specific Recent Chat Session
         */
        $(document).on('click', '.recent-chat-item', function() {
            const id = $(this).data('id');
            const history = recentData[id];
            
            if (!history) {
                return;
            }

            // Set session cookie to maintain context on reload
            document.cookie = "inteaili_chat_uuid=" + id + "; path=/; max-age=3600; SameSite=Lax";
            
            // Clear current view and restore welcome message
            chat.$messages.empty().append(chat.$welcomeHtml.clone());

            // Rebuild chat history bubbles
            $.each(history, function(i, msg) {
                const isBot = (msg.role === 'assistant' || msg.role === 'ai');
                const roleClass = isBot ? 'ai' : 'user';
                
                chat.$messages.append(`
                    <div class="msg ${roleClass}">
                        ${isBot ? `<div class="msg-avatar-container"><img src="${encodeURI(chat.botImg)}" class="msg-avatar" alt=""></div>` : ''}
                        <div class="msg-content-wrapper">
                            ${isBot ? `<div class="msg-bot-name">${escapeHtml(chat.botName)}</div>` : ''}
                            <div class="bubble">${msg.content}</div>
                        </div>
                    </div>`);
            });
            
            $recentView.hide().addClass('hidden');
            chat.scrollBottom();
        });
    });

})(jQuery);