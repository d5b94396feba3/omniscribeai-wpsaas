/**
 * f2e-generate.js
 * Handles the main code generation logic and progress polling.
 * Requires: f2e-core.js (for F2E.Elements, F2E.Utils, F2E.State, F2E.Constants)
 * Requires: f2e-ui-components.js (for F2E.Loader, F2E.Preview, F2E.Metadata)
 */

window.F2E = window.F2E || {};

(function($) {
    "use strict";
    
    // Get references from Core
    const Elements = window.F2E.Elements || {};
    const $generateButton = Elements.$generateButton;
    const $description = Elements.$description;
    const $resultCode = Elements.$resultCode;
    const $loading = Elements.$loading;
    const $resultContainerActions = Elements.$resultContainerActions;
    const $copyButton = Elements.$copyButton;
    const $downloadButton = Elements.$downloadButton;
    const $clearButton = Elements.$clearButton;
    const $previewButton = Elements.$previewButton;
    const $f2eCodeContainer = Elements.$f2eCodeContainer;
    const $resultContent = Elements.$resultContent;
    const $siteURL = Elements.$siteURL; // <--- ADDED LINE

    // NEW ADVANCED FIELDS REFERENCES
    const $targetKeyword = Elements.$targetKeyword;
    const $audienceLevel = Elements.$audienceLevel;
    const $desiredTone = Elements.$desiredTone;
    const $language = Elements.$language; // NEW
    const $requiredLength = Elements.$requiredLength; // NEW
    const $callToAction = Elements.$callToAction; // NEW
    // END NEW
    
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
    
                if (!response.ok) {
                    throw new Error("Progress check failed");
                }
    
                const progress = await response.json();
    
                if (progress && progress.current !== undefined && progress.total !== undefined) {
                    retryCount = 0;
                    if (F2E.State.lastProgress !== progress.current) {
                        F2E.State.lastProgress = progress.current;
                    }
                    if (progress.current >= iterations) {
                        clearInterval(F2E.State.pollProgress);
                        F2E.State.pollProgress = null;
                    }
                }
            } catch (e) {
                retryCount++;
                if (retryCount >= maxRetries) {
                    console.log('Max retries reached for progress polling');
                    clearInterval(F2E.State.pollProgress);
                    F2E.State.pollProgress = null;
                }
            }
        }, 5000);
    }

    // Helper function for URL validation - place this at the top of your file
    function isValidUrl(string) {
        try {
            const url = new URL(string);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (_) {
            return false;
        }
    }
    
    async function generateCode() {

        $generateButton.prop('disabled', true).addClass('disabled');
        $resultContent.addClass('f2e-hidden');
        $f2eCodeContainer.addClass('f2e-hidden');
        $loading.removeClass('f2e-hidden');
        $resultContainerActions.addClass('f2e-hidden');
        $copyButton.addClass('f2e-hidden');
        $downloadButton.addClass('f2e-hidden');
        $previewButton.addClass('f2e-hidden');
        $clearButton.addClass('f2e-hidden');
        $('.f2e-metadata-panel').remove();
        
        $('.f2e-tabs-nav .f2e-tab-button').prop('disabled', true).addClass('disabled'); 
    
        $('.f2e-tab-button').removeClass('active border-[var(--primary-color)] text-[var(--primary-color)]').addClass('text-[var(--text-gray-500)]');
        $('.f2e-tab-button[data-tab="preview"]').addClass('active border-[var(--primary-color)] text-[var(--primary-color)]').removeClass('text-[var(--text-gray-500)]');
        $('#code-tab').removeClass('active').addClass('hidden');
        $('#preview-tab').removeClass('active').addClass('hidden');
    
        F2E.State.lastGeneratedMetadata = {};
    
        $generateButton.html(`<span class="f2e-spinner"></span>${i18n.generating || 'Generating...'}`);
    
        const iterations = F2E.Utils.getIterations();
        const description = $description.val().trim();
        const contentType = F2E.Utils.getContentType();
    
        // NEW: Get Advanced Settings Values
        const targetKeyword = $targetKeyword.val().trim();
        const audienceLevel = $audienceLevel.val().trim();
        const desiredTone = $desiredTone.val().trim();
        const language = $language.val().trim(); // NEW
        const requiredLength = $requiredLength.val().trim(); // NEW
        const callToAction = $callToAction.val().trim(); // NEW
        // END NEW
        
        if (!description) {
            alert(i18n.please_enter_description || 'Please enter a description');
            F2E.Utils.resetGenerateButton();
            
            $('.f2e-tabs-nav .f2e-tab-button').prop('disabled', false).removeClass('disabled'); 
            return;
        }
    
        F2E.Loader.startLoader();
    
        const currentCode = $resultCode.text().trim();
        const $siteURLValue = $siteURL.val().trim();
    
        if ($siteURLValue && !isValidUrl($siteURLValue)) {
            alert(i18n.please_enter_description || "Please enter a valid website URL (e.g., https://www.example.com)");
            resetGenerateButton();
            
            $('.f2e-tabs-nav .f2e-tab-button').prop('disabled', false).removeClass('disabled'); 
            return;
        }
    
        const formData = new FormData();
        formData.append('output_type', 'html'); 
        formData.append('iterations', iterations);
        formData.append('description', description);
        formData.append('current_code', currentCode);
        formData.append('content_type', contentType);
        formData.append('siteURL', $siteURLValue);

        // NEW: Append Advanced Settings to formData
        formData.append('target_keyword', targetKeyword);
        formData.append('audience_level', audienceLevel);
        formData.append('desired_tone', desiredTone);
        formData.append('language', language); // NEW
        formData.append('required_length', requiredLength); // NEW
        formData.append('call_to_action', callToAction); // NEW
        // END NEW
    
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
                    success: function(response) {
                        clearInterval(F2E.State.pollProgress);
                        resolve(response); 
                    },
                    error: function(jqXHR) {
                        clearInterval(F2E.State.pollProgress);
                        const serverMessage = jqXHR.responseJSON?.message || jqXHR.statusText; 
                        reject(new Error(serverMessage ?? i18n?.unknown_error ?? "Unknown server error. Please try again."));
                    },
                    complete: function() {
                        clearInterval(F2E.State.pollProgress);
                    }
                });
            });
    
            if (data.success) {
                $resultContent.removeClass('f2e-hidden');
                $f2eCodeContainer.removeClass('f2e-hidden');
                $resultContainerActions.removeClass('f2e-hidden');
                $copyButton.removeClass('f2e-hidden');
                $downloadButton.removeClass('f2e-hidden');
                $clearButton.removeClass('f2e-hidden');
                $loading.addClass('f2e-hidden');
    
                $('#preview-tab').addClass('active').removeClass('hidden');
    
                const outputContent = data.content || 'No content generated.'; 
                const title = data.title || '';
                const tags = data.tags || [];
                const image = data.image || '';
                 
                const extractedData = { title, content: outputContent, tags, image };
                F2E.State.lastGeneratedMetadata = extractedData;
    
                $resultCode.text(outputContent);
                F2E.Preview.loadPrism().then(() => {
                    if ($resultCode[0]) {
                         Prism.highlightElement($resultCode[0]);
                    }
                }).catch(e => console.warn('Prism.js error:', e));
    
                F2E.Metadata.createMetadataPanel(extractedData);
    
                if (data.preview_id) {
                    $previewButton.removeClass('f2e-hidden').off('click').on('click', function() {
                         $(this).html('<span>' + (i18n.preparing_preview || 'Preparing preview...') + '</span>');
                        window.open(MPAI_vars.preview_page_url + '?preview_id=' + encodeURIComponent(data.preview_id), '_blank');
                        setTimeout(() => {
                            $(this).html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> ' + (i18n.preview || 'Preview'));
                        }, 1000);
                    });
                } else {
                    $previewButton.addClass('f2e-hidden');
                }
                 
                setTimeout(() => {
                    F2E.Preview.updateLivePreview();
                    F2E.Utils.updateInputUI();
                }, 100);
            } else {
                const errorMessage = data.message ?? i18n?.unknown_error ?? "Unknown error occurred. Please try again.";
                alert(errorMessage);
            }
        } catch (error) {
            console.error('Error in generateCode:', error);
            let errorMessage = error.message ?? i18n?.unknown_error ?? i18n?.try_again ?? "An unknown error occurred. Please try again.";
            alert(errorMessage);
        } finally {
            F2E.Loader.clearLoader();
            F2E.Utils.resetGenerateButton();
            
            $('.f2e-tabs-nav .f2e-tab-button').prop('disabled', false).removeClass('disabled'); 
        }
    }

    // Expose functions to the global namespace
    window.F2E.Generate = {
        generateCode: generateCode,
    };
    
})(jQuery);