/**
 * f2e-generate.js
 * Handles the main content generation logic and form data collection.
 */

window.F2E = window.F2E || {};

(function($) {
    "use strict";

    const Elements = window.F2E.Elements || {};
    const { i18n } = F2E.Constants || {};

    /**
     * Polls the server for current progress.
     */
    function procesProgress() {
        if (F2E.State.pollProgress) {
            clearInterval(F2E.State.pollProgress);
        }
        
        const iterations = F2E.Utils.getIterations();
        let retryCount = 0;
        const maxRetries = 20;
        
        F2E.State.pollProgress = setInterval(async () => {
            try {
                const response = await fetch(
                    MPAI_vars.ajax_url_progress,
                    {
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': MPAI_vars.nonce,
                            'Accept': 'application/json'
                        }
                    }
                );
    
                if (!response.ok) throw new Error("Progress check failed");
    
                const progress = await response.json();
    
                if (progress && progress.current !== undefined) {
                    retryCount = 0;
                    if (progress.current >= iterations) {
                        clearInterval(F2E.State.pollProgress);
                        F2E.State.pollProgress = null;
                    }
                }
            } catch (e) {
                retryCount++;
                if (retryCount >= maxRetries) {
                    clearInterval(F2E.State.pollProgress);
                }
            }
        }, 5000);
    }

    function isValidUrl(string) {
        try {
            const url = new URL(string);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (_) {
            return false;
        }
    }
    
    async function generateCode() {
        // UI Preparation
        Elements.$generateButton.prop('disabled', true).addClass('disabled');
        Elements.$loading.removeClass('f2e-hidden');
        Elements.$resultContent.addClass('f2e-hidden');
        $('.f2e-metadata-panel').remove();
        
        // Reset Tabs
        $('.f2e-tab-button').removeClass('active border-[var(--primary-color)] text-[var(--primary-color)]').addClass('text-[var(--text-gray-500)]');
        $('.f2e-tab-button[data-tab="preview"]').addClass('active border-[var(--primary-color)] text-[var(--primary-color)]');
        $('#code-tab, #preview-tab').addClass('hidden');

        Elements.$generateButton.html(`<span class="f2e-spinner"></span>${i18n.generating || 'Generating...'}`);

        // Data Collection
        const iterations = F2E.Utils.getIterations();
        const contentType = F2E.Utils.getContentType();
        const isYoutubeMode = Elements.$inputModeToggle.prop('checked');
        
        const description = Elements.$description.val().trim();
        const siteURLValue = Elements.$siteURL.val().trim();
        const descriptionYt = $('#f2e-description-yt').val().trim(); // Capture optional instructions

        // Validation based on mode
        if (isYoutubeMode) {
            if (!siteURLValue || !isValidUrl(siteURLValue)) {
                alert("Please enter a valid YouTube URL.");
                F2E.Utils.resetGenerateButton();
                return;
            }
        } else {
            if (!description) {
                alert(i18n.please_enter_description || "Please enter a topic or description.");
                F2E.Utils.resetGenerateButton();
                return;
            }
        }

        F2E.Loader.startLoader();

        // Build FormData
        const formData = new FormData();
        formData.append('output_type', 'html'); 
        formData.append('iterations', iterations);
        formData.append('content_type', contentType);
        formData.append('current_code', Elements.$resultCode.text().trim());
        
        // Mode-specific data
        if (isYoutubeMode) {
            formData.append('siteURL', siteURLValue);
            formData.append('description_yt', descriptionYt); // Sent to PHP
            formData.append('input_mode', 'youtube');
        } else {
            formData.append('description', description);
            formData.append('input_mode', 'text');
        }

        // Advanced Settings
        formData.append('target_keyword', Elements.$targetKeyword.val().trim());
        formData.append('audience_level', Elements.$audienceLevel.val().trim());
        formData.append('desired_tone', Elements.$desiredTone.val().trim());
        formData.append('language', Elements.$language.val().trim());
        formData.append('required_length', Elements.$requiredLength.val().trim());
        formData.append('call_to_action', Elements.$callToAction.val().trim());

        try {
            const data = await new Promise((resolve, reject) => {
                procesProgress();
                $.ajax({
                    url: MPAI_vars.ajax_url,
                    method: 'POST',
                    headers: { 'X-WP-Nonce': MPAI_vars.nonce },
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#f2e-iteration-heading').text(`Generating your ${F2E.Utils.getContentTypeDisplayName().toLowerCase()}...`);
                    },
                    success: (response) => resolve(response),
                    error: (jqXHR) => reject(new Error(jqXHR.responseJSON?.message || "Server Error"))
                });
            });

        // Inside generateCode function after successful AJAX
        if (data.success) {
            Elements.$resultContent.removeClass('f2e-hidden');
            Elements.$resultContainerActions.removeClass('f2e-hidden');
            Elements.$loading.addClass('f2e-hidden');
            
            // Set Preview as the default view after generation
            $('.f2e-tab-button').removeClass('active');
            $('.f2e-tab-button[data-tab="preview"]').addClass('active');
            $('#code-tab').addClass('hidden');
            $('#preview-tab').removeClass('hidden').addClass('active');

            const outputContent = data.content || 'No content generated.'; 
            F2E.State.lastGeneratedMetadata = { 
                title: data.title || '', 
                content: outputContent, 
                tags: data.tags || [], 
                image: data.image || '' 
            };

            Elements.$resultCode.text(outputContent);
            
            // Create the panel
            F2E.Metadata.createMetadataPanel(F2E.State.lastGeneratedMetadata);
            
            // CRITICAL: Ensure code container is hidden and metadata is ready but hidden for preview mode
            $('.f2e-code-container').addClass('f2e-hidden').hide();
            $('.f2e-metadata-panel').hide(); 

            F2E.Preview.updateLivePreview();
        }else {
                alert(data.message || "Processing failed.");
            }
        } catch (error) {
            console.error('Generation Error:', error);
            alert(error.message);
        } finally {
            F2E.Loader.clearLoader();
            F2E.Utils.resetGenerateButton();
            $('.f2e-tab-button').prop('disabled', false).removeClass('disabled'); 
        }
    }

    window.F2E.Generate = { generateCode };
    
})(jQuery);