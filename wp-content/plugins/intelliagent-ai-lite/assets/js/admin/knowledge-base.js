/**
 * KB JavaScript for IntelliAgent AI Lite
 *
 * Handles knowledge base synchronization, content discovery,
 * and AI system prompt generation.
 *
 * @package           IntelliAgent_AI_Lite
 * @since             1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Knowledge Base Admin Actions
     * * @return void
     */
    $(function() {
        /** @var {Array} pages Stores the list of discovered pages and products */
        let pages = [];

        /** Cache jQuery selectors for performance */
        const $discoveryLoader   = $('#discovery-loader');
        const $syncContainer     = $('#sync-data-container');
        const $syncSuccessMsg    = $('#sync-success-msg');
        const $syncProgressBar   = $('#sync-progress-bar');
        const $syncTableBody     = $('#sync-table-body');
        const $summaryBadge      = $('#sync-summary-badge');
        const $bulkSyncBtn       = $('#run-bulk-sync');

        /**
         * Escapes HTML strings to prevent XSS attacks.
         *
         * @param {string} text The raw string to be escaped.
         * @return {string} The safely escaped HTML string.
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
         * Check URL parameters to auto-trigger discovery if on the KB Sync tab.
         */
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'kb-sync') {
            runDiscovery();
        }

        /**
         * Handles the sliding toggle of the sync table interface.
         * * @return void
         */
        $(document).on('click', '#toggle-sync-table', function() {
            const $content = $('#sync-collapsible-content');
            $content.slideToggle(300);
            $(this).find('.toggle-icon').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        });

        /**
         * Triggers a fresh discovery scan via AJAX.
         * * @return void
         */
        $(document).on('click', '#start-discovery-refresh', function() {
            runDiscovery();
        });

        /**
         * Executes the discovery AJAX request to map pages and products.
         *
         * @return void
         */
        function runDiscovery() {
            $discoveryLoader.show();
            $syncContainer.hide();
            $syncSuccessMsg.hide();
            $syncProgressBar.hide();
            
            $.post(inteaili_admin_data.ajax_url, {
                action: 'inteaili_sync_website_knowledge',
                sync_step: 'discovery',
                nonce: inteaili_admin_data.nonce
            }, function(res) {
                $discoveryLoader.hide();
                if (res.success) {
                    pages = res.data.pages;
                    renderRows(pages);
                    updateSyncButtonState();
                    $syncContainer.fadeIn();
                    
                    const pageCount = res.data.pages_count || 0;
                    const prodCount = res.data.product_count || 0;

                    let summaryText = `${pageCount} ${inteaili_admin_data.i18n_pages || 'Pages'}`;
                    if (prodCount > 0) {
                        summaryText += ` & ${prodCount} ${inteaili_admin_data.i18n_products || 'Products'}`;
                    }
                    
                    $summaryBadge.text(summaryText).show();
                }
            }).fail(function() {
                $discoveryLoader.hide();
                alert(inteaili_admin_data.i18n_server_error || 'Error during discovery.');
            });
        }

        /**
         * Renders the table rows for the discovery results.
         *
         * @param {Array} data The array of page/product objects.
         * @return void
         */
        function renderRows(data) {
            let rows = '';
            $.each(data, function(i, p) {
                const isSynced   = p.status === 'Synced';
                const badgeClass = isSynced ? 'status-approved' : 'status-pending';
                const statusText = isSynced ? (inteaili_admin_data.i18n_synced || 'Synced') : (inteaili_admin_data.i18n_pending || 'Pending');
                const isProduct  = p.title.includes('[Product]');
                const typeLabel  = isProduct ? 'Product' : 'Page';
                const typeIcon   = isProduct ? 'cart' : 'admin-page';
                
                rows += `<tr data-url="${escapeHtml(p.url)}">
                    <td class="col-source">
                        <div style="font-weight: 600; color: #1e293b;">${escapeHtml(p.title.replace('[Product] ', ''))}</div>
                        <div style="font-size: 11px; color: #94a3b8;">${escapeHtml(p.url)}</div>
                    </td>
                    <td class="col-type">
                        <span style="font-size: 12px; font-weight: 600; color: ${isProduct ? '#8b5cf6' : '#64748b'}">
                            <span class="dashicons dashicons-${typeIcon}" style="font-size: 15px; width:15px; height:15px; margin-right:4px;"></span>
                            ${typeLabel}
                        </span>
                    </td>
                    <td class="col-status">
                        <span class="status-badge ${badgeClass}">${statusText}</span>
                        <div class="last-sync-col" style="font-size: 10px; color: #94a3b8; margin-top: 4px; margin-right:4px;">Last: ${escapeHtml(p.last)}</div>
                    </td>
                </tr>`;
            });
            $syncTableBody.html(rows);
        }
        
        /**
         * Updates the bulk sync button label and state based on pending pages.
         *
         * @return {void}
         */
        function updateSyncButtonState() {
            if (!Array.isArray(pages)) {
                return;
            }

            const pendingPages = pages.filter(p => p && p.status !== 'Synced').length;
            
            const $savedSvg = $('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" style="display: block; flex-shrink: 0; fill: currentColor;"><path d="M14.83 5.17L7.5 12.5 4.17 9.17l-1.41 1.42 4.75 4.75 8.75-8.75z"/></svg>');
            const $updateSvg = $('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" style="display: block; flex-shrink: 0; fill: currentColor;"><path d="M10.2 3.28c2.4 0 4.6 1 6.2 2.6l2.1-2.1v6.2h-6.2l2.4-2.4c-1.2-1.2-2.8-1.9-4.5-1.9-3.3 0-6.1 2.5-6.4 5.7H1.6c.3-4.5 4-8.1 8.6-8.1zm.1 13.4c-2.4 0-4.6-1-6.2-2.6l-2.1 2.1v-6.2h6.2l-2.4 2.4c1.2 1.2 2.8 1.9 4.5 1.9 3.3 0 6.1-2.5 6.4-5.7h2.2c-.3 4.5-4 8.1-8.6 8.1z"/></svg>');

            $bulkSyncBtn.css({
                'display': 'inline-flex',
                'align-items': 'center',
                'justify-content': 'center',
                'gap': '6px'
            });

            if (pendingPages === 0) {
                const upToDateLabel = inteaili_admin_data.i18n_uptodate || 'Everything Up to Date';
                
                $bulkSyncBtn.empty()
                    .append($savedSvg)
                    .append(document.createTextNode(upToDateLabel))
                    .prop('disabled', false);
                    
                $bulkSyncBtn.removeClass('button-primary').addClass('button-secondary');
            } else {
                const syncPendingLabel = inteaili_admin_data.i18n_sync_pending || 'Sync %s Pending Items';
                const totalCountString = String(parseInt(pendingPages, 10));
                const finalLabel = syncPendingLabel.replace('%s', totalCountString);
                
                $bulkSyncBtn.empty()
                    .append($updateSvg)
                    .append(document.createTextNode(finalLabel))
                    .prop('disabled', false);
                    
                $bulkSyncBtn.removeClass('button-secondary').addClass('button-primary');
            }
        }

        /**
         * Handles the bulk synchronization process using an asynchronous loop.
         *
         * @return Promise<void>
         */
        $bulkSyncBtn.on('click', async function() {
            const syncingLabel = inteaili_admin_data.i18n_syncing || 'Syncing...';
            const $btn = $(this).prop('disabled', true).text(syncingLabel);
            const $syncFill = $('#sync-fill');
            const $syncPercent = $('#sync-percent');
            const $syncMsg = $('#sync-msg');

            $syncProgressBar.slideDown();
            $syncSuccessMsg.hide();
            
            for (let i = 0; i < pages.length; i++) {
                let p = pages[i];
                let percent = Math.round(((i + 1) / pages.length) * 100);
                
                const processingLabel = inteaili_admin_data.i18n_processing || 'Processing:';
                $syncMsg.text(`${processingLabel} ${p.title.replace('[Product] ', '')}`);
                $syncFill.css('width', percent + '%');
                $syncPercent.text(percent + '%');

                try {
                    const response = await $.post(inteaili_admin_data.ajax_url, {
                        action: 'inteaili_sync_website_knowledge',
                        sync_step: 'sync_page',
                        url: p.url,
                        nonce: inteaili_admin_data.nonce
                    });

                    if (response && response.success) {
                        const $row = $(`tr[data-url="${p.url}"]`);
                        $row.find('.status-badge').attr('class', 'status-badge status-approved').text(inteaili_admin_data.i18n_synced || 'Synced');
                        // "Just now" update for the specific row
                        $row.find('.last-sync-col').text(inteaili_admin_data.i18n_justnow || 'Last: Just now');
                        pages[i].status = 'Synced';
                    }
                } catch (err) {
                    console.error("Sync error for " + p.url, err);
                }
            }

            $syncProgressBar.delay(500).slideUp(400, function() {
                $syncSuccessMsg.fadeIn();
                updateSyncButtonState();
                setTimeout(() => $syncSuccessMsg.fadeOut(500), 4000);
            });
        });

        /**
         * Generate AI System Prompt logic
         * * @param {Event} e The click event.
         * @return void
         */
        $(document).on('click', '#btn-generate-ai-prompt', function(e) {
            e.preventDefault();
            
            const confirmMsg = inteaili_admin_data.i18n_confirm_gen || "This will replace your current instructions. Proceed?";
            if (!confirm(confirmMsg)) {
                return;
            }

            const $btn = $(this);
            const $textarea = $('#inteaili_ai_persona_area');
            const $loader = $('#prompt-gen-loader');
            const $unsavedBar = $('#inteaili-unsaved-bar');

            $btn.prop('disabled', true).css('opacity', '0.5');
            $textarea.css('opacity', '0.3');
            $loader.fadeIn(300);

            $.post(inteaili_admin_data.ajax_url, {
                action: 'generate_ai_persona',
                nonce: inteaili_admin_data.nonce
            }, function(response) {
                $loader.hide();
                $btn.prop('disabled', false).css('opacity', '1');
                $textarea.css('opacity', '1');

                if (response.success) {
                    $textarea.val(response.data).addClass('field-changed');
                    $unsavedBar.css('display', 'flex');
                } else {
                    alert(response.data || 'Error generating prompt.');
                }
            }).fail(function() {
                $loader.hide();
                $btn.prop('disabled', false).css('opacity', '1');
                $textarea.css('opacity', '1');
                alert(inteaili_admin_data.i18n_server_error || 'Server error.');
            });
        });
    });

})(jQuery);