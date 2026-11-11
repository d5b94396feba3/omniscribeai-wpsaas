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
        $iterationsValue, $f2eClearConsoleButton, $contentTypeSelect, $siteURL
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
                $description.attr('placeholder', 'Generate a Medium post about ai automation...');
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
            const buttonText = $resultCode.text().trim() && $resultCode.text().trim() !== 'No content generated yet.' ? 'Update' : 'Generate';
            
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
        const $previewTabButton = $('.f2e-tab-button[data-tab="preview"]');
        const $codeTabButton = $('.f2e-tab-button[data-tab="code"]');
        $codeTabButton.removeClass('active border-[var(--primary-color)] text-[var(--primary-color)]')
                      .addClass('text-[var(--text-gray-500)]');
        $previewTabButton.addClass('active border-[var(--primary-color)] text-[var(--primary-color)]')
                         .removeClass('text-[var(--text-gray-500)]');
        $('#code-tab').removeClass('active').addClass('hidden');
        $('#preview-tab').addClass('active').removeClass('hidden');

        $('.f2e-tab-button').on('click', function() {
            const tabId = $(this).data('tab');
            $('.f2e-tab-button').removeClass('active border-[var(--primary-color)] text-[var(--primary-color)]')
                            .addClass('text-[var(--text-gray-500)]');
            $(this).addClass('active border-[var(--primary-color)] text-[var(--primary-color)]')
                .removeClass('text-[var(--text-gray-500)]');
            
            $('.f2e-tab-content').removeClass('active').addClass('hidden');
            $(`#${tabId}-tab`).addClass('active').removeClass('hidden');
            
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