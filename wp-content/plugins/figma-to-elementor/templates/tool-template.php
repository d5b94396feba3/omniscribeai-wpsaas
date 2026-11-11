

    <div class="f2e-tool-wrapper max-w-7xl mx-auto px-4 py-8 lg:py-12">

        <h3 class="f2e-section-title text-3xl font-extrabold text-[var(--text-gray-800)] mb-8 lg:mb-10">Generate 3D Interactive Web Components</h3>

        <div class="f2e-grid-container grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">

            <!-- Left Column: Inputs -->
            <div class="f2e-column lg:col-span-1 f2e-left-column">
                <div class="f2e-card f2e-description-field f2e-description-section border border-[var(--border-gray-100)] rounded-xl shadow-lg bg-[var(--bg-white)] overflow-hidden transition-all duration-300 hover:shadow-xl hover:border-[var(--blue-200)] h-full p-6 flex flex-col">
                    <div class="f2e-form-group space-y-6 flex-1 flex flex-col">
                        <div class="f2e-textarea-wrapper flex-1">
                            <label for="f2e-description" class="sr-only">Description for 3D component</label>
                            <input type="hidden" name="desc-tag-name" value="">
                            <textarea id="f2e-description" name="description" class="placeholder:text-sm w-full p-4 border border-[var(--border-gray-200)] rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] h-full resize-y transition-colors duration-200 text-[var(--text-gray-800)] bg-[var(--bg-white)]" placeholder="Generate a 3d logo for my IT consulting business, make it spin slowly and glow blue..."></textarea>
                        </div>

                        <div class="f2e-setting-group mt-6">
                            <label class="f2e-setting-label block text-lg font-medium text-[var(--text-gray-700)] mb-2">Iterations <span class="text-[var(--text-red-500)]">*</span></label>
                            <div class="f2e-iteration-selector flex items-center gap-2 bg-[var(--bg-gray-50)] p-1 rounded-lg border border-[var(--border-gray-200)] shadow-inner" role="radiogroup" aria-label="Number of iterations for generation">
                                <button type="button" class="f2e-iteration-option px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-1" data-value="1" aria-pressed="false" aria-label="1 Iteration">1</button>
                                <button type="button" class="f2e-iteration-option active px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-1" data-value="2" aria-pressed="true" aria-label="2 Iterations">2</button>
                            </div>
                            <p class="f2e-setting-description text-[var(--text-gray-500)] text-sm mt-2">Recommended: 2. More iterations can improve result output but may take longer.</p>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <button id="f2e-generate-button" class="f2e-button f2e-button-primary py-3 px-6 text-lg font-semibold w-full mt-8 flex items-center justify-center gap-3 rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-2 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed" aria-label="Generate code with 100 points">
                        <span class="f2e-button-content flex items-center gap-2">
                            Generate
                            <span class="f2e-points-badge ml-2 px-3 py-1 bg-[var(--white-opacity-20)] rounded-full text-xs font-bold">100 Points</span>
                        </span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="f2e-button-icon text-white">
                            <path d="M13 6L19 12L13 18M5 6L11 12L5 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Right Column: Output -->
            <div class="f2e-column lg:col-span-2 -mt-2">
                <div class="f2e-result-section f2e-card p-6 h-full border border-[var(--border-gray-100)] rounded-xl shadow-lg bg-[var(--bg-white)] overflow-hidden transition-all duration-300 hover:shadow-xl flex flex-col">

                    <div id="f2e-result-container" class="f2e-hidden-disable flex-1 flex flex-col">
                        <div class="f2e-result-header mb-4">
                            <div class="f2e-result-actions f2e-hidden flex justify-between items-center flex-wrap gap-3">
                                <div class="f2e-left-actions flex flex-wrap gap-3">
                                    <button id="f2e-preview-button" class="f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center gap-2 transition-colors duration-200" aria-label="Preview generated code">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" />
                                            <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" />
                                        </svg>
                                        Preview
                                    </button>
                                    <button id="f2e-copy-button" class="f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center gap-2 transition-colors duration-200" aria-label="Copy generated code">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M8 16H6C4.89543 16 4 15.1046 4 14V6C4 4.89543 4.89543 4 6 4H14C15.1046 4 16 4.89543 16 6V8M10 20H18C19.1046 20 20 19.1046 20 18V10C20 8.89543 19.1046 8 18 8H10C8.89543 8 8 8.89543 8 10V18C8 19.1046 8.89543 20 10 20Z" />
                                        </svg>
                                        Copy
                                    </button>
                                    <button id="f2e-download-button" class="f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center gap-2 transition-colors duration-200" aria-label="Download generated code">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 15L12 3M12 15L8 11M12 15L16 11M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15" />
                                        </svg>
                                        Download
                                    </button>
                                </div>
                                <button id="f2e-clear-button" class="f2e-button2 f2e-button-danger py-2 px-4 rounded-lg bg-[var(--red-500)] text-[var(--bg-white)] hover:bg-[var(--red-600)] focus:outline-none focus:ring-2 focus:ring-[var(--red-500)] flex items-center gap-2 transition-colors duration-200" aria-label="Clear output">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M18 6L6 18M6 6L18 18" />
                                    </svg>
                                    Clear
                                </button>
                            </div>
                        </div>
                        <div class="f2e-code-container bg-gray-800 text-white rounded-lg p-4 overflow-auto h-96 font-mono text-sm shadow-inner flex-1">
                            <pre><code id="f2e-result" class="language-html"> 
                                 <p id="result-default-text" class="text-[var(--text-gray-600)] text-center">
                               No code generated yet.
                                </p></code></pre>
                        </div>

                        <div id="f2e-loading" class="f2e-loading-container f2e-hidden flex flex-col items-center justify-center py-12 flex-1" role="status" aria-live="polite" aria-label="Generating code">
                            <div class="f2e-loading-spinner animate-spin rounded-full h-12 w-12 border-4 border-t-4 border-[var(--blue-600-text)] border-opacity-75"></div>
                            <div class="f2e-loading-content mt-4 text-center">
                                <h4 id="f2e-iteratin-heading" class="text-xl font-semibold mb-2 text-[var(--text-gray-800)]">Generating your 3D web component...</h4>
                                <p id="f2e-iteration-status" class="text-[var(--text-gray-600)] text-center">
                                <!-- Processing generation request. This may take a few moments depending on complexity. -->
                                </p>
                            </div>
                        </div>
                    </div>
                
                </div>
            </div>
        </div>
        <div class="f2e-disclaimer mt-8 p-4 bg-[var(--blue-50-light)] text-[var(--blue-800-dark)] rounded-lg text-sm flex items-start gap-2" role="alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 mt-0.5 w-4 h-4 text-[var(--blue-600-text)]" aria-hidden="true">
                <path d="M12 13V9M12 17V16.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Important: Your input and generated code are not stored. All data will be cleared upon page refresh.
        </div>
    </div>

