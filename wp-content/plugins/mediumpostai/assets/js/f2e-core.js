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
        $language, $requiredLength, $callToAction, $ytDescriptionToggleBtn, $ytDescriptionContainer
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
            const isRefining = ($resultCode.text().trim() && $resultCode.text().trim() !== 'No content generated yet.');
            const buttonText = isRefining ? 'Refine' : 'Generate';
            
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
        function toggleInputFields() {
            const isYoutube = $inputModeToggle.prop('checked');
            if (isYoutube) {
                $textInputContainer.addClass('f2e-hidden');
                $youtubeInputContainer.removeClass('f2e-hidden');
                $description.prop('disabled', true);
                $siteURL.prop('disabled', false);
            } else {
                $textInputContainer.removeClass('f2e-hidden');
                $youtubeInputContainer.addClass('f2e-hidden');
                $description.prop('disabled', false);
                $siteURL.prop('disabled', true);
            }
            window.F2E.Utils.updateInputUI();
        }

        $inputModeToggle.on('change', toggleInputFields);

        // Specific Instructions Toggle (Fixed)
        $ytDescriptionToggleBtn.on('click', function(e) {
            e.preventDefault();
            $ytDescriptionContainer.toggleClass('f2e-hidden');
            const isExpanded = !$ytDescriptionContainer.hasClass('f2e-hidden');
            $(this).attr('aria-expanded', isExpanded);
            
            // Toggle icon rotation or style if needed
            $(this).toggleClass('active');
        });

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

            // Logic to show Details and remove Code Preview
            if (tabId === 'code') {
                // Force hide the dark code container
                $('.f2e-code-container').addClass('f2e-hidden').hide(); 
                // Show the details/metadata panel
                $('.f2e-metadata-panel').removeClass('f2e-hidden').fadeIn(200);
            } else if (tabId === 'preview') {
                // Hide details panel when looking at the live preview
                $('.f2e-metadata-panel').hide();
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
        // Main Generate
        $generateButton.on('click', function(e) {
            e.preventDefault();
            if (window.F2E.Generate) window.F2E.Generate.generateCode();
        });

        // Advanced Settings Modal (Fixed)
        $advancedSettingsButton.on('click', function(e) {
            e.preventDefault();
            $advancedModal.removeClass('f2e-hidden').addClass('active flex');
        });

        // Close Modal via X or Done buttons
        $(document).on('click', '.f2e-close-modal', function(e) {
            e.preventDefault();
            $advancedModal.addClass('f2e-hidden').removeClass('active flex');
        });

        // Close Modal via Background click
        $advancedModal.on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('f2e-hidden').removeClass('active flex');
            }
        });

        // Standard Actions
        if (window.F2E.Actions) {
            $clearButton.on('click', window.F2E.Actions.clearContent);
            $copyButton.on('click', window.F2E.Actions.copyContent);
            $downloadButton.on('click', window.F2E.Actions.downloadContent);
        }
    }
    
    $(document).ready(function() {
        initContentTypeDropdown();
        initTabs(); 
        initIterationOptions();
        initInputModeToggle();
        initActions();
        window.F2E.Utils.updateInputUI();
        $('.f2e-tab-button[data-tab="preview"]').trigger('click');
    });

})(jQuery);