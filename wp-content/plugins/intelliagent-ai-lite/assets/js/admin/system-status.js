/**
 * System Status Scripts for IntelliAgent AI Lite.
 * * Handles report copying and UI interactions.
 * * @package IntelliAgent_AI_Lite
 */
(function($) {
    'use strict';

    $(function() {
        /**
         * Handle copying system diagnostics to clipboard.
         */
        $('#inteaili-copy-diagnostics').on('click', function(e) {
            e.preventDefault();

            let $btn = $(this);
            let originalText = $btn.html();
            let reportText = "### IntelliAgent AI System Report ###\n";
            reportText += "Generated: " + new Date().toLocaleString() + "\n\n";

            // Loop through each status section
            $('.inteaili-status-header').each(function() {
                let sectionTitle = $(this).find('h2').text().trim();
                reportText += "== " + sectionTitle + " ==\n";
                
                let $table = $(this).next('.inteaili-status-table');
                $table.find('tbody tr').each(function() {
                    let metric = $(this).find('td:first').text().trim();
                    let value  = $(this).find('td:last').text().trim();
                    if (metric && value) {
                        reportText += metric + ": " + value + "\n";
                    }
                });
                reportText += "\n";
            });

            // Clipboard API with safety check
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(reportText).then(function() {
                    $btn.html('<span class="dashicons dashicons-yes"></span> ' + (inteaili_admin_data.i18n_synced || 'Copied!'));
                    setTimeout(function() {
                        $btn.html(originalText);
                    }, 2000);
                }).catch(function(err) {
                    console.error('Action failed: ', err);
                });
            } else {
                // Fallback for older browsers
                let textArea = document.createElement("textarea");
                textArea.value = reportText;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    $btn.html('<span class="dashicons dashicons-yes"></span> ' + (inteaili_admin_data.i18n_synced || 'Copied!'));
                } catch (err) {
                    console.error('Fallback failed: ', err);
                }
                document.body.removeChild(textArea);
                setTimeout(function() {
                    $btn.html(originalText);
                }, 2000);
            }
        });
    });

})(jQuery);