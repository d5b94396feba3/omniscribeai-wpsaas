/**
 * f2e-image.js
 * Handles the featured image refine and regenerate functionality.
 * Requires: f2e-core.js (for F2E.Elements, F2E.Utils, F2E.State, F2E.Constants)
 */
window.F2E = window.F2E || {};

(function($) {
    "use strict";

    // Get references from Core
    const Elements = window.F2E.Elements || {};
    const $loading = Elements.$loading;
    const { i18n } = F2E.Constants || {};
    
    // Internal state for Image operations
    let isImageGenerating = false;
    
    // NEW: Loader state variables for image generation
    let imageLoaderInterval = null;
    let imageCurrentProgress = 0;
    const IMAGE_GENERATION_DURATION = 60000; // 60 seconds in milliseconds
    const IMAGE_UPDATE_INTERVAL = 500; // Update progress every 0.5 seconds

    // --- Image Modal UI ---
    function initImageRefineModal() {
        if ($('#f2e-image-refine-modal').length === 0) {
            const modalHTML = `
            <div id="f2e-image-refine-modal" class="f2e-image-modal fixed inset-0 z-[1000] bg-black bg-opacity-75 hidden items-center justify-center p-4 transition-opacity duration-300" aria-modal="true" role="dialog" tabindex="-1">
                <div class="f2e-modal-content bg-[var(--bg-white)] rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col transform transition-transform duration-300 scale-95" style="z-index: 1001;">
                    <div class="f2e-modal-header flex justify-between items-center p-4 border-b border-[var(--border-gray-200)]">
                        <h5 class="text-xl font-semibold text-[var(--text-gray-800)]">Refine Image Concept</h5>
                        <button type="button" class="f2e-close-modal text-[var(--text-gray-500)] hover:text-[var(--text-gray-800)] transition-colors" aria-label="Close modal">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6L18 18"/></svg>
                        </button>
                    </div>
                    <div class="f2e-modal-body p-5 flex-1 overflow-y-auto bg-white space-y-4">
                        
                        <div class="f2e-modal-image-preview bg-slate-50 p-3 rounded-lg border border-slate-200 shadow-sm">
                            <label class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-2">Current Image Selection</label>
                            <div class="flex gap-4 items-center">
                                <img id="f2e-refine-image-preview-display" src="" alt="Refine Preview" class="w-24 h-24 object-cover rounded-md border border-slate-300 shadow-sm">
                                <div class="flex-1">
                                    <p class="text-xs text-slate-500 leading-tight italic">AI will use this image and your instructions below to generate a new variation.</p>
                                </div>
                            </div>
                        </div>

                        <div class="f2e-input-group">
                            <label for="f2e-image-refine-prompt" class="block text-sm font-bold text-slate-700 mb-2">
                                Refinement Instructions
                            </label>
                            <textarea id="f2e-image-refine-prompt" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-sm" rows="3" placeholder="e.g., 'Change the style to watercolor', 'Make it brighter', 'Add a robotic hand'..."></textarea>
                        </div>
                        
                        <div class="p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                            <span class="block text-[10px] uppercase font-bold text-indigo-400 mb-1">Current Base Concept</span>
                            <span id="f2e-current-concept-display" class="text-xs text-indigo-900 font-medium"></span>
                        </div>
                    </div>
                    <div class="f2e-modal-footer flex justify-end p-4 border-t border-[var(--border-gray-200)] bg-white">
                        <button id="f2e-submit-refine-button" class="f2e-button f2e-button-primary py-2.5 px-6 text-sm font-bold rounded-xl flex items-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-95" aria-label="Generate Refined Image">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Refine & Generate
                        </button>
                    </div>
                </div>
            </div>
        `;
            $('body').append(modalHTML);
        }

        const $modal = $('#f2e-image-refine-modal');
        $modal.on('click', '.f2e-close-modal', closeRefineModal);
        $modal.on('click', function(e) {
            if (e.target === this) {
                closeRefineModal();
            }
        });
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('flex')) {
                closeRefineModal();
            }
        });

        $('#f2e-submit-refine-button').on('click', handleImageRefine);
    }

    // function openRefineModal(currentConcept) {
    //     if (isImageGenerating) return;
    //     $('#f2e-current-concept-display').text(currentConcept);
    //     $('#f2e-image-refine-prompt').val('');
    //     $('#f2e-image-refine-modal').removeClass('hidden').addClass('flex');
    // }

    function openRefineModal(currentConcept) {
        if (isImageGenerating) return;

        // Fetch current image from state
        const metadata = window.F2E.State.lastGeneratedMetadata || {};
        const imageUrl = F2E.Utils.getFeaturedImageUrl(metadata.image);

        // Update Modal Preview Image
        const $previewImg = $('#f2e-refine-image-preview-display');
        if (imageUrl) {
            $previewImg.attr('src', imageUrl).show();
        } else {
            $previewImg.hide();
        }

        $('#f2e-current-concept-display').text(currentConcept);
        $('#f2e-image-refine-prompt').val('');
        $('#f2e-image-refine-modal').removeClass('hidden').addClass('flex');
    }

    function closeRefineModal() {
        $('#f2e-image-refine-modal').removeClass('flex').addClass('hidden');
    }

    // --- Image Utility Functions (UPDATED) ---

    /**
     * Shows a full-width loader across the main content area for image generation.
     */
    function startImageLoader() {
        if (imageLoaderInterval) clearInterval(imageLoaderInterval);
        isImageGenerating = true;
        imageCurrentProgress = 0;

        // Temporarily hide metadata actions and content to show loader clearly
        $('.f2e-metadata-actions').addClass('f2e-hidden');
        $('.f2e-image-preview-wrapper').addClass('f2e-hidden');
        $('.f2e-image-actions-new').find('button').prop('disabled', true).addClass('disabled');

        $loading.removeClass('f2e-hidden').empty();

        // **NEW LOADER HTML - MATCHING CONTENT LOADER STYLE**
        const loaderHTML = `
            <div class="f2e-progress-loader mt-6">
                <div class="f2e-loader-header flex justify-between items-center mb-3">
                    <span class="f2e-loader-label text-sm font-medium text-[var(--text-gray-700)]">Generating Featured Image. This may take up to 60 seconds...</span>
                    <span class="f2e-loader-percentage text-sm font-semibold text-[var(--blue-600)]">0%</span>
                </div>
                <div class="f2e-progress-bar-container w-full bg-[var(--bg-gray-200)] rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="f2e-progress-bar h-full bg-gradient-to-r from-[var(--blue-500)] to-[var(--blue-600)] rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                </div>
                <div class="f2e-loader-steps mt-2 text-xs text-[var(--text-gray-500)]">
                    <span class="f2e-current-step">Submitting request to the Image AI model...</span>
                </div>
            </div>
        `;
        $loading.html(loaderHTML);

        const $progressBar = $loading.find('.f2e-progress-bar');
        const $percentage = $loading.find('.f2e-loader-percentage');
        const $currentStep = $loading.find('.f2e-current-step');
        
        // Calculate the increment required to reach 100% in 60 seconds
        const increments = IMAGE_GENERATION_DURATION / IMAGE_UPDATE_INTERVAL;
        const incrementValue = 100 / increments;
        
        imageLoaderInterval = setInterval(() => {
            imageCurrentProgress += incrementValue;
            let progressPercent = Math.min(99, Math.round(imageCurrentProgress)); // Cap at 99%
            
            // If the time exceeds the expected duration, ensure we cap it to prevent progress from running forever
            if (imageCurrentProgress > 60000) {
                 progressPercent = 99;
            }

            $progressBar.css('width', `${progressPercent}%`);
            $percentage.text(`${progressPercent}%`);

            if (progressPercent < 25) { 
                $currentStep.text('Analyzing concept and desired style...'); 
            } else if (progressPercent < 50) { 
                $currentStep.text('Synthesizing image details...'); 
            } else if (progressPercent < 75) { 
                $currentStep.text('Rendering high-resolution output...'); 
            } else { 
                $currentStep.text('Finalizing image and saving to server...'); 
            }

        }, IMAGE_UPDATE_INTERVAL);
    }

    /**
     * Hides the image loader and re-enables buttons.
     */
    function stopImageLoader(success = true) {
        if (imageLoaderInterval) {
            clearInterval(imageLoaderInterval);
            imageLoaderInterval = null;
        }
        isImageGenerating = false;
        
        // Set to 100% on success before hiding
        if (success) {
            const $progressBar = $loading.find('.f2e-progress-bar');
            const $percentage = $loading.find('.f2e-loader-percentage');
            $progressBar.css('width', '100%');
            $percentage.text('100%');
            $loading.find('.f2e-current-step').text('Image generation complete!');
            
            // Give a brief moment for 100% to display
            setTimeout(() => {
                $loading.addClass('f2e-hidden').empty();
            }, 300);

        } else {
            $loading.addClass('f2e-hidden').empty();
        }

        // Re-enable main UI elements
        $('.f2e-metadata-actions').removeClass('f2e-hidden');
        $('.f2e-image-preview-wrapper').removeClass('f2e-hidden');
        $('.f2e-image-actions-new').find('button').prop('disabled', false).removeClass('disabled');
        imageCurrentProgress = 0;
    }

    /**
     * Updates the UI after successful image generation/refinement.
     * @param {object} newMetadata - The new metadata object containing title, image, and content type.
     */
    function updateMetadataPanel(newMetadata) {
        // Remove old panel
        $('.f2e-metadata-panel').remove();
        
        // Update the global state with the new image URL
        F2E.State.lastGeneratedMetadata = {
            ...F2E.State.lastGeneratedMetadata,
            image: newMetadata.image_url // The new image URL
        };

        // Re-create the metadata panel to reflect the new image
        if (F2E.Metadata && F2E.Metadata.createMetadataPanel) {
            F2E.Metadata.createMetadataPanel(F2E.State.lastGeneratedMetadata);
        }
    }

    // --- Image Handler Logic ---

    /**
     * Handles the click event for the "Refine Image" button.
     * Extracts the current image concept (which is stored in F2E.State.lastGeneratedMetadata.image).
     */
    function handleRefineButtonClick() {
        if (isImageGenerating) return;
        const metadata = F2E.State.lastGeneratedMetadata;
        if (!metadata || !metadata.image) {
            alert('Please generate a post first to get an image concept.');
            return;
        }

        const imageConcept = metadata.image;

        // If it seems to be a URL (meaning it's the result of a generation), use the title for refinement context
        if (imageConcept.startsWith('http') || imageConcept.startsWith('/')) {
             openRefineModal(metadata.title || 'Abstract post concept');
        } else {
             openRefineModal(imageConcept);
        }
    }

    /**
     * Handles the click event for the "Regenerate Image" button.
     * Simply calls the main refinement function with an empty prompt and a flag.
     */
    function handleRegenerateButtonClick() {
        if (isImageGenerating) return;
        const metadata = F2E.State.lastGeneratedMetadata;
        if (!metadata || !metadata.title) {
            alert('Please generate a post first to get a title/concept for regeneration.');
            return;
        }

        // Use the primary title as the concept for regeneration
        const currentConcept = metadata.title;
        const confirmRegenerate = confirm(i18n.confirm_regenerate_image || `Do you want to regenerate the featured image based on the title: "${currentConcept}"?`);
        
        if (confirmRegenerate) {
            processImageRequest(currentConcept, '', 'regenerate');
        }
    }

    /**
     * Handles the image refinement submission from the modal.
     */
    function handleImageRefine() {
        if (isImageGenerating) return;

        const metadata = F2E.State.lastGeneratedMetadata;
        const refinePrompt = $('#f2e-image-refine-prompt').val().trim();
        const currentConcept = $('#f2e-current-concept-display').text().trim();

        if (!metadata || !metadata.title) {
            alert('Missing post data. Please generate a post first.');
            closeRefineModal();
            return;
        }
        if (!refinePrompt) {
            alert('Please enter a refinement instruction.');
            return;
        }

        // For refinement, the new base concept is the combination of the old concept (title) and the refinement prompt
        const combinedConcept = `${currentConcept}, ${refinePrompt}`;

        processImageRequest(combinedConcept, '', 'refine');
        closeRefineModal();
    }

    /**
     * Main function to make the AJAX call for image refinement or regeneration.
     * @param {string} baseConcept - The base concept (usually the title or original image concept).
     * @param {string} instruction - The refinement instruction (empty for regeneration).
     * @param {string} actionType - 'refine' or 'regenerate'.
     */
    async function processImageRequest(baseConcept, instruction, actionType) {
        if (isImageGenerating) return;

        startImageLoader();

        const metadata = F2E.State.lastGeneratedMetadata;
        const endpoint = actionType === 'refine' ? MPAI_vars.ajax_url_refine_image : MPAI_vars.ajax_url_regenerate_image;
        const contentType = F2E.Utils.getContentType();

        const formData = new FormData();
        formData.append('base_concept', baseConcept); // This holds the combined concept for refine, or title for regenerate
        formData.append('refinement_instruction', instruction); // This is intentionally empty for regenerate, only used in refine endpoint logic
        formData.append('post_title', metadata.title || '');
        formData.append('content_type', contentType);
        
        let success = false;

        try {
            const data = await new Promise((resolve, reject) => {
                $.ajax({
                    url: endpoint,
                    method: 'POST',
                    headers: { 'X-WP-Nonce': MPAI_vars.nonce },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            resolve(response);
                        } else {
                            reject(new Error(response.message || 'Image generation failed.'));
                        }
                    },
                    error: function(jqXHR) {
                        const serverMessage = jqXHR.responseJSON?.message || jqXHR.statusText;
                        reject(new Error(serverMessage ?? i18n?.unknown_error ?? "Unknown server error. Please try again."));
                    },
                });
            });

            // Update UI with new image
            updateMetadataPanel({
                image_url: data.image_url,
                title: data.post_title,
                content_type: contentType
            });
            success = true;

        } catch (error) {
            console.error(`Image ${actionType} error:`, error);
            alert(`Error during image ${actionType}: ${error.message}`);
        } finally {
            stopImageLoader(success);
        }
    }
    
    // --- Global Image Action Setup ---

    /**
     * Attaches click handlers to the new buttons within the metadata panel.
     * This needs to be called after the panel is created (i.e., inside F2E.Metadata.createMetadataPanel).
     */
    function attachImageActionHandlers() {
        // Delegate event handling for buttons inside the dynamically loaded metadata panel
        $(document).off('click', '.f2e-refine-image-button').on('click', '.f2e-refine-image-button', handleRefineButtonClick);
        $(document).off('click', '.f2e-regenerate-image-button').on('click', '.f2e-regenerate-image-button', handleRegenerateButtonClick);
    }
    
    // --- Expose and Initialize ---
    
    window.F2E.ImageActions = {
        initImageRefineModal,
        attachImageActionHandlers
    };

    $(document).ready(function() {
        window.F2E.ImageActions.initImageRefineModal();
        window.F2E.ImageActions.attachImageActionHandlers();
    });

})(jQuery);