<div class="f2e-tool-wrapper max-w-7xl mx-auto px-4 py-8 lg:py-12 bg-white">

    <div class="mb-10">
        <h3 class="f2e-section-title text-4xl font-black text-slate-900 mb-2">Content Studio</h3>
        <p class="text-slate-500 text-lg">Generate high-converting social and blog content in seconds.</p>
    </div>

    <div class="f2e-grid-container grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">

        <div class="f2e-column lg:col-span-1 f2e-left-column">
            <div class="f2e-card f2e-description-field f2e-description-section border border-slate-200 rounded-2xl shadow-sm bg-white overflow-hidden transition-all duration-300 hover:shadow-md h-full p-6 flex flex-col">

                <div class="f2e-form-group space-y-6 flex-1 flex flex-col">

                    <div class="f2e-toggle-switch flex items-center justify-between p-1 bg-slate-100 rounded-xl border border-slate-200">
                        <label for="f2e-input-mode-toggle" class="f2e-switch-label flex-1 text-center py-2 text-sm font-bold cursor-pointer transition-colors text-slate-700">Text</label>
                        <label class="f2e-switch relative inline-block w-12 h-6 mx-2">
                            <input type="checkbox" id="f2e-input-mode-toggle" class="sr-only peer">
                            <span class="f2e-slider absolute cursor-pointer inset-0 bg-slate-300 rounded-full transition-all peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-6"></span>
                        </label>
                        <label for="f2e-input-mode-toggle" class="f2e-switch-label flex-1 text-center py-2 text-sm font-bold cursor-pointer transition-colors text-slate-700">YouTube</label>
                    </div>

                    <div id="f2e-text-input-container">
                        <div class="f2e-textarea-wrapper flex-1">
                            <label for="f2e-description" class="block text-sm font-bold text-slate-700 mb-2">Topic or Content</label>
                            <textarea id="f2e-description" name="description" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none text-slate-800 h-48 resize-none" placeholder="Give a topic name or description or any text..."></textarea>
                            <p class="text-slate-400 text-xs mt-2">Enter the topic description or any text to generate content.</p>
                        </div>
                    </div>

                    <div id="f2e-youtube-input-container" class="f2e-hidden">
                        <div id="panel-website" role="tabpanel" aria-labelledby="tab-website" class="tab-panel">
                            <label for="f2e-site-url" class="block text-sm font-bold text-slate-700 mb-2">YouTube Video URL</label>
                            <input type="url" id="f2e-site-url" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                            <p class="text-slate-400 text-xs mt-2 mb-4">Enter the public URL of a video you want to explain or blog.</p>
                        </div>
                        
                        <button type="button" id="f2e-toggle-yt-description" class="f2e-button f2e-button-secondary w-full py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-blue-600 hover:text-white font-semibold flex items-center justify-center gap-2 transition-all text-sm">
                            <i class="fas fa-plus-circle text-xs"></i> Add Specific Instructions (Optional)
                        </button>
                        
                        <div id="f2e-description-yt-container" class="mt-4">
                            <div class="f2e-textarea-wrapper flex-1">
                                <label for="f2e-description-yt" class="block text-xs font-bold text-slate-500 mb-1">Additional Instructions for AI</label>
                                <textarea id="f2e-description-yt" name="description-yt" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl h-24 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="E.g., Focus only on the technical details..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="f2e-setting-group">
                        <label class="f2e-setting-label block text-sm font-bold text-slate-700 mb-2">Target Platform <span class="text-rose-500">*</span></label>
                        <div class="f2e-content-type-selector relative">
                            <select id="f2e-content-type" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl appearance-none cursor-pointer focus:ring-2 focus:ring-blue-500 outline-none text-slate-800 font-medium">
                                <option value="website">Website Blog Post</option>
                                <option value="linkedin">LinkedIn Professional Post</option>
                                <option value="medium">Medium Article</option>
                                <option value="devio">Dev.to Technical Post</option>
                                <option value="facebook">Facebook Social Post</option>
                                <option value="instagram">Instagram Caption</option>
                                <option value="x-twitter">X (Twitter) Thread</option>
                            </select>
                        </div>
                    </div>

                    <div class="f2e-setting-group">
                        <label class="f2e-setting-label block text-sm font-bold text-slate-700 mb-2">Quality Iterations <span class="text-rose-500">*</span></label>
                        <div class="f2e-iteration-selector flex p-1 bg-slate-100 rounded-xl border border-slate-200" role="radiogroup">
                            <button type="button" class="f2e-iteration-option flex-1 py-2 text-xs font-bold rounded-lg transition-all" data-value="1">Draft (1)</button>
                            <button type="button" class="f2e-iteration-option active flex-1 py-2 text-xs font-bold rounded-lg transition-all bg-white shadow-sm text-blue-600" data-value="2">Polished (2)</button>
                        </div>
                    </div>

                    <div class="f2e-setting-group pt-2">
                        <button id="f2e-advanced-settings-button" type="button" class="f2e-button f2e-button-secondary w-full py-2.5 px-4 rounded-xl border border-slate-200 text-slate-700 hover:bg-blue-600 hover:text-white font-bold flex items-center justify-center gap-2 transition-all">
                            <i class="fas fa-sliders-h text-sm"></i> Advanced Settings
                        </button>
                    </div>

                </div>

                <button id="f2e-generate-button" class="f2e-button f2e-button-primary py-4 px-6 text-lg font-bold w-full mt-8 flex items-center justify-center gap-3 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200 hover:bg-blue-700 hover:text-white hover:-translate-y-0.5 transition-all">
                    <span class="f2e-button-content flex items-center gap-2">
                        Generate
                        <span class="f2e-points-badge ml-2 px-2 py-0.5 bg-blue-500 rounded text-[10px] font-black uppercase tracking-tighter">100 Points</span>
                    </span>
                    <i class="fas fa-magic text-sm"></i>
                </button>

            </div>
        </div>

        <div class="f2e-column lg:col-span-2">
            <div class="f2e-result-section f2e-card h-full border border-slate-200 rounded-2xl shadow-sm bg-white overflow-hidden flex flex-col transition-all duration-300 hover:shadow-md">

                <div id="f2e-result-container" class="flex-1 flex flex-col p-6">
                    
                    <div class="f2e-result-header mb-6">
                        <div class="f2e-result-actions flex justify-between items-center flex-wrap gap-3">
                            <div class="f2e-left-actions flex gap-2">
                                <button id="f2e-copy-button" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-blue-600 hover:text-white flex items-center gap-2 transition-all">
                                    <i class="far fa-copy"></i> Copy
                                </button>
                                <button id="f2e-download-button" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 hover:bg-blue-600 hover:text-white flex items-center gap-2 transition-all">
                                    <i class="fas fa-download"></i> Download
                                </button>
                            </div>
                            <button id="f2e-clear-button" class="px-4 py-2 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-600 hover:text-white transition-all">
                                <i class="fas fa-trash-alt mr-2"></i> Clear
                            </button>
                        </div>
                    </div>

                    <div class="f2e-tabs-container mb-4">
                        <div class="f2e-tabs-nav flex gap-1 bg-slate-100 p-1 rounded-xl w-fit">
                            <button class="f2e-tab-button active py-2 px-6 rounded-lg font-bold text-sm bg-white shadow-sm text-blue-600" data-tab="preview">
                                <i class="fas fa-eye mr-2"></i> PREVIEW
                            </button>
                            <button class="f2e-tab-button py-2 px-6 rounded-lg font-bold text-sm text-slate-500 hover:text-slate-700" data-tab="code">
                                <i class="fas fa-code mr-2"></i> DETAILS
                            </button>
                        </div>
                    </div>

                    <div class="relative flex-1 min-h-[500px] flex flex-col">
                        <div class="f2e-tab-content active flex-1 flex flex-col" id="code-tab">
                            <div class="f2e-code-container bg-slate-900 text-slate-300 rounded-2xl p-6 overflow-auto font-mono text-sm shadow-inner flex-1 border border-slate-800">
                                <pre><code id="f2e-result" class="language-html leading-relaxed text-blue-100"><p id="result-default-text" class="text-slate-500 text-center py-20">No content generated yet.</p></code></pre>
                            </div>
                        </div>

                        <div class="f2e-tab-content hidden flex-1 flex flex-col" id="preview-tab">
                            <div class="f2e-preview-container border border-slate-200 rounded-2xl bg-white overflow-hidden flex-1 flex flex-col shadow-inner">
                                <iframe id="f2e-preview-frame" class="w-full h-full border-0" sandbox="allow-same-origin allow-scripts"></iframe>
                            </div>
                        </div>

                        <div id="f2e-loading" class="f2e-loading-container f2e-hidden absolute inset-0 bg-white/90 backdrop-blur-sm z-10 flex flex-col items-center justify-center py-12 rounded-2xl" role="status">
                            <div class="f2e-loading-spinner animate-spin rounded-full h-14 w-14 border-4 border-slate-200 border-t-blue-600"></div>
                            <div class="f2e-loading-content mt-6 text-center">
                                <h4 id="f2e-iteration-heading" class="text-xl font-bold text-slate-900 mb-1">Generating your content...</h4>
                                <p id="f2e-iteration-status" class="text-slate-500 text-sm"></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="f2e-disclaimer mt-8 p-5 bg-blue-50 border border-blue-100 rounded-2xl text-blue-800 text-sm flex items-start gap-4">
        <i class="fas fa-info-circle mt-0.5 text-blue-600 text-lg"></i>
        <p class="leading-relaxed">
            <strong>Important:</strong> Your input and generated content are not stored. All data will be cleared upon page refresh and images stored temporarily.
        </p>
    </div>

    <div id="f2e-advanced-modal" class="f2e-modal fixed inset-0 z-[1000] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 transition-all duration-300" aria-modal="true" role="dialog" tabindex="-1">
        <div class="f2e-modal-content bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col transform transition-all scale-95">
            
            <div class="f2e-modal-header flex justify-between items-center p-6 border-b border-slate-100">
                <h5 class="text-xl font-bold text-slate-900">Advanced Content Settings</h5>
                <button type="button" class="f2e-close-modal text-slate-400 hover:text-slate-900 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="f2e-modal-body p-6 overflow-y-auto space-y-6">
                <div class="f2e-form-group">
                    <label for="f2e-target-keyword" class="block text-sm font-bold text-slate-700 mb-2">Target Keyword</label>
                    <input type="text" id="f2e-target-keyword" placeholder="React Performance Optimization" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    <p class="text-slate-400 text-[10px] mt-1 uppercase tracking-wider">The primary keyword for SEO.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="f2e-form-group">
                        <label for="f2e-audience-level" class="block text-sm font-bold text-slate-700 mb-2">Audience Level</label>
                        <select id="f2e-audience-level" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-medium appearance-none">
                            <option value="">(Select Level)</option>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>

                    <div class="f2e-form-group">
                        <label for="f2e-desired-tone" class="block text-sm font-bold text-slate-700 mb-2">Desired Tone</label>
                        <select id="f2e-desired-tone" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-medium appearance-none">
                            <option value="">(Select Tone)</option>
                            <option value="Tutorial/Instructive">Tutorial/Instructive</option>
                            <option value="Opinion/Thought Leadership">Opinion/Thought Leadership</option>
                            <option value="In-depth Analysis">In-depth Analysis</option>
                            <option value="Casual/Informal">Casual/Informal</option>
                            <option value="Formal/Academic">Formal/Academic</option>
                        </select>
                    </div>
                </div>

                <div class="f2e-form-group">
                    <label for="f2e-language" class="block text-sm font-bold text-slate-700 mb-2">Language</label>
                    <div class="relative">
                        <select id="f2e-language" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-medium appearance-none">
                            <option value="English">English (Global)</option>
                            <option value="German">German (Deutsch) - DE</option>
                            <option value="French">French (Français) - FR</option>
                            <option value="Spanish">Spanish (Español) - ES</option>
                            <option value="Italian">Italian (Italiano) - IT</option>
                            <option value="Dutch">Dutch (Nederlands) - NL</option>
                            <option value="Portuguese">Portuguese (Português) - PT</option>
                            <option value="Swedish">Swedish (Svenska) - SE</option>
                            <option value="Japanese">Japanese (日本語) - JP</option>
                            <option value="Chinese">Chinese (Mandarin) - CN</option>
                        </select>
                    </div>
                    <p class="text-slate-400 text-[10px] mt-1 uppercase tracking-wider">The language for the generated output.</p>
                </div>

                <div class="f2e-form-group">
                    <label for="f2e-required-length" class="block text-sm font-bold text-slate-700 mb-2">Required Length</label>
                    <select id="f2e-required-length" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-medium appearance-none">
                        <option value="">(Default/Auto)</option>
                        <option value="Short (approx 300 words)">Short (approx 300 words)</option>
                        <option value="Medium (approx 500-800 words)">Medium (approx 500-800 words)</option>
                        <option value="Long (approx 1000-1500 words)">Long (approx 1000-1500 words)</option>
                        <option value="In-depth (2000+ words)">In-depth (2000+ words)</option>
                    </select>
                </div>

                <div class="f2e-form-group">
                    <label for="f2e-call-to-action" class="block text-sm font-bold text-slate-700 mb-2">Call to Action (CTA)</label>
                    <textarea id="f2e-call-to-action" placeholder="e.g., Subscribe to our newsletter for more tips!" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl h-24 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                    <p class="text-slate-400 text-[10px] mt-1 uppercase tracking-wider">A specific instruction to conclude the post.</p>
                </div>

            </div>
            
            <div class="f2e-modal-footer p-6 border-t border-slate-100 flex justify-end">
                <button type="button" class="f2e-close-modal f2e-done-modal f2e-button f2e-button-primary py-3 px-10 text-base font-bold text-white bg-blue-600 rounded-2xl shadow-lg hover:bg-blue-700 hover:text-white transition-all">
                    Done
                </button>
            </div>
        </div>
    </div>

</div>