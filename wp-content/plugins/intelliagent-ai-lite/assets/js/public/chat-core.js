/**
 * Public Chat Interface JavaScript for IntelliAgent AI Lite
 *
 * Handles the front-end chat widget interactions, including cookie-based
 * popup management, message scrolling, and real-time timestamp updates.
 *
 * @package IntelliAgent_AI_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * AICA Chat Handler Object
     */
    window.INTEAILI_Chat = {
        $window: null,
        $messages: null,
        $input: null,
        botImg: '',
        botName: '',
        $welcomeHtml: null,
        popupTimer: null,

        /**
         * Get a cookie value by name
         * @param {string} name 
         * @return {string|null}
         */
        getCookie: function(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) {
                return parts.pop().split(';').shift();
            }
            return null;
        },

        /**
         * Set a cookie value
         * @param {string} name 
         * @param {string} value 
         * @param {number} seconds 
         */
        setCookie: function(name, value, seconds) {
            let expires = "";
            if (seconds) {
                const date = new Date();
                date.setTime(date.getTime() + (seconds * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            const secure = window.location.protocol === 'https:' ? "; Secure" : "";
            document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax" + secure;
        },

        /**
         * Initialize the chat widget
         */
        init: function() {
            this.$window = $('#inteaili-c-w');
            this.$messages = $('#chat-messages');
            this.$input = $('#chat-input');
            
            this.botImg = $('.chat-header .header-logo').attr('src');
            this.botName = $('.chat-header .header-name').text();
            
            this.$welcomeHtml = $('.welcome-msg').clone();

            this.bindCoreEvents();
            this.updateTimestamps();
            
            const popupDelay = (typeof inteaili_public_data !== 'undefined' && inteaili_public_data.popup_delay) 
                ? parseInt(inteaili_public_data.popup_delay) 
                : 3000;
            
            this.popupTimer = setTimeout(() => {
                this.triggerPopup();
            }, popupDelay);

            // Update timestamps every 30 seconds
            setInterval(() => {
                this.updateTimestamps();
            }, 30000); 
        },

        /**
         * Bind core UI events using delegated listeners
         */
        bindCoreEvents: function() {
            const self = this;

            $(document).on('click', '#ai-chat-bubble', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.toggleChat();
            });

            $(document).on('click', '#chat-close-btn', function(e) {
                e.preventDefault();
                self.toggleChat();
            });
            
            $(document).on('click', '.popup-bubble-item', function(e) {
                if ($(e.target).is('#close-welcome-popup')) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#ai-chat-welcome-popup').fadeOut(200);
                    self.setCookie('inteaili_popup_shown', 'true', 600);
                    return;
                }
                self.toggleChat();
            });
        },

        /**
         * Toggle chat window visibility
         */
        toggleChat: function() {
            const self = this; 
            const $popup = $('#ai-chat-welcome-popup');
            const $window = $('#inteaili-c-w');

            if (this.popupTimer) {
                clearTimeout(this.popupTimer);
                this.popupTimer = null;
            }

            if ($window.hasClass('hidden')) {
                this.setCookie('inteaili_popup_shown', 'true', 600);

                $popup.stop(true, true).fadeOut(200);
                $window.stop(true, true).fadeIn(300, function() {
                    $(this).removeClass('hidden').css('display', 'flex');
                    self.scrollBottom();
                });
                setTimeout(() => self.$input.focus(), 150);
            } else {
                $window.stop(true, true).fadeOut(300, function() {
                    $(this).addClass('hidden');
                });
            }
        },

        /**
         * Smooth scroll to bottom of chat messages
         */
        scrollBottom: function() {
            if (this.$messages.length) {
                this.$messages.animate({ scrollTop: this.$messages[0].scrollHeight }, 300);
            }
        },

        /**
         * Trigger the welcome popup based on throttle cookies
         */
        triggerPopup: function() {
            const $popup = $('#ai-chat-welcome-popup');
            const isThrottled = this.getCookie('inteaili_popup_shown');

            if (this.$window.length && this.$window.hasClass('hidden') && !isThrottled) {
                $popup.hide().removeClass('hidden').fadeIn(500);
                this.setCookie('inteaili_popup_shown', 'true', 600);
            }
        },

        /**
         * Update human-readable timestamps (e.g., "5m ago")
         */
        updateTimestamps: function() {
            const now = Math.floor(Date.now() / 1000);
            const i18n = (typeof inteaili_public_data !== 'undefined') ? inteaili_public_data : {};

            $('.msg-time').each(function() {
                const sentTime = parseInt($(this).attr('data-time'));
                if (!sentTime || isNaN(sentTime)) {
                    return;
                }

                const diff = now - sentTime;
                if (diff < 60) {
                    $(this).text(i18n.just_now || 'just now');
                } else if (diff < 3600) {
                    const mins = Math.floor(diff / 60);
                    const label = i18n.m_ago || 'm ago';
                    $(this).text(mins + label);
                } else {
                    const hrs = Math.floor(diff / 3600);
                    const label = i18n.h_ago || 'h ago';
                    $(this).text(hrs + label);
                }
            });
        }
    };

    /**
     * Document Ready Entry Point
     */
    $(function() {
        if ($('#inteaili-wrapper').length) {
            window.INTEAILI_Chat.init();
        }
    });

})(jQuery);