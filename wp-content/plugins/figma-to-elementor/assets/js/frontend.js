jQuery(document).ready(function($) {
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
    const $iterationStatus = $('#f2e-iteration-status');
    const $f2eClearConsoleButton = $('#f2e-clear-console-button');
    const i18n = f2e_vars.i18n || {};

    const basePointsPerIteration = 100;

    let pollProgress = null;
    let lastProgress = 0;
    let site_source_chk = false;
    let figma_source_chk = false;
    let source_msg = "";
    let outputType = "";
    let console_mode = "";
    let loaderInterval = null;
    let currentProgress = 0;
    let targetProgress = 0;

    // Store original button HTML with proper points badge
    const originalGenerateButtonHtml = `
        <span class="f2e-button-content">
            Generate
            <span class="f2e-points-badge">${basePointsPerIteration * getIterations()} Points</span>
        </span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="f2e-button-icon">
            <path d="M13 6L19 12L13 18M5 6L11 12L5 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    `;

    // Function to update placeholder and button text based on current code
    function updateInputUI() {
        const currentCode = $resultCode.text().trim();
        const $generateButtonContent = $generateButton.find('.f2e-button-content');
        $description.val("");
        if (currentCode && currentCode !== 'No code generated yet.') {
            $description.attr('placeholder', 'Update current code by providing instructions such as change text color');
            $generateButtonContent.find('span:first').text('Update');
        } else {
            $description.attr('placeholder', 'Generate a 3d logo for my IT consulting business...');
            $generateButtonContent.find('span:first').text('Generate');
        }
        
        // Always update points badge when UI updates
        updateGenerateButtonPoints();
    }

    /**
     * Displays a message in the unified console.
     */
    function showConsoleMessage(type, message, clearPrevious = false) {
        const timestamp = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        let messageClass = 'f2e-console-default';

        switch (type) {
            case 'error':
                messageClass = 'f2e-console-error';
                break;
            case 'success':
                messageClass = 'f2e-console-success';
                break;
            case 'info':
                messageClass = 'f2e-console-info';
                break;
        }

        const messageElement = $(`
            <div class="f2e-console-message ${messageClass}">
                <span class="f2e-console-timestamp">[${timestamp}]</span>
                ${message}
            </div>
        `);
    }

    $iterationOptions.on('click', function() {
        $iterationOptions.removeClass('active');
        $(this).addClass('active');
        const selectedValue = $(this).data('value');
        $iterationsValue.text(selectedValue);
        updateGenerateButtonPoints();
        updateInputUI(); // Also update button text when iterations change
    });

    function updateGenerateButtonPoints() {
        const currentIterations = getIterations();
        const totalPoints = basePointsPerIteration * currentIterations;
        const $pointsBadge = $generateButton.find('.f2e-points-badge');
        if ($pointsBadge.length) {
            $pointsBadge.text(`${totalPoints} Points`);
        }
    }

    function getIterations() {
        const activeOption = $('.f2e-iteration-option.active');
        return activeOption.length ? parseInt(activeOption.data('value')) : 2; // Default to 2 as per HTML
    }

    function updateIterationStatusMsg(current, total, source = '', msg = '') {
        if (typeof current === 'number' && typeof total === 'number') {
            // $iterationStatus.text(
            //     `${i18n.processing_iteration2 || 'Working on iteration'} ${current} ${i18n.of || 'of'} ${total}, please wait.`
            // );
        }
    }

    // New function to create and manage the loader
    function createLoader() {
        // Remove existing loader if present
        $('.f2e-progress-loader').remove();
        
        const loaderHTML = `
            <div class="f2e-progress-loader mt-6">
                <div class="f2e-loader-header flex justify-between items-center mb-3">
                    <span class="f2e-loader-label text-sm font-medium text-[var(--text-gray-700)]">Generating Progress</span>
                    <span class="f2e-loader-percentage text-sm font-semibold text-[var(--blue-600)]">0%</span>
                </div>
                <div class="f2e-progress-bar-container w-full bg-[var(--bg-gray-200)] rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="f2e-progress-bar h-full bg-gradient-to-r from-[var(--blue-500)] to-[var(--blue-600)] rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                </div>
                <div class="f2e-loader-steps mt-2 text-xs text-[var(--text-gray-500)]">
                    <span class="f2e-current-step">Initializing generation process...</span>
                </div>
            </div>
        `;
        
        $loading.append(loaderHTML);
    }

    function startLoader() {
        clearLoader();
        createLoader();
        
        const iterations = getIterations();
        const totalDuration = iterations * 65000; // minutes to milliseconds
        const updateInterval = 100; // Update every 100ms for smooth animation
        const steps = totalDuration / updateInterval;
        const increment = 100 / steps;
        
        currentProgress = 0;
        targetProgress = 0;
        
        const $progressBar = $('.f2e-progress-bar');
        const $percentage = $('.f2e-loader-percentage');
        const $currentStep = $('.f2e-current-step');
        
        loaderInterval = setInterval(() => {
            if (currentProgress < targetProgress) {
                currentProgress += increment;
                if (currentProgress > targetProgress) {
                    currentProgress = targetProgress;
                }
                
                const progressPercent = Math.min(100, Math.round(currentProgress));
                $progressBar.css('width', `${progressPercent}%`);
                $percentage.text(`${progressPercent}%`);
                
                // Update step text based on progress
                if (progressPercent < 25) {
                    $currentStep.text('Processing your description and requirements...');
                } else if (progressPercent < 50) {
                    $currentStep.text('Generating 3D component structure...');
                } else if (progressPercent < 75) {
                    $currentStep.text('Applying animations and visual effects...');
                } else {
                    $currentStep.text('Finalizing code and preparing output...');
                }
                
                // Stop when we reach 100%
                if (progressPercent >= 100) {
                    clearInterval(loaderInterval);
                    $currentStep.text('Generation complete! Finalizing output...');
                }
            }
        }, updateInterval);
        
        // Start the progress
        targetProgress = 100;
    }

    function clearLoader() {
        if (loaderInterval) {
            clearInterval(loaderInterval);
            loaderInterval = null;
        }
        $('.f2e-progress-loader').remove();
        currentProgress = 0;
        targetProgress = 0;
    }

    async function generateCode() {
        // Disable button immediately
        $generateButton.prop('disabled', true).addClass('disabled');
        
        $resultContent.addClass('f2e-hidden');
        $f2eCodeContainer.addClass('f2e-hidden');
        $loading.removeClass('f2e-hidden');
    
        $resultContainerActions.addClass('f2e-hidden');
        $copyButton.addClass('f2e-hidden');
        $downloadButton.addClass('f2e-hidden');
        $previewButton.addClass('f2e-hidden');
        $clearButton.addClass('f2e-hidden');
    
        // Show loading state with spinner
        $generateButton.html(`
            <span class="f2e-spinner"></span>
            ${i18n.generating || 'Generating...'}
        `);
    
        outputType = 'html';
        const iterations = getIterations();
        const description = $description.val().trim();

        // Validate description
        if (!description) {
            alert(i18n.please_enter_description || 'Please enter a description');
            resetGenerateButton();
            return;
        }

        // Start the loader
        startLoader();

        const totalPoints = basePointsPerIteration * iterations;
        const currentCode = $resultCode.text().trim();

        const formData = new FormData();
        formData.append('output_type', outputType);
        formData.append('iterations', iterations);
        formData.append('description', description);
        formData.append('current_code', currentCode);
        
        const iterationLabel = iterations > 1 ? 'iterations' : 'iteration';
  
        try {
            const data = await new Promise((resolve, reject) => {
                procesProgress();
                $.ajax({
                    url: f2e_vars.ajax_url,
                    method: 'POST',
                    headers: { 'X-WP-Nonce': f2e_vars.nonce },
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#f2e-iteration-heading').text(i18n.generating_your_code || 'Generating your code...');
                        // $('#f2e-iteration-status').text(i18n.generating_iteration_status || 'Processing generation request. This may take a few moments depending on complexity.');
                    },
                    success: function(response) {
                        clearInterval(pollProgress);
                        resolve(response);
                    },
                    error: function(jqXHR) {
                        clearInterval(pollProgress);
                        const serverMessage = jqXHR.responseJSON?.message || jqXHR.statusText;
                        reject(new Error(serverMessage ?? i18n?.unknown_error ?? "Unknown server error. Please try again."));
                    },
                    complete: function() {
                        clearInterval(pollProgress);
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

                let outputContent = typeof data.result === 'string' ? data.result : JSON.stringify(data.result, null, 2);
                $resultCode.text(outputContent);
    
                try {
                    if (typeof Prism === 'undefined') {
                        await loadPrism();
                    }
                    const codeEl = $resultCode[0];
                    if (codeEl) {
                        Prism.highlightElement(codeEl);
                    }
                } catch (prismError) {
                    console.warn('Prism.js error:', prismError);
                }
    
                if (data.preview_id) {
                    $previewButton.removeClass('f2e-hidden').off('click').on('click', function() {
                        $(this).html('<span>' + (f2e_vars.i18n.preparing_preview || 'Preparing preview...') + '</span>');
                        window.open(f2e_vars.preview_page_url + '?preview_id=' + encodeURIComponent(data.preview_id), '_blank');
                        setTimeout(() => {
                            $(this).html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> ' + (f2e_vars.i18n.preview || 'Preview'));
                        }, 1000);
                    });
                } else {
                    $previewButton.addClass('f2e-hidden');
                }
                
                // Update UI after generating code
                updateInputUI();
            } else {
                const errorMessage = data.message ?? i18n?.unknown_error ?? "Unknown error occurred. Please try again.";
                alert(errorMessage);
            }
        } catch (error) {
            console.error('Error in generateCode:', error);
            let errorMessage = error.message ?? i18n?.unknown_error ?? i18n?.try_again ?? "An unknown error occurred. Please try again.";
    
            if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                errorMessage = i18n?.network_error ?? "Network error. Please check your connection.";
            }

            alert(errorMessage);
        } finally {
            clearLoader();
            resetGenerateButton();
        }
    }

    function resetGenerateButton() {
        $loading.addClass('f2e-hidden');
        $generateButton.prop('disabled', false).removeClass('disabled');
        
        // Restore original button content with updated points
        const currentIterations = getIterations();
        const totalPoints = basePointsPerIteration * currentIterations;
        const buttonText = $resultCode.text().trim() ? 'Update' : 'Generate';
        
        $generateButton.html(`
            <span class="f2e-button-content">
                ${buttonText}
                <span class="f2e-points-badge">${totalPoints} Points</span>
            </span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="f2e-button-icon">
                <path d="M13 6L19 12L13 18M5 6L11 12L5 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        `);
        
        $('#f2e-iteration-heading').text(i18n.generating_your_code || 'Generating your code...');
    }

    function procesProgress() {
        if (pollProgress) {
            clearInterval(pollProgress);
        }
        
        const iterations = getIterations();
        let retryCount = 0;
        const maxRetries = 20;
        
        pollProgress = setInterval(async () => {
            try {
                const response = await fetch(
                    f2e_vars.ajax_url_progress,
                    {
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': f2e_vars.nonce,
                            'Accept': 'application/json'
                        }
                    }
                );
    
                if (!response.ok) {
                    throw new Error("Progress check failed");
                }
    
                const progress = await response.json();
                let current_source = progress.source;
    
                if (progress && progress.current !== undefined && progress.total !== undefined) {
                    // updateIterationStatusMsg(progress.current, iterations, current_source);
                    retryCount = 0;
                    if (lastProgress !== progress.current) {
                        lastProgress = progress.current;
                    }
                    if (progress.current >= iterations) {
                        clearInterval(pollProgress);
                        pollProgress = null;
                    }
                }
            } catch (e) {
                retryCount++;
                if (retryCount >= maxRetries) {
                    console.log('Max retries reached for progress polling');
                    clearInterval(pollProgress);
                    pollProgress = null;
                }
            }
        }, 5000);
    }

    $generateButton.on('click', async function(e) {
        e.preventDefault();
        await generateCode();
    });

    $clearButton.on('click', function() {
        const confirmClear = confirm(i18n.confirm_clear_generate || "Do you want to clear code preview?");
        if (confirmClear) {
            $resultCode.empty();
            $description.val('');
            $resultContent.addClass('f2e-hidden');
            $resultContainerActions.addClass('f2e-hidden');
            $copyButton.addClass('f2e-hidden');
            $downloadButton.addClass('f2e-hidden');
            $previewButton.addClass('f2e-hidden');
            $clearButton.addClass('f2e-hidden');
            
            // Update UI after clearing code
            updateInputUI();
        }
    });

    $f2eClearConsoleButton.on('click', function() {
        // Console clearing logic would go here
    });

    $copyButton.on('click', function() {
        const text = $resultCode.text();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text)
                .then(() => showCopiedMessage($copyButton))
                .catch(err => {
                    console.log('Failed to copy:', err);
                    fallbackCopyToClipboard(text);
                });
        } else {
            fallbackCopyToClipboard(text);
        }
    });

    $downloadButton.on('click', function() {
        const text = $resultCode.text();
        const outputType = 'html';
        const extension = outputType === 'html' ? 'html' : 'json';
        const output = outputType === 'html' ? 'HTML' : 'Elementor';
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `3DBadgeMakerAI-${output}-${timestamp}.${extension}`;

        const blob = new Blob([text], { type: outputType === 'html' ? 'text/html' : 'application/json' });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showDownloadMessage($downloadButton);
    });

    function fallbackCopyToClipboard(text) {
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        showCopiedMessage($copyButton);
    }

    function showCopiedMessage($button) {
        const originalHTML = $button.html();
        const tempHTML = `<span>${i18n.copied || 'Copied!'}</span>`;
        $button.html(tempHTML);
        setTimeout(() => {
            $button.html(originalHTML);
        }, 2000);
    }

    function showDownloadMessage($button) {
        const originalHTML = $button.html();
        const tempHTML = `<span>${i18n.downloading || 'Downloading...'}</span>`;
        $button.html(tempHTML);
        setTimeout(() => {
            $button.html(originalHTML);
        }, 2000);
    }s

    function loadPrism() {
        return new Promise((resolve) => {
            if (typeof Prism !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js';
            script.className = 'f2e-prism-js';
            script.onload = function() {
                const jsonScript = document.createElement('script');
                jsonScript.src = 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-json.min.js';
                jsonScript.onload = resolve;
                document.body.appendChild(jsonScript);
            };
            document.body.appendChild(script);
        });
    }

    // Initialize UI on page load
    updateInputUI();
    updateGenerateButtonPoints();
});