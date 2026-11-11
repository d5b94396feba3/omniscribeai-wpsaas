

<div class="f2e-tool-wrapper max-w-7xl mx-auto px-4 py-8 lg:py-12">



    <h3 class="f2e-section-title text-3xl font-extrabold text-[var(--text-gray-800)] mb-8 lg:mb-10">Content Studio</h3>

    <p class="text-slate-500 text-lg">Generate high-converting social and blog content in seconds.</p>

    <div class="f2e-grid-container grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">

        <div class="f2e-column lg:col-span-1 f2e-left-column">

            <div class="f2e-card f2e-description-field f2e-description-section border border-[var(--border-gray-100)] rounded-xl shadow-lg bg-[var(--bg-white)] overflow-hidden transition-all duration-300 hover:shadow-xl hover:border-[var(--blue-200)] h-full p-6 flex flex-col">

                <div class="f2e-form-group space-y-6 flex-1 flex flex-col">

                    

                    <div class="f2e-toggle-switch flex items-center justify-between p-0 m-0">

                        <label for="f2e-input-mode-toggle" class="f2e-switch-label text-base font-semibold text-[var(--text-gray-800)]">Text</label>

                        <label class="f2e-switch">

                            <input type="checkbox" id="f2e-input-mode-toggle">

                            <span class="f2e-slider"></span>

                        </label>

                        <label for="f2e-input-mode-toggle" class="f2e-switch-label text-base font-semibold text-[var(--text-gray-800)]">Youtube</label>

                    </div>

                    <div id="f2e-text-input-container">

                        <div class="f2e-textarea-wrapper flex-1">

                            <label for="f2e-description" class="sr-only">Description for content</label>

                            <input type="hidden" name="desc-tag-name" value="">

                            <textarea id="f2e-description" name="description" class="placeholder:text-sm w-full p-4 border border-[var(--border-gray-200)] rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] h-full resize-y transition-colors duration-200 text-[var(--text-gray-800)] bg-[var(--bg-white)]" 

                            placeholder="Give a topic name or description or any text..."></textarea>

                            <p class="text-gray-500 text-sm mt-2">Enter the topic description or any text to generate content.</p>

                        </div>

                    </div>

                    <div id="f2e-youtube-input-container" class="f2e-hidden">

                        <div id="panel-website" role="tabpanel" aria-labelledby="tab-website" class="tab-panel">

                            <label for="website-url-input" class="block text-lg font-medium text-gray-700 mb-2">Youtube Video URL</label>

                            <input

                            type="url"

                            id="f2e-site-url"

                            placeholder="<?php esc_attr_e('https://www.youtube.com/watch?v=LO5OMv7J1h8', 'figma-to-elementor'); ?>"

                            class="w-full px-4 py-2 text-sm text-gray-800 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"

                            style="border-radius: 5px;"

                            >

                            <p class="text-gray-500 text-sm mt-2 mb-4">Enter the public URL of a video you want to explain or blog.</p>

                        </div>

                        

                        <button type="button" id="f2e-toggle-yt-description" class="f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center gap-2 transition-colors duration-200 text-sm">

                            <span class="f2e-toggle-icon">

                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>

                            </span>

                            Add Specific Instructions (Optional)

                        </button>

                        

                        <div id="f2e-description-yt-container" class="mt-4">

                        <div class="f2e-textarea-wrapper flex-1">

                            <label for="f2e-description-yt" class="block text-sm font-medium text-gray-700 mb-1">Additional Instructions for AI</label>

                            <textarea id="f2e-description-yt" name="description-yt" class="w-full p-4 border border-[var(--border-gray-200)] rounded-lg h-32 bg-[var(--bg-white)]" placeholder="E.g., Focus only on the technical details..."></textarea>

                        </div>

                    </div>

                    </div>

                    

                    <div class="f2e-setting-group">

                        <label class="f2e-setting-label block text-lg font-medium text-[var(--text-gray-700)] mb-2">Target Platform <span class="text-[var(--text-red-500)]">*</span></label>

                        <div class="f2e-content-type-selector relative">

                            <select id="f2e-content-type" class="w-full p-3 border border-[var(--border-gray-200)] rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] bg-white text-[var(--text-gray-800)] appearance-none cursor-pointer transition-colors duration-200">

                                <option value="medium">Medium Post</option>

                                <option value="devio">Dev.io Post</option>

                                <option value="linkedin">Linkedin Post</option>

                                <option value="facebook">Facebook Post</option>

                                <option value="instagram">Instagram Post</option>

                                <option value="x-twitter">X (Formaly Known Twitter) Post</option>

                                <option value="website">Website Blog Post</option>

                            </select>

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">

                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">

                                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>

                                </svg>

                            </div>

                        </div>

                        <p class="f2e-setting-description text-[var(--text-gray-500)] text-sm mt-2">Choose Target Platform</p>

                        

                    </div>

                    <div class="f2e-setting-group mt-6">
                        <label class="f2e-setting-label block text-lg font-medium text-[var(--text-gray-700)] mb-2">Iterations <span class="text-[var(--text-red-500)]">*</span></label>
                        <div class="f2e-iteration-selector flex items-center gap-2 bg-[var(--bg-gray-50)] p-1 rounded-lg border border-[var(--border-gray-200)] shadow-inner" role="radiogroup" aria-label="Number of iterations for generation">
                            <button type="button" class="f2e-iteration-option px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-1" data-value="1" aria-pressed="false" aria-label="1 Iteration">1</button>
                            <button type="button" class="f2e-iteration-option active px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-1" data-value="2" aria-pressed="true" aria-label="2 Iterations">2</button>
                        </div>
                        <p class="f2e-setting-description text-[var(--text-gray-500)] text-sm mt-2">Recommended: 2. More iterations can improve result output but may take longer.</p>
                    </div>

                    <div class="f2e-setting-group mt-6">

                        <button id="f2e-advanced-settings-button" type="button" class="f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center gap-2 transition-colors duration-200">

                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">

                                <path d="M12 20v-8M12 4v4M4 12h4M16 12h4M6 16v-2h12v2M8 8v8M16 8v8"/>

                            </svg>

                            Advanced Settings

                        </button>

                    </div>

                </div>



                <button id="f2e-generate-button" class="f2e-button f2e-button-primary py-3 px-6 text-lg font-semibold w-full mt-8 flex items-center justify-center gap-3 rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-2 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed" aria-label="Generate content with 100 points">

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



        <div class="f2e-column lg:col-span-2 -mt-2">

            <div class="f2e-result-section f2e-card p-6 h-full border border-[var(--border-gray-100)] rounded-xl shadow-lg bg-[var(--bg-white)] overflow-hidden transition-all duration-300 hover:shadow-xl flex flex-col">



                <div id="f2e-result-container" class="f2e-hidden-disable flex-1 flex flex-col">

                    <div class="f2e-result-header mb-4">

                        <div class="f2e-result-actions f2e-hidden flex justify-between items-center flex-wrap gap-3">

                            <div class="f2e-left-actions flex flex-wrap gap-3">

                              

                                <button id="f2e-copy-button" class="f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center gap-2 transition-colors duration-200" aria-label="Copy generated content">

                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" aria-hidden="true">

                                        <path d="M8 16H6C4.89543 16 4 15.1046 4 14V6C4 4.89543 4.89543 4 6 4H14C15.1046 4 16 4.89543 16 6V8M10 20H18C19.1046 20 20 19.1046 20 18V10C20 8.89543 19.1046 8 18 8H10C8.89543 8 8 8.89543 8 10V18C8 19.1046 8.89543 20 10 20Z" />

                                    </svg>

                                    Copy

                                </button>

                                <button id="f2e-download-button" class="f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center gap-2 transition-colors duration-200" aria-label="Download generated content">

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

                    

                    <div class="f2e-tabs-container mb-4">

                        <div class="f2e-tabs-nav flex border-b border-[var(--border-gray-200)]">

                            <button class="f2e-tab-button active py-2 px-4 border-b-2 border-[var(--primary-color)] text-[var(--primary-color)] font-medium" data-tab="code">

                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="inline mr-2">

                                    <polyline points="16 18 22 12 16 6"></polyline>

                                    <polyline points="8 6 2 12 8 18"></polyline>

                                </svg>

                                Content

                            </button>

                            <button class="f2e-tab-button py-2 px-4 text-[var(--text-gray-500)] font-medium" data-tab="preview">

                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="inline mr-2">

                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>

                                    <line x1="8" y1="21" x2="16" y2="21"></line>

                                    <line x1="12" y1="17" x2="12" y2="21"></line>

                                </svg>

                                Preview

                            </button>

                        </div>

                    </div>



                    <div class="f2e-tab-content active" id="code-tab">

                        <div class="f2e-code-container bg-gray-800 text-white rounded-lg p-4 overflow-auto h-96 font-mono text-sm shadow-inner flex-1">

                            <pre><code id="f2e-result" class="language-html"> 

                                <p id="result-default-text" class="text-[var(--text-gray-600)] text-center">

                                    No content generated yet.

                                </p>

                            </code></pre>

                        </div>

                    </div>



                    <div class="f2e-tab-content hidden" id="preview-tab">

                        <div class="f2e-preview-container border border-[var(--border-gray-200)] rounded-lg bg-white overflow-hidden h-96 flex flex-col">

                            <div class="f2e-preview-toolbar bg-[var(--bg-gray-50)] px-4 py-2 border-b border-[var(--border-gray-200)] flex items-center justify-between">

                                <span class="text-sm font-medium text-[var(--text-gray-700)]">Live Preview</span>

                                <div class="f2e-preview-actions flex gap-2">

                                    <button id="f2e-refresh-preview" class="text-xs px-2 py-1 bg-white border border-[var(--border-gray-300)] rounded text-[var(--text-gray-600)] hover:bg-[var(--bg-gray-100)] transition-colors">

                                        Refresh

                                    </button>

                                </div>

                            </div>

                            <div class="f2e-preview-content flex-1 bg-white">

                                <iframe id="f2e-preview-frame" class="w-full h-full border-0" sandbox="allow-same-origin allow-scripts"></iframe>

                            </div>

                        </div>

                    </div>



                    <div id="f2e-loading" class="f2e-loading-container f2e-hidden flex flex-col items-center justify-center py-12 flex-1" role="status" aria-live="polite" aria-label="Generating content">
                        <div class="f2e-loading-spinner animate-spin rounded-full h-12 w-12 border-4 border-t-4 border-[var(--blue-600-text)] border-opacity-75"></div>
                        <div class="f2e-loading-content mt-4 text-center">
                            <h4 id="f2e-iteration-heading" class="text-xl font-semibold mb-2 text-[var(--text-gray-800)]">Generating your content...</h4>
                            <p id="f2e-iteration-status" class="text-[var(--text-gray-600)] text-center"></p>
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

        Important: Your input and generated content are not stored. All data will be cleared upon page refresh and images stored temporarily.

    </div>



    <div id="f2e-advanced-modal" class="f2e-modal fixed inset-0 z-[1000] bg-black bg-opacity-75 hidden items-center justify-center p-4 transition-opacity duration-300" aria-modal="true" role="dialog" tabindex="-1">

        <div class="f2e-modal-content bg-[var(--bg-white)] rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col transform transition-transform duration-300 scale-95" style="z-index: 1001;">

            

            <div class="f2e-modal-header flex justify-between items-center p-4 border-b border-[var(--border-gray-200)]">

                <h5 class="text-xl font-semibold text-[var(--text-gray-800)]">Advanced Content Settings</h5>

                <button type="button" class="f2e-close-modal text-[var(--text-gray-500)] hover:text-[var(--text-gray-800)] transition-colors" aria-label="Close modal">

                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6L18 18"/></svg>

                </button>

            </div>

            

            <div class="f2e-modal-body p-6 flex-1 overflow-y-auto space-y-5">

                

                <div class="f2e-form-group">

                    <label for="f2e-target-keyword" class="block text-lg font-medium text-gray-700 mb-2">Target Keyword</label>

                    <input

                        type="text"

                        id="f2e-target-keyword"

                        placeholder="React Performance Optimization"

                        class="w-full px-4 py-2 text-sm text-gray-800 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"

                        style="border-radius: 5px;"

                    >

                    <p class="text-gray-500 text-sm mt-2">The primary keyword for SEO.</p>

                </div>



                <div class="f2e-form-group">

                    <label for="f2e-audience-level" class="block text-lg font-medium text-gray-700 mb-2">Audience Level</label>

                    <select id="f2e-audience-level" class="w-full p-3 border border-[var(--border-gray-200)] rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] bg-white text-[var(--text-gray-800)] appearance-none cursor-pointer transition-colors duration-200">

                        <option value="">(Select Level)</option>

                        <option value="Beginner">Beginner</option>

                        <option value="Intermediate">Intermediate</option>

                        <option value="Advanced">Advanced</option>

                    </select>

                    <p class="text-gray-500 text-sm mt-2">The required technical level of the audience.</p>

                </div>

                

                <div class="f2e-form-group">

                    <label for="f2e-desired-tone" class="block text-lg font-medium text-gray-700 mb-2">Desired Tone</label>

                    <select id="f2e-desired-tone" class="w-full p-3 border border-[var(--border-gray-200)] rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] bg-white text-[var(--text-gray-800)] appearance-none cursor-pointer transition-colors duration-200">

                        <option value="">(Select Tone)</option>

                        <option value="Tutorial/Instructive">Tutorial/Instructive</option>

                        <option value="Opinion/Thought Leadership">Opinion/Thought Leadership</option>

                        <option value="In-depth Analysis">In-depth Analysis</option>

                        <option value="Casual/Informal">Casual/Informal</option>

                        <option value="Formal/Academic">Formal/Academic</option>

                    </select>

                    <p class="text-gray-500 text-sm mt-2">The style and tone of the generated content.</p>

                </div>



                <div class="f2e-form-group">

                    <label for="f2e-language" class="block text-lg font-medium text-gray-700 mb-2">Language</label>

                    <select id="f2e-language" class="w-full p-3 border border-[var(--border-gray-200)] rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] bg-white text-[var(--text-gray-800)] appearance-none cursor-pointer transition-colors duration-200">

                        <option value="English">English</option>

                        <option value="Spanish">Spanish</option>

                        <option value="French">French</option>

                        <option value="German">German</option>

                        <option value="Japanese">Japanese</option>

                        <option value="Chinese">Chinese</option>

                    </select>

                    <p class="text-gray-500 text-sm mt-2">The language for the generated output.</p>

                </div>



                <div class="f2e-form-group">

                    <label for="f2e-required-length" class="block text-lg font-medium text-gray-700 mb-2">Required Length</label>

                    <select id="f2e-required-length" class="w-full p-3 border border-[var(--border-gray-200)] rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] bg-white text-[var(--text-gray-800)] appearance-none cursor-pointer transition-colors duration-200">

                        <option value="">(Default/Auto)</option>

                        <option value="Short (approx 300 words)">Short (approx 300 words)</option>

                        <option value="Medium (approx 500-800 words)">Medium (approx 500-800 words)</option>

                        <option value="Long (approx 1000-1500 words)">Long (approx 1000-1500 words)</option>

                        <option value="In-depth (2000+ words)">In-depth (2000+ words)</option>

                    </select>

                    <p class="text-gray-500 text-sm mt-2">Specify the approximate length of the final content.</p>

                </div>



                <div class="f2e-form-group">

                    <label for="f2e-call-to-action" class="block text-lg font-medium text-gray-700 mb-2">Call to Action (CTA)</label>

                    <textarea 

                        id="f2e-call-to-action" 

                        placeholder="e.g., Subscribe to our newsletter for more tips!"

                        class="w-full px-4 py-2 text-sm text-gray-800 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"

                        style="border-radius: 5px; height: 80px;"

                    ></textarea>

                    <p class="text-gray-500 text-sm mt-2">A specific instruction to conclude the post.</p>

                </div>



            </div>

            

            <div class="f2e-modal-footer flex justify-end p-4 border-t border-[var(--border-gray-200)]">

                <button type="button" class="f2e-close-modal f2e-button f2e-button-primary py-2 px-6 text-base font-medium rounded-xl flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200">

                    Done

                </button>

            </div>

        </div>

    </div>

</div>
