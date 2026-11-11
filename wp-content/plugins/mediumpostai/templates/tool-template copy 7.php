<div class="f2e-tool-wrapper max-w-7xl mx-auto px-4 py-12 lg:py-16 font-sans antialiased text-slate-900">
    <div class="mb-12 text-center lg:text-left">
        <h3 class="f2e-section-title text-4xl lg:text-5xl font-black tracking-tight text-slate-900 mb-4">
            Content Studio<span class="text-indigo-600">.</span>
        </h3>
        <p class="text-slate-500 text-lg lg:text-xl max-w-2xl">
            Elevate your digital presence. Generate high-converting social and blog content using advanced AI processing.
        </p>
    </div>

    <div class="f2e-grid-container grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="f2e-column lg:col-span-4 f2e-left-column">
            <div class="f2e-card f2e-description-field f2e-description-section border border-slate-200 rounded-2xl shadow-sm bg-white p-8 flex flex-col sticky top-8 transition-all hover:shadow-md">
                <div class="f2e-form-group space-y-8 flex-1 flex flex-col">
                    
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Input Source</span>
                        <div class="f2e-toggle-switch flex items-center bg-slate-100 p-1.5 rounded-xl border border-slate-200">
                            <label for="f2e-input-mode-toggle" class="f2e-switch-label flex-1 text-center py-2 text-sm font-bold cursor-pointer transition-all text-slate-600">Text</label>
                            <label class="f2e-switch relative inline-block w-14 h-7 mx-2">
                                <input type="checkbox" id="f2e-input-mode-toggle" class="sr-only peer">
                                <span class="f2e-slider absolute cursor-pointer inset-0 bg-indigo-600 rounded-full transition-all before:absolute before:h-5 before:w-5 before:left-1 before:bottom-1 before:bg-white before:rounded-full before:transition-all peer-checked:bg-indigo-600 peer-checked:before:translate-x-7"></span>
                            </label>
                            <label for="f2e-input-mode-toggle" class="f2e-switch-label flex-1 text-center py-2 text-sm font-bold cursor-pointer transition-all text-slate-600">YouTube</label>
                        </div>
                    </div>

                    <div id="f2e-text-input-container" class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Topic or Script</label>
                        <div class="f2e-textarea-wrapper relative">
                            <input type="hidden" name="desc-tag-name" value="">
                            <textarea id="f2e-description" name="description" 
                                class="w-full p-4 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 min-h-[180px] text-slate-800 bg-slate-50/50 transition-all placeholder:text-slate-400" 
                                placeholder="What would you like to write about?"></textarea>
                            <p class="text-slate-400 text-[11px] mt-2 italic leading-tight">Pro-tip: Be specific about your goal for better AI results.</p>
                        </div>
                    </div>

                    <div id="f2e-youtube-input-container" class="f2e-hidden space-y-4">
                        <div id="panel-website" role="tabpanel" aria-labelledby="tab-website" class="tab-panel">
                            <label for="website-url-input" class="block text-sm font-bold text-slate-700 mb-2">Video URL</label>
                            <input type="url" id="f2e-site-url"
                                placeholder="https://www.youtube.com/watch?v=..."
                                class="w-full px-4 py-3 text-sm text-slate-800 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 bg-slate-50/50 outline-none transition-all">
                        </div>
                        
                        <button type="button" id="f2e-toggle-yt-description" class="f2e-button f2e-button-secondary w-full py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-sm flex items-center justify-center gap-2 transition-all">
                            <span class="f2e-toggle-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                            </span>
                            Specific Instructions
                        </button>
                        
                        <div id="f2e-description-yt-container" class="mt-4">
                            <textarea id="f2e-description-yt" name="description-yt" class="w-full p-4 border border-slate-200 rounded-xl h-24 bg-slate-50/50 text-sm focus:ring-4 focus:ring-indigo-50" placeholder="E.g., Focus on technical takeaways..."></textarea>
                        </div>
                    </div>

                    <div class="f2e-setting-group">
                        <label class="f2e-setting-label block text-sm font-bold text-slate-700 mb-2">Target Platform <span class="text-rose-500">*</span></label>
                        <div class="f2e-content-type-selector relative">
                            <select id="f2e-content-type" class="w-full p-3 pr-10 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 bg-white text-slate-700 appearance-none cursor-pointer font-medium transition-all">
                                <option value="medium">Medium Post</option>
                                <option value="devio">Dev.io Post</option>
                                <option value="linkedin">LinkedIn Post</option>
                                <option value="facebook">Facebook Post</option>
                                <option value="instagram">Instagram Post</option>
                                <option value="x-twitter">X (Twitter) Post</option>
                                <option value="website">Website Blog Post</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="f2e-setting-group">
                        <label class="f2e-setting-label block text-sm font-bold text-slate-700 mb-2">Quality Iterations <span class="text-rose-500">*</span></label>
                        <div class="f2e-iteration-selector flex p-1 bg-slate-100 rounded-xl border border-slate-200" role="radiogroup">
                            <button type="button" class="f2e-iteration-option flex-1 py-2 text-xs font-bold rounded-lg transition-all" data-value="1">Draft (1)</button>
                            <button type="button" class="f2e-iteration-option active flex-1 py-2 text-xs font-bold rounded-lg transition-all bg-white shadow-sm text-indigo-600" data-value="2">Polished (2)</button>
                        </div>
                    </div>

                    <button id="f2e-advanced-settings-button" type="button" class="f2e-button f2e-button-secondary py-3 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-sm flex items-center justify-center gap-2 transition-all">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20v-8M12 4v4M4 12h4M16 12h4M6 16v-2h12v2M8 8v8M16 8v8"/></svg>
                        Refine Parameters
                    </button>
                </div>

                <button id="f2e-generate-button" class="f2e-button f2e-button-primary py-4 px-6 text-base font-bold w-full mt-8 flex items-center justify-center gap-3 rounded-xl shadow-lg shadow-indigo-200 bg-indigo-600 text-white hover:bg-indigo-700 hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="f2e-button-content flex items-center gap-2">
                        Generate Content
                        <span class="f2e-points-badge px-2 py-0.5 bg-indigo-500/30 rounded-md text-[10px] uppercase tracking-widest">100 Pts</span>
                    </span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="f2e-button-icon"><path d="M13 6L19 12L13 18M5 6L11 12L5 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </button>
            </div>
        </div>

        <div class="f2e-column lg:col-span-8">
            <div class="f2e-result-section f2e-card h-full border border-slate-200 rounded-2xl shadow-sm bg-white overflow-hidden flex flex-col min-h-[600px]">
                
                <div id="f2e-result-container" class="f2e-hidden-disable flex-1 flex flex-col">
                    <div class="f2e-result-header p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap justify-between items-center gap-4">
                        <div class="f2e-tabs-nav flex gap-1 p-1 bg-slate-200/50 rounded-lg">
                            <button class="f2e-tab-button active py-1.5 px-4 rounded-md text-xs font-bold transition-all bg-white text-indigo-600 shadow-sm" data-tab="code">CONTENT</button>
                            <button class="f2e-tab-button py-1.5 px-4 rounded-md text-xs font-bold transition-all text-slate-500 hover:text-slate-700" data-tab="preview">PREVIEW</button>
                        </div>

                        <div class="f2e-result-actions flex gap-2">
                            <button id="f2e-copy-button" class="f2e-button f2e-button-secondary py-1.5 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center gap-1.5 transition-all">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 16H6C4.89543 16 4 15.1046 4 14V6C4 4.89543 4.89543 4 6 4H14C15.1046 4 16 4.89543 16 6V8M10 20H18C19.1046 20 20 19.1046 20 18V10C20 8.89543 19.1046 8 18 8H10C8.89543 8 8 8.89543 8 10V18C8 19.1046 8.89543 20 10 20Z" /></svg>
                                Copy
                            </button>
                            <button id="f2e-download-button" class="f2e-button f2e-button-secondary py-1.5 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center gap-1.5 transition-all">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15L12 3M12 15L8 11M12 15L16 11M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15" /></svg>
                                Save
                            </button>
                            <button id="f2e-clear-button" class="f2e-button2 f2e-button-danger py-1.5 px-3 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs flex items-center gap-1.5 transition-all">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6L18 18" /></svg>
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="f2e-tab-content active flex-1 p-6" id="code-tab">
                        <div class="f2e-code-container bg-slate-900 rounded-xl p-6 h-[500px] overflow-auto shadow-inner border border-slate-800 relative">
                            <pre class="m-0"><code id="f2e-result" class="language-html text-indigo-300 font-mono text-sm leading-relaxed"> 
                                <div id="result-default-text" class="absolute inset-0 flex flex-col items-center justify-center text-slate-500">
                                    <svg class="w-12 h-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2zM14 3v5h5"></path></svg>
                                    <p class="text-sm font-medium">Your generated content will appear here</p>
                                </div>
                            </code></pre>
                        </div>
                    </div>

                    <div class="f2e-tab-content hidden flex-1 p-6" id="preview-tab">
                        <div class="f2e-preview-container border border-slate-200 rounded-xl bg-slate-50 overflow-hidden h-[500px] flex flex-col shadow-inner">
                            <div class="f2e-preview-toolbar bg-white px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Live Render</span>
                                <button id="f2e-refresh-preview" class="text-[10px] font-bold px-3 py-1 bg-slate-100 rounded-full text-slate-600 hover:bg-indigo-600 hover:text-white transition-all">RELOAD</button>
                            </div>
                            <div class="f2e-preview-content flex-1 bg-white">
                                <iframe id="f2e-preview-frame" class="w-full h-full border-0"></iframe>
                            </div>
                        </div>
                    </div>

                    <div id="f2e-loading" class="f2e-loading-container f2e-hidden absolute inset-0 bg-white/90 backdrop-blur-sm z-50 flex flex-col items-center justify-center py-12">
                        <div class="relative">
                            <div class="f2e-loading-spinner animate-spin rounded-full h-16 w-16 border-[3px] border-slate-100 border-t-indigo-600"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="h-2 w-2 bg-indigo-600 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                        <div class="f2e-loading-content mt-6 text-center">
                            <h4 id="f2e-iteration-heading" class="text-xl font-bold text-slate-900">Crafting Excellence</h4>
                            <p id="f2e-iteration-status" class="text-slate-500 text-sm mt-1">Analyzing context and generating output...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="f2e-disclaimer mt-10 p-5 bg-indigo-50/50 border border-indigo-100 rounded-2xl text-indigo-700/80 text-sm flex items-start gap-4">
        <div class="bg-indigo-600 p-1 rounded-md text-white">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 13V9M12 17V16.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke-linecap="round" stroke-linejoin="round" /></svg>
        </div>
        <p class="leading-relaxed">
            <strong class="text-indigo-900 block mb-1">Privacy Focused Generation</strong>
            Your inputs and generated content are processed in real-time and not stored on our servers. Refreshing the page will clear all current session data.
        </p>
    </div>

    <div id="f2e-advanced-modal" class="f2e-modal fixed inset-0 z-[1000] bg-slate-900/80 backdrop-blur-md hidden items-center justify-center p-4 transition-all" aria-modal="true" role="dialog">
        <div class="f2e-modal-content bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-hidden flex flex-col transform transition-all border border-slate-200">
            <div class="f2e-modal-header flex justify-between items-center p-6 border-b border-slate-100">
                <h5 class="text-xl font-black tracking-tight text-slate-900">Advanced Parameters</h5>
                <button type="button" class="f2e-close-modal text-slate-400 hover:text-slate-900 transition-colors p-2 bg-slate-50 rounded-full">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6L18 18"/></svg>
                </button>
            </div>
            
            <div class="f2e-modal-body p-8 overflow-y-auto space-y-6">
                <div class="f2e-form-group">
                    <label for="f2e-target-keyword" class="block text-sm font-bold text-slate-700 mb-2">Target SEO Keyword</label>
                    <input type="text" id="f2e-target-keyword" placeholder="e.g. React Performance" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-indigo-50 outline-none text-sm font-medium">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="f2e-form-group">
                        <label for="f2e-audience-level" class="block text-sm font-bold text-slate-700 mb-2">Audience</label>
                        <select id="f2e-audience-level" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none">
                            <option value="">Any Level</option>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>
                    <div class="f2e-form-group">
                        <label for="f2e-language" class="block text-sm font-bold text-slate-700 mb-2">Language</label>
                        <select id="f2e-language" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none">
                            <option value="English">English</option>
                            <option value="Spanish">Spanish</option>
                            <option value="French">French</option>
                            <option value="German">German</option>
                        </select>
                    </div>
                </div>

                <div class="f2e-form-group">
                    <label for="f2e-desired-tone" class="block text-sm font-bold text-slate-700 mb-2">Voice & Tone</label>
                    <select id="f2e-desired-tone" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none">
                        <option value="">Select Tone</option>
                        <option value="Tutorial/Instructive">Tutorial</option>
                        <option value="Opinion/Thought Leadership">Thought Leader</option>
                        <option value="Casual/Informal">Casual</option>
                        <option value="Formal/Academic">Formal</option>
                    </select>
                </div>

                <div class="f2e-form-group">
                    <label for="f2e-required-length" class="block text-sm font-bold text-slate-700 mb-2">Content Length</label>
                    <select id="f2e-required-length" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none">
                        <option value="">Auto Detect</option>
                        <option value="Short (approx 300 words)">Short (300w)</option>
                        <option value="Medium (approx 500-800 words)">Medium (800w)</option>
                        <option value="Long (approx 1000-1500 words)">Long (1500w)</option>
                    </select>
                </div>

                <div class="f2e-form-group">
                    <label for="f2e-call-to-action" class="block text-sm font-bold text-slate-700 mb-2">Call to Action (CTA)</label>
                    <textarea id="f2e-call-to-action" placeholder="What should the reader do next?" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl h-24 text-sm outline-none"></textarea>
                </div>
            </div>
            
            <div class="f2e-modal-footer p-6 border-t border-slate-100 bg-slate-50/50">
                <button type="button" class="f2e-close-modal f2e-button f2e-button-primary w-full py-4 text-base font-bold rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">
                    Apply Parameters
                </button>
            </div>
        </div>
    </div>
</div>