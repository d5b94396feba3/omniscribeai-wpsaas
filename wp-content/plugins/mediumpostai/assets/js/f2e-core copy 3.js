/**
 * f2e-core.js
 * Initializes variables, creates the global F2E namespace, and runs the main setup functions.
 */

// Create a namespace for the frontend functions
window.F2E = window.F2E || {};

(function($) {
    "use strict";

    // --- DOM Elements & Global State ---
    if (typeof MPAI_vars === 'undefined') {
        console.error("MPAI_vars is not defined. Ensure it is localized using wp_localize_script('f2e-core', 'MPAI_vars', ...)");
        return;
    }
    
    const $generateButton = $('#f2e-generate-button');
    const $description = $('#f2e-description');
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

    // NEW ADVANCED FIELDS AND MODAL ELEMENTS
    const $advancedSettingsButton = $('#f2e-advanced-settings-button');
    const $advancedModal = $('#f2e-advanced-modal');
    const $advancedModalCloseButtons = $advancedModal.find('.f2e-close-modal');
    const $targetKeyword = $('#f2e-target-keyword');
    const $audienceLevel = $('#f2e-audience-level');
    const $desiredTone = $('#f2e-desired-tone');
    const $language = $('#f2e-language'); // NEW
    const $requiredLength = $('#f2e-required-length'); // NEW
    const $callToAction = $('#f2e-call-to-action'); // NEW
    // END NEW

    const basePointsPerIteration = 100;
    
    // Global state
    let pollProgress = null;
    let lastProgress = 0;
    let loaderInterval = null;
    let currentProgress = 0;
    let targetProgress = 0;
    let lastGeneratedMetadata = {};

    // Expose utility functions/variables via the F2E namespace for other modules
    window.F2E.Elements = {
        $generateButton, $description, $copyButton, $downloadButton, 
        $clearButton, $resultContainerActions, $resultCode, $loading, 
        $f2eCodeContainer, $resultContent, $previewButton, $iterationOptions, 
        $iterationsValue, $f2eClearConsoleButton, $contentTypeSelect, $siteURL,
        // NEW ADVANCED FIELDS
        $advancedSettingsButton, $advancedModal, $targetKeyword, $audienceLevel, $desiredTone,
        $language, $requiredLength, $callToAction // ADDED HERE
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
            if ($pointsBadge.length) {
                $pointsBadge.text(`${totalPoints} Points`);
            }
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
                $description.attr('placeholder', `Update current ${window.F2E.Utils.getContentTypeDisplayName().toLowerCase()} by providing instructions such as change text color`);
                $generateButtonContent.find('span:first').text('Update');
            } else {
                window.F2E.Utils.updatePlaceholderBasedOnContentType();
                $generateButtonContent.find('span:first').text('Generate');
            }
            
            window.F2E.Utils.updateGenerateButtonPoints();
        },
        resetGenerateButton: function() {
            if (window.F2E.Loader && window.F2E.Loader.clearLoader) {
                window.F2E.Loader.clearLoader();
            }
            
            $loading.addClass('f2e-hidden');
            $generateButton.prop('disabled', false).removeClass('disabled');
            
            const currentIterations = window.F2E.Utils.getIterations();
            const totalPoints = basePointsPerIteration * currentIterations;
            const buttonText = $resultCode.text().trim() && $resultCode.text().trim() !== 'No content generated yet.' ? 'Refine' : 'Generate';
            
            $generateButton.html(`
                <span class="f2e-button-content">
                    ${buttonText}
                    <span class="f2e-points-badge">${totalPoints} Points</span>
                </span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="f2e-button-icon">
                    <path d="M13 6L19 12L13 18M5 6L11 12L5 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            `);
            
            $('#f2e-iteration-heading').text(`Generating your ${window.F2E.Utils.getContentTypeDisplayName().toLowerCase()}...`);
        },
        getFeaturedImageUrl: function(imageConcept) {
            if (!imageConcept) return '';
            // If the concept is a valid URL, use it directly.
            if (imageConcept.startsWith('http') || imageConcept.startsWith('/')) {
                return imageConcept;
            }
            // Fallback to a placeholder service using the concept text for a visual representation.
            const placeholderText = imageConcept.substring(0, 50) + (imageConcept.length > 50 ? '...' : '');
            return 'https://via.placeholder.com/740x400?text=' + encodeURIComponent(placeholderText);
        },
    };

    // --- Initialization Functions ---
    function initContentTypeDropdown() {
        $contentTypeSelect.on('change', function() {
            window.F2E.Utils.updatePlaceholderBasedOnContentType();
            window.F2E.Utils.updateInputUI();
        });
        window.F2E.Utils.updatePlaceholderBasedOnContentType();
    }

    function initTabs() {
        // Ensure initial state: Code is active, Preview is hidden
        $('.f2e-tab-button[data-tab="code"]').addClass('active border-[var(--primary-color)] text-[var(--primary-color)]').removeClass('text-[var(--text-gray-500)]');
        $('.f2e-tab-button[data-tab="preview"]').removeClass('active border-[var(--primary-color)] text-[var(--primary-color)]').addClass('text-[var(--text-gray-500)]');
        $('#code-tab').addClass('active').removeClass('hidden');
        $('#preview-tab').removeClass('active').addClass('hidden');

        $('.f2e-tabs-nav').on('click', '.f2e-tab-button', function() {
            const tabId = $(this).data('tab');
            
            // 1. Update button styling (active/inactive)
            $('.f2e-tab-button').removeClass('active border-[var(--primary-color)] text-[var(--primary-color)]')
                            .addClass('text-[var(--text-gray-500)]');
            $(this).addClass('active border-[var(--primary-color)] text-[var(--primary-color)]')
                .removeClass('text-[var(--text-gray-500)]');
            
            // 2. Update content visibility (active/hidden) - THIS IS THE LOGIC THAT HIDES/SHOWS THE TABS
            $('.f2e-tab-content').removeClass('active').addClass('hidden');
            $(`#${tabId}-tab`).addClass('active').removeClass('hidden');
            
            // 3. Trigger preview update if switching to preview tab
            if (tabId === 'preview' && window.F2E.Preview && window.F2E.Preview.updateLivePreview) {
                window.F2E.Preview.updateLivePreview();
            }
        });
    }



    function initIterationOptions() {
        $iterationOptions.on('click', function() {
            $iterationOptions.removeClass('active');
            $(this).addClass('active');
            const selectedValue = $(this).data('value');
            $iterationsValue.text(selectedValue);
            window.F2E.Utils.updateGenerateButtonPoints();
            window.F2E.Utils.updateInputUI();
        });
    }

    function initActions() {
        if (window.F2E.Generate && window.F2E.Generate.generateCode) {
            $generateButton.on('click', async function(e) {
                e.preventDefault();
                await window.F2E.Generate.generateCode();
            });
        }

        if (window.F2E.Actions) {
            $clearButton.on('click', window.F2E.Actions.clearContent);
            $f2eClearConsoleButton.on('click', window.F2E.Actions.clearConsole);
            $copyButton.on('click', window.F2E.Actions.copyContent);
            $downloadButton.on('click', window.F2E.Actions.downloadContent);
        }
        
        if (window.F2E.Preview && window.F2E.Preview.updateLivePreview) {
            $('#f2e-refresh-preview').on('click', window.F2E.Preview.updateLivePreview);
        }

        // NEW: Advanced Modal Actions
        $advancedSettingsButton.on('click', function() {
            $advancedModal.removeClass('hidden').addClass('flex');
        });

        $advancedModalCloseButtons.on('click', function() {
            $advancedModal.removeClass('flex').addClass('hidden');
        });

        $advancedModal.on('click', function(e) {
            if (e.target === this) {
                $advancedModal.removeClass('flex').addClass('hidden');
            }
        });
        
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $advancedModal.hasClass('flex')) {
                $advancedModal.removeClass('flex').addClass('hidden');
            }
        });
        // END NEW
        
        // NEW: Ensure Image Action Handlers are attached after everything is ready/loaded
        if (window.F2E.ImageActions && window.F2E.ImageActions.attachImageActionHandlers) {
             window.F2E.ImageActions.attachImageActionHandlers();
        }
    }
    
    // --- Document Ready ---
    $(document).ready(function() {
        initContentTypeDropdown();
        initTabs(); 
        initIterationOptions();
        window.F2E.Utils.updateInputUI();
        window.F2E.Utils.updateGenerateButtonPoints();
        initActions();
    });

})(jQuery);