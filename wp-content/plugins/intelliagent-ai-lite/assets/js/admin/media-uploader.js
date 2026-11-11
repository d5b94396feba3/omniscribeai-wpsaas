/**
 * Media Uploader JavaScript for IntelliAgent AI Lite
 *
 * Manages the WordPress Media Library integration for avatars
 *
 * @package  IntelliAgent_AI_Lite
 * @since    1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Media Uploader Functionality
     */
    $(function() {
        // Exit if WordPress media API is not available
        if (typeof wp === 'undefined' || !wp.media) {
            return;
        }

        /** @type {Object} Cache for media frames to prevent duplicate instances */
        const frame_cache = {};

        /**
         * Generic Wrapper for WordPress Media Frame
         * @param {string}   button_id - Selector for the trigger button.
         * @param {Object}   options   - Config for the media frame (title, button text).
         * @param {Function} callback  - Logic to execute upon selection.
         */
        function open_media_frame(button_id, options, callback) {
            $(document).on('click', button_id, function(e) {
                e.preventDefault();

                // Use cached frame if it exists
                if (frame_cache[button_id]) {
                    frame_cache[button_id].open();
                    return;
                }

                // Initialize new WP Media Frame
                frame_cache[button_id] = wp.media({
                    title: options.title || (inteaili_admin_data.i18n_select_media || 'Select Media'),
                    button: { 
                        text: options.button_text || (inteaili_admin_data.i18n_use_media || 'Use this Media') 
                    },
                    multiple: false
                });

                // Selection event handler
                frame_cache[button_id].on('select', function() {
                    const attachment = frame_cache[button_id].state().get('selection').first().toJSON();
                    callback(attachment);
                });

                frame_cache[button_id].open();
            });
        }

        /**
         * Avatar/Logo Uploader Initialization
         */
        open_media_frame('#inteaili_upload_btn', {
            title: inteaili_admin_data.i18n_select_avatar || 'Select Avatar',
            button_text: inteaili_admin_data.i18n_use_image || 'Use this Image'
        }, function(attachment) {
            $('#inteaili_logo_id').val(attachment.id).trigger('change');
            $('#inteaili_logo_preview').attr('src', attachment.url).show();
            $('#inteaili_remove_btn').show();
            
            if (typeof window.syncAdminPreview === 'function') {
                window.syncAdminPreview();
            }
        });


        /**
         * Remove Avatar Action
         */
        $(document).on('click', '#inteaili_remove_btn', function() {
            $('#inteaili_logo_id').val('').trigger('change');
            const defaultAvatar = (typeof inteaili_admin_assets !== 'undefined') ? inteaili_admin_assets.default_avatar : '';
            $('#inteaili_logo_preview').attr('src', defaultAvatar);
            $(this).hide();
            
            if (typeof window.syncAdminPreview === 'function') {
                window.syncAdminPreview();
            }
        });

    });

})(jQuery);