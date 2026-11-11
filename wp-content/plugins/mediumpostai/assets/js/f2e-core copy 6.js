
/**
 * f2e-core.js
 * Initializes variables, creates the global F2E namespace, and runs the main setup functions.
 */

window.F2E = window.F2E || {};

(function($) {
    "use strict";

    if (typeof MPAI_vars === 'undefined') {
        console.error("MPAI_vars is not defined. Ensure it is localized properly.");
        return;
    }
    
    // --- DOM Elements ---
    const $generateButton = $('#f2e-generate-button');
    const $description = $('#f2e-description');
    const $description_yt = $('#f2e-description-yt');
    const $copyButton = $('#f2e-copy-button');
    const $downloadButton = $('#f2e-download-button');
    const $clearButton = $('#f2e-clear-button');
    const $resultContainerActions = $('.f2e-result-actions');
    const $resultCode = $('#f2e-result');
    const $loading = $('#f2e-loading');
    const $f2eCodeContainer = $('.f2e-code-container');
    const $resultContent = $('.f2e-result-content');
    const $previewButton = $('#f2e-preview-button');
    const $iterationOptions = $('.f2e-iteration-option');
    const $iterationsValue = $('#f2e-iterations-value');
    const $f2eClearConsoleButton = $('#f2e-clear-console-button');
    const $contentTypeSelect = $('#f2e-content-type');
    const i18n = MPAI_vars.i18n || {};
    const $siteURL = $('#f2e-site-url');

    // Toggle Elements
    const $inputModeToggle = $('#f2e-input-mode-toggle');
    const $textInputContainer = $('#f2e-text-input-container');
    const $youtubeInputContainer = $('#f2e-youtube-input-container');
    const $ytDescriptionToggleBtn = $('#f2e-toggle-yt-description');
    const $ytDescriptionContainer = $('#f2e-description-yt-container');

    // Advanced Modal Elements
    const $advancedSettingsButton = $('#f2e-advanced-settings-button');
    const $advancedModal = $('#f2e-advanced-modal');
    const $advancedModalCloseButtons = $advancedModal.find('.f2e-close-modal');
    const $targetKeyword = $('#f2e-target-keyword');
    const $audienceLevel = $('#f2e-audience-level');
    const $desiredTone = $('#f2e-desired-tone');
    const $language = $('#f2e-language');
    const $requiredLength = $('#f2e-required-length');
    const $callToAction = $('#f2e-call-to-action');

    const basePointsPerIteration = 100;
    
    // Global state
    let pollProgress = null;
    let lastProgress = 0;
    let loaderInterval = null;
    let currentProgress = 0;
    let targetProgress = 0;
    let lastGeneratedMetadata = {};

    // Expose elements and state
    window.F2E.Elements = {
        $generateButton, $description, $description_yt, $copyButton, $downloadButton, 
        $clearButton, $resultContainerActions, $resultCode, $loading, 
        $f2eCodeContainer, $resultContent, $previewButton, $iterationOptions, 
        $iterationsValue, $f2eClearConsoleButton, $contentTypeSelect, $siteURL,
        $inputModeToggle, $textInputContainer, $youtubeInputContainer,
        $advancedSettingsButton, $advancedModal, $targetKeyword, $audienceLevel, $desiredTone,
        $language, $requiredLength, $callToAction
    };
    window.F2E.Constants = { basePointsPerIteration, i18n };
    window.F2E.State = { pollProgress, lastProgress, loaderInterval, currentProgress, targetProgress, lastGeneratedMetadata };

    // --- Core Utility Functions ---
    window.F2E.Utils = {
        getIterations: function() {
            const activeOption = $('.f2e-iteration-option.active');
            return activeOption.length ? parseInt(activeOption.data('value')) : 2;
        },
        getContentType: function() {
            return $contentTypeSelect.val();
        },
        getContentTypeDisplayName: function() {
            return $contentTypeSelect.find('option:selected').text();
        },
        updateGenerateButtonPoints: function() {
            const currentIterations = window.F2E.Utils.getIterations();
            const totalPoints = basePointsPerIteration * currentIterations;
            const $pointsBadge = $generateButton.find('.f2e-points-badge');
            if ($pointsBadge.length) { $pointsBadge.text(`${totalPoints} Points`); }
        },
        updatePlaceholderBasedOnContentType: function() {
            const contentType = window.F2E.Utils.getContentType();
            if (contentType === 'devio') {
                $description.attr('placeholder', 'Generate a Dev.io post about web development...');
            } else {
                $description.attr('placeholder', 'Give a topic name or description or any text...');
            }
        },
        updateInputUI: function() {
            const currentCode = $resultCode.text().trim();
            const $generateButtonContent = $generateButton.find('.f2e-button-content');
            
            if (currentCode && currentCode !== 'No content generated yet.') {
                $description.attr('placeholder', `Update current ${window.F2E.Utils.getContentTypeDisplayName().toLowerCase()}...`);
                $generateButtonContent.find('span:first').text('Update');
            } else {
                window.F2E.Utils.updatePlaceholderBasedOnContentType();
                $generateButtonContent.find('span:first').text('Generate');
            }
            window.F2E.Utils.updateGenerateButtonPoints();
        },
        resetGenerateButton: function() {
            if (window.F2E.Loader && window.F2E.Loader.clearLoader) { window.F2E.Loader.clearLoader(); }
            $loading.addClass('f2e-hidden');
            $generateButton.prop('disabled', false).removeClass('disabled');
            const currentIterations = window.F2E.Utils.getIterations();
            const totalPoints = basePointsPerIteration * currentIterations;
            const buttonText = ($resultCode.text().trim() && $resultCode.text().trim() !== 'No content generated yet.') ? 'Refine' : 'Generate';
            
            $generateButton.html(`
                <span class="f2e-button-content">${buttonText} <span class="f2e-points-badge">${totalPoints} Points</span></span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="f2e-button-icon">
                    <path d="M13 6L19 12L13 18M5 6L11 12L5 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            `);
        },
        getFeaturedImageUrl: function(imageConcept) {
            if (!imageConcept) return '';
            if (imageConcept.startsWith('http') || imageConcept.startsWith('/')) return imageConcept;
            const placeholderText = imageConcept.substring(0, 50) + (imageConcept.length > 50 ? '...' : '');
            return 'https://via.placeholder.com/740x400?text=' + encodeURIComponent(placeholderText);
        },
    };

    // --- Initialization Functions ---
    
    function initInputModeToggle() {
        // Mode Switch Logic
        function toggleInputFields() {
            const isYoutube = $inputModeToggle.prop('checked');
            if (isYoutube) {
                $textInputContainer.addClass('f2e-hidden');
                $youtubeInputContainer.removeClass('f2e-hidden');
                $description.prop('disabled', true);
                $siteURL.prop('disabled', false).focus();
            } else {
                $textInputContainer.removeClass('f2e-hidden');
                $youtubeInputContainer.addClass('f2e-hidden');
                $description.prop('disabled', false).focus();
                $siteURL.prop('disabled', true);
            }
            window.F2E.Utils.updateInputUI();
        }

        $inputModeToggle.on('change', toggleInputFields);

        // Youtube Additional Instructions Slider Logic
        $ytDescriptionContainer.hide(); // Hide instructions by default
        $ytDescriptionToggleBtn.on('click', function() {
            $ytDescriptionContainer.slideToggle(300);
            $(this).toggleClass('active');
        });

        // Initialize state
        toggleInputFields();
    }

    function initContentTypeDropdown() {
        $contentTypeSelect.on('change', function() {
            window.F2E.Utils.updatePlaceholderBasedOnContentType();
            window.F2E.Utils.updateInputUI();
        });
        window.F2E.Utils.updatePlaceholderBasedOnContentType();
    }

    function initTabs() {
        $('.f2e-tabs-nav').on('click', '.f2e-tab-button', function() {
            const tabId = $(this).data('tab');
            
            // Tab UI toggle
            $('.f2e-tab-button').removeClass('active border-[var(--primary-color)] text-[var(--primary-color)]').addClass('text-[var(--text-gray-500)]');
            $(this).addClass('active border-[var(--primary-color)] text-[var(--primary-color)]').removeClass('text-[var(--text-gray-500)]');
            
            $('.f2e-tab-content').removeClass('active').addClass('hidden');
            $(`#${tabId}-tab`).addClass('active').removeClass('hidden');
    
            // NEW LOGIC: If clicking the first tab (details), hide code preview and show metadata
            if (tabId === 'code') {
                $('.f2e-code-container').addClass('f2e-hidden'); // Hide the dark code box
                $('.f2e-metadata-panel').removeClass('f2e-hidden').show(); // Show the details panel
            } else if (tabId === 'preview') {
                // When in preview, we usually want metadata hidden or at the bottom, 
                // but per your request to "remove code preview with details", we ensure metadata is visible in the details tab.
                if (window.F2E.Preview) { window.F2E.Preview.updateLivePreview(); }
            }
        });
    }

    function initIterationOptions() {
        $iterationOptions.on('click', function() {
            $iterationOptions.removeClass('active');
            $(this).addClass('active');
            $iterationsValue.text($(this).data('value'));
            window.F2E.Utils.updateInputUI();
        });
    }

    function initActions() {
        $generateButton.on('click', function(e) {
            e.preventDefault();
            if (window.F2E.Generate) window.F2E.Generate.generateCode();
        });

        if (window.F2E.Actions) {
            $clearButton.on('click', window.F2E.Actions.clearContent);
            $copyButton.on('click', window.F2E.Actions.copyContent);
            $downloadButton.on('click', window.F2E.Actions.downloadContent);
        }

        $advancedSettingsButton.on('click', () => $advancedModal.removeClass('hidden').addClass('flex'));
        $advancedModalCloseButtons.on('click', () => $advancedModal.removeClass('flex').addClass('hidden'));
        $advancedModal.on('click', (e) => { if (e.target === $advancedModal[0]) $advancedModal.removeClass('flex').addClass('hidden'); });
    }
    
    $(document).ready(function() {
        initContentTypeDropdown();
        initTabs(); 
        initIterationOptions();
        initInputModeToggle();
        initActions();
        window.F2E.Utils.updateInputUI();
    });

})(jQuery);

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

            if (data.success) {
                // UI Success State
                Elements.$resultContent.removeClass('f2e-hidden');
                Elements.$f2eCodeContainer.removeClass('f2e-hidden');
                Elements.$resultContainerActions.removeClass('f2e-hidden');
                Elements.$loading.addClass('f2e-hidden');
                $('#preview-tab').addClass('active').removeClass('hidden');

                const outputContent = data.content || 'No content generated.'; 
                F2E.State.lastGeneratedMetadata = { 
                    title: data.title || '', 
                    content: outputContent, 
                    tags: data.tags || [], 
                    image: data.image || '' 
                };

                Elements.$resultCode.text(outputContent);
                
                // Highlight and Preview
                F2E.Preview.loadPrism().then(() => {
                    if (Elements.$resultCode[0]) Prism.highlightElement(Elements.$resultCode[0]);
                });

                F2E.Metadata.createMetadataPanel(F2E.State.lastGeneratedMetadata);
                F2E.Preview.updateLivePreview();
                
            } else {
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