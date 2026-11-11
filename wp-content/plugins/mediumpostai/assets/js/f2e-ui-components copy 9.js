/**
 * f2e-ui-components.js
 * Handles UI interactions, preview, loader, metadata, copy, and download logic.
 * Requires: f2e-core.js (for F2E.Elements, F2E.Utils, F2E.State, F2E.Constants)
 */
window.F2E = window.F2E || {};
(function($) {
"use strict";
const Elements = window.F2E.Elements || {};
const $generateButton = Elements.$generateButton;
const $description = Elements.$description;
const $copyButton = Elements.$copyButton;
const $downloadButton = Elements.$downloadButton;
const $clearButton = Elements.$clearButton;
const $resultCode = Elements.$resultCode;
const $loading = Elements.$loading;
const { i18n } = window.F2E.Constants || {};
F2E.State = F2E.State || {};
F2E.State.isCopying = false;
F2E.State.lastCopyTimestamp = 0;
const COPY_THROTTLE_DELAY = 500;
// --- START NEW CODE: A minimal Markdown to HTML converter for Dev.io Post preview ---
function markdownToHtml(markdown) {
	if (!markdown) return '';
	let html = markdown;
	html = html.replace(/```(.*)\n([\s\S]*?)```/g, function(match, lang, code) {
		const escapedCode = code.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/</g, '>').replace(/>/g, '>');
		const languageClass = lang.trim() ? `language-${lang.trim()}` : '';
		return `<pre class="f2e-md-pre"><code class="${languageClass}">${escapedCode.trim()}</code></pre>`;
	});
	html = html.replace(/^###### (.*$)/gim, '<h6>$1</h6>');
	html = html.replace(/^##### (.*$)/gim, '<h5>$1</h5>');
	html = html.replace(/^#### (.*$)/gim, '<h4>$1</h4>');
	html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
	html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
	html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
	html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
	html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
	let blocks = html.split(/\n\s*\n/);
	blocks = blocks.map(block => {
		let line = block.trim();
		if (!line) return '';
		if (line.match(/^\s*<pre|<h/i)) return line;
		if (line.match(/^[\*\-\+] /m)) {
			let listHtml = '<ul>\n';
			line.split('\n').forEach(listItem => {
				if (listItem.match(/^[\*\-\+] /)) {
					listHtml += `<li>${listItem.replace(/^[\*\-\+] /, '').trim()}</li>\n`;
				}
			});
			listHtml += '</ul>';
			return listHtml;
		}
		return `<p>${line.replace(/\n/g, '<br>')}</p>`;
	});
	return blocks.join('\n\n').replace(/<p>\s*<\/p>/g, '');
}
// --- END NEW CODE ---
// --- Loader Functions (Original, Unchanged) ---
const Loader = {
	createLoader: function() {
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
	},
	startLoader: function() {
		Loader.clearLoader();
		Loader.createLoader();
		const updateInterval = 100;
		F2E.State.currentProgress = 0;
		F2E.State.targetProgress = 100;
		const $progressBar = $('.f2e-progress-bar');
		const $percentage = $('.f2e-loader-percentage');
		const $currentStep = $('.f2e-current-step');
		const increment = 0.5;
		F2E.State.loaderInterval = setInterval(() => {
			if (F2E.State.currentProgress < F2E.State.targetProgress) {
				F2E.State.currentProgress += increment;
				if (F2E.State.currentProgress > F2E.State.targetProgress) {
					F2E.State.currentProgress = F2E.State.targetProgress;
				}
				const progressPercent = Math.min(100, Math.round(F2E.State.currentProgress));
				$progressBar.css('width', `${progressPercent}%`);
				$percentage.text(`${progressPercent}%`);
				if (progressPercent < 25) { $currentStep.text('Processing your description and requirements...'); }
				else if (progressPercent < 50) { $currentStep.text(`Generating ${F2E.Utils.getContentTypeDisplayName()} content...`); }
				else if (progressPercent < 75) { $currentStep.text('Formatting and structuring the article...'); }
				else { $currentStep.text('Finalizing content and preparing output...'); }
				if (progressPercent >= 100) {
					clearInterval(F2E.State.loaderInterval);
					$currentStep.text('Generation complete! Finalizing output...');
				}
			}
		}, updateInterval);
	},
	clearLoader: function() {
		if (F2E.State.loaderInterval) {
			clearInterval(F2E.State.loaderInterval);
			F2E.State.loaderInterval = null;
		}
		$('.f2e-progress-loader').remove();
		F2E.State.currentProgress = 0;
		F2E.State.targetProgress = 0;
	}
};
// --- Preview Functions (MODIFIED TO SUPPORT DEV.IO PREVIEW) ---
const Preview = {
	updateLivePreview: function() {
		let generatedContent = $resultCode.text().trim();
		const contentType = F2E.Utils.getContentType();
		const metadata = F2E.State.lastGeneratedMetadata;
		const featuredImage = metadata.image || '';
		const getContentTypeDisplayName = F2E.Utils.getContentTypeDisplayName;
		const imageUrl = F2E.Utils.getFeaturedImageUrl(featuredImage);
		if (generatedContent && generatedContent !== 'No content generated yet.') {
			let previewHtml = '';
			let contentToDisplay = generatedContent.trim();
			if (contentType === 'devio') {
				contentToDisplay = markdownToHtml(contentToDisplay);
			} else {
				contentToDisplay = contentToDisplay.replace(/```html|```/g, '').trim();
			}
			if (imageUrl) {
				const imageAlt = metadata.title || 'Featured image concept';
				previewHtml += `
					<div style="margin-bottom: 20px;">
						<img src="${imageUrl}" alt="${imageAlt}" style="width: 100%; height: auto; display: block; border-radius: 8px;">
					</div>
				`;
			}
			previewHtml += contentToDisplay;
			const fullHtml = `
				<!DOCTYPE html>
				<html>
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<style>
						body {
							font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
							line-height: 1.6;
							padding: 20px;
							margin: 0;
							background: white;
						}
						.content-post { max-width: 740px; margin: 0 auto; }
						img { max-width: 100%; height: auto; }
						.devio-post {
							max-width: 680px;
							margin: 0 auto;
							font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
						}
						.medium-post {
							max-width: 740px;
							margin: 0 auto;
							font-family: 'Medium Content Serif', Georgia, Cambria, 'Times New Roman', Times, serif;
						}
						.devio-post h1, .devio-post h2, .devio-post h3, .devio-post h4, .devio-post h5, .devio-post h6 {
							font-weight: bold;
							margin-top: 1.5em;
							margin-bottom: 0.5em;
						}
						.devio-post h1 { font-size: 2em; }
						.devio-post h2 { font-size: 1.5em; }
						.devio-post h3 { font-size: 1.25em; }
						.devio-post p {
							margin-top: 1em;
							margin-bottom: 1em;
						}
						.devio-post ul {
							padding-left: 20px;
							margin-top: 1em;
							margin-bottom: 1em;
						}
						.devio-post li {
							margin-bottom: 0.5em;
						}
						.devio-post .f2e-md-pre {
							background-color: #f6f8fa;
							border-radius: 6px;
							padding: 16px;
							overflow-x: auto;
							font-size: 0.9em;
						}
						.devio-post .f2e-md-pre code {
							display: block;
							font-family: 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
							white-space: pre;
						}
						.devio-post strong, .devio-post em {
							font-weight: inherit;
						}
						.devio-post strong { font-weight: bolder; }
						.devio-post em { font-style: italic; }
					</style>
				</head>
				<body>
				<div class="content-post ${contentType === 'devio' ? 'devio-post' : 'medium-post'}">
					${previewHtml}
				</div>
				</body>
				</html>
			`;
			const iframe = document.getElementById('f2e-preview-frame');
			const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
			iframeDoc.open();
			iframeDoc.write(fullHtml);
			iframeDoc.close();
		} else {
			const iframe = document.getElementById('f2e-preview-frame');
			const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
			iframeDoc.open();
			iframeDoc.write(`
				<!DOCTYPE html>
				<html>
				<body style="display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; color: #666;">
					<div style="text-align: center;">
						<h3>No content to preview</h3>
						<p>Generate a ${getContentTypeDisplayName()} first to see the live preview.</p>
					</div>
				</body>
				</html>
			`);
			iframeDoc.close();
		}
	},
	loadPrism: function() {
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
};
// --- Metadata Functions (MODIFIED SECTION) ---
const Metadata = {
	createMetadataPanel: function(metadata) {
		$('.f2e-metadata-panel').remove();
		const tagsArray = Array.isArray(metadata.tags) ? metadata.tags : [];
		const featuredImage = metadata.image || '';
		const imageUrl = F2E.Utils.getFeaturedImageUrl(featuredImage);
		const imageTitleSlug = (metadata.title || 'featured-image').replace(/[^a-z0-9]/gi, '_').toLowerCase();
		
		// NEW: Check if the 'image' field is a URL or a concept.
		const isImageGenerated = featuredImage.startsWith('http') || featuredImage.startsWith('/');

		const metadataPanel = `
			<div class="f2e-metadata-panel mt-6 border border-[var(--border-gray-200)] rounded-lg bg-[var(--bg-gray-50)] overflow-hidden transition-all duration-300">
				<div class="f2e-metadata-header bg-white px-4 py-3 border-b border-[var(--border-gray-200)] flex items-center justify-between cursor-pointer hover:bg-[var(--bg-gray-50)] transition-colors">
					<div class="flex items-center gap-3">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-[var(--blue-600)]">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2"/>
							<polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/>
							<line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2"/>
							<line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2"/>
							<polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2"/>
						</svg>
						<h4 class="font-semibold text-[var(--text-gray-800)]">Content Details</h4>
					</div>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="f2e-metadata-chevron text-[var(--text-gray-500)] transition-transform duration-200 rotate-180">
						<path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<div class="f2e-metadata-content px-4 py-4 space-y-4 bg-white" style="display: block;">
					${metadata.title ? `
					<div class="f2e-metadata-item">
						<label class="f2e-metadata-label block text-sm font-medium text-[var(--text-gray-600)] mb-1">Title</label>
						<div class="f2e-metadata-value text-[var(--text-gray-800)] font-medium">${metadata.title}</div>
					</div>
					` : ''}
					${tagsArray.length > 0 ? `
					<div class="f2e-metadata-item">
						<label class="f2e-metadata-label block text-sm font-medium text-[var(--text-gray-600)] mb-2">Tags</label>
						<div class="f2e-tags-container flex flex-wrap gap-2">
							${tagsArray.map(tag => `
								<span class="f2e-tag px-3 py-1 bg-[var(--blue-100)] text-[var(--blue-800)] rounded-full text-xs font-medium border border-[var(--blue-200)]">
									${tag}
								</span>
							`).join('')}
						</div>
					</div>
					` : ''}
					${metadata.image ? `
					<div class="f2e-metadata-item">
						<label class="f2e-metadata-label block text-sm font-medium text-[var(--text-gray-600)] mb-2">Featured Image Concept</label>
						<div class="f2e-image-concept bg-[var(--bg-gray-100)] p-4 rounded-lg border border-[var(--border-gray-200)] flex flex-col gap-4">
							<div class="flex items-start gap-3">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-[var(--blue-600)] mt-0.5 flex-shrink-0">
									<rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
									<circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
									<polyline points="21,15 16,10 5,21" stroke="currentColor" stroke-width="2"/>
								</svg>
								<p class="text-[var(--text-gray-700)] text-sm leading-relaxed">${metadata.image}</p>
							</div>
							${imageUrl ? `
							<div class="f2e-image-preview-wrapper flex flex-col gap-3">
								<img src="${imageUrl}" alt="${metadata.title || 'Featured Image Concept'}" class="w-full h-auto max-h-48 object-cover rounded-md border border-[var(--border-gray-300)]"/>
								
								<div class="f2e-image-actions-new flex flex-wrap gap-2">
									${isImageGenerated ? `
										<button class="f2e-refine-image-button f2e-button f2e-button-primary py-2 px-4 rounded-lg text-sm font-medium flex items-center justify-center gap-2 shadow-sm hover:shadow-md transition-all duration-200">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
											</svg>
											Refine
										</button>
										<button class="f2e-regenerate-image-button f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] flex items-center justify-center gap-2 shadow-sm transition-colors duration-200 text-sm">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
											</svg>
											Regenerate
										</button>
									` : ''}
									<button class="f2e-open-image-modal f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center justify-center gap-2 shadow-sm transition-colors duration-200 text-sm" data-image-url="${imageUrl}" data-image-title-slug="${imageTitleSlug}" aria-label="View and Download Image">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
										</svg>
										View Image
									</button>
									<a href="${imageUrl}" download="${imageTitleSlug}-concept.jpg"
									class="f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center justify-center gap-2 shadow-sm transition-colors duration-200 text-sm" aria-label="Download Image Directly">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
										</svg>
										Download
									</a>
								</div>
							</div>
							` : ''}
						</div>
					</div>
					` : ''}
					<div class="f2e-metadata-actions pt-2 border-t border-[var(--border-gray-200)]">
						<button class="f2e-copy-metadata f2e-button f2e-button-secondary py-2 px-4 rounded-lg border border-[var(--gray-300)] text-[var(--text-gray-700)] hover:bg-[var(--bg-gray-100)] focus:outline-none focus:ring-2 focus:ring-[var(--gray-300)] flex items-center gap-2 transition-colors duration-200 text-sm">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M8 16H6C4.89543 16 4 15.1046 4 14V6C4 4.89543 4.89543 4 6 4H14C15.1046 4 16 4.89543 16 6V8M10 20H18C19.1046 20 20 19.1046 20 18V10C20 8.89543 19.1046 8 18 8H10C8.89543 8 8 8.89543 8 10V18C8 19.1046 8.89543 20 10 20Z" stroke="currentColor" stroke-width="2"/>
							</svg>
							Copy All Details
						</button>
					</div>
				</div>
			</div>
		`;
		$('.f2e-code-container').after(metadataPanel);
		$('.f2e-metadata-header').on('click', function() {
			const $content = $(this).next('.f2e-metadata-content');
			const $chevron = $(this).find('.f2e-metadata-chevron');
			$content.slideToggle(200);
			$chevron.toggleClass('rotate-180');
		});
		$('.f2e-copy-metadata').on('click', function() {
			const metadataText = `
				Title: ${metadata.title || 'Not specified'}
				Tags: ${tagsArray.length > 0 ? tagsArray.join(', ') : 'No tags'}
				Image Concept: ${metadata.image || 'Not specified'}
			`.trim();
			navigator.clipboard.writeText(metadataText)
				.then(() => Actions.showCopiedMessage($(this)))
				.catch(err => {
					console.log('Failed to copy metadata:', err);
					alert('Failed to copy metadata to clipboard');
				});
		});
		
		// NEW: Re-attach image action handlers after metadata panel is rebuilt
		if (window.F2E.ImageActions && window.F2E.ImageActions.attachImageActionHandlers) {
			window.F2E.ImageActions.attachImageActionHandlers();
		}
	}
};
// --- Modal Functions (Original, Unchanged) ---
const Modal = {
	initImageModal: function() {
		if ($('#f2e-image-modal').length === 0) {
			const modalHTML = `
				<div id="f2e-image-modal" class="f2e-image-modal fixed inset-0 z-[1000] bg-black bg-opacity-75 hidden items-center justify-center p-4 transition-opacity duration-300" aria-modal="true" role="dialog" tabindex="-1">
					<div class="f2e-modal-content bg-[var(--bg-white)] rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col transform transition-transform duration-300 scale-95" style="z-index: 1001;">
						<div class="f2e-modal-header flex justify-between items-center p-4 border-b border-[var(--border-gray-200)]">
							<h5 class="text-xl font-semibold text-[var(--text-gray-800)]">Featured Image Preview</h5>
							<button type="button" class="f2e-close-modal text-[var(--text-gray-500)] hover:text-[var(--text-gray-800)] transition-colors" aria-label="Close modal">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6L18 18"/></svg>
							</button>
						</div>
						<div class="f2e-modal-body p-4 flex-1 overflow-y-auto flex flex-col items-center justify-center bg-[var(--bg-gray-100)]">
							<img id="f2e-modal-image" src="" alt="Featured Image" class="max-w-full h-auto max-h-[70vh] object-contain rounded-lg shadow-lg"/>
						</div>
						<div class="f2e-modal-footer flex justify-center p-4 border-t border-[var(--border-gray-200)]">
							<a id="f2e-modal-download-button" href="#" download="featured-image.jpg" class="f2e-button f2e-button-primary py-2 px-6 text-base font-medium rounded-xl flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200" aria-label="Download Image from modal">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
								</svg>
								Download Image
							</a>
						</div>
					</div>
				</div>
			`;
			$('body').append(modalHTML);
		}
		const $modal = $('#f2e-image-modal');
		const $modalImage = $('#f2e-modal-image');
		const $modalDownloadButton = $('#f2e-modal-download-button');
		const $closeButton = $('.f2e-close-modal');
		const closeModal = () => {
			$modal.removeClass('flex').addClass('hidden');
		};
		$(document).on('click', '.f2e-open-image-modal', function() {
			const imageUrl = $(this).data('image-url');
			const imageTitleSlug = $(this).data('image-title-slug');
			const filename = imageTitleSlug + '-concept.jpg';
			$modalImage.attr('src', imageUrl);
			$modalDownloadButton.attr('href', imageUrl).attr('download', filename);
			$modal.removeClass('hidden').addClass('flex');
		});
		$closeButton.on('click', closeModal);
		$modal.on('click', function(e) {
			if (e.target === this) {
				closeModal();
			}
		});
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && $modal.hasClass('flex')) {
				closeModal();
			}
		});
	}
};
// --- Action Functions (Original, Unchanged) ---
const Actions = {
	showCopiedMessage: function($button) {
		const originalHTML = $button.html();
		const tempHTML = `<span>${i18n.copied || 'Copied!'}</span>`;
		$button.html(tempHTML);
		setTimeout(() => { $button.html(originalHTML); }, 2000);
	},
	showDownloadMessage: function($button) {
		const originalHTML = $button.html();
		const tempHTML = `<span>${i18n.downloading || 'Downloading...'}</span>`;
		$button.html(tempHTML);
		setTimeout(() => { $button.html(originalHTML); }, 2000);
	},
	clearContent: function() {
		const contentTypeDisplayName = F2E.Utils.getContentTypeDisplayName();
		const confirmClear = confirm(i18n.confirm_clear_generate || `Do you want to clear the generated ${contentTypeDisplayName.toLowerCase()}?`);
		if (confirmClear) {
			$resultCode.text('No content generated yet.');
			$description.val('');
			F2E.Elements.$resultContent.addClass('f2e-hidden');
			F2E.Elements.$resultContainerActions.addClass('f2e-hidden');
			$copyButton.addClass('f2e-hidden');
			$downloadButton.addClass('f2e-hidden');
			F2E.Elements.$previewButton.addClass('f2e-hidden');
			$clearButton.addClass('f2e-hidden');
			$('.f2e-metadata-panel').remove();
			F2E.State.lastGeneratedMetadata = {};
			F2E.Preview.updateLivePreview();
			F2E.Utils.updateInputUI();
		}
	},
	clearConsole: function() {},
	copyContent: function() {
		const now = Date.now();
		if (F2E.State.isCopying || (now - F2E.State.lastCopyTimestamp < COPY_THROTTLE_DELAY)) { return; }
		F2E.State.isCopying = true;
		F2E.State.lastCopyTimestamp = now;
		const generatedContent = $resultCode.text().trim();
		const contentType = F2E.Utils.getContentType();
		if (!generatedContent || generatedContent === 'No content generated yet.') {
			alert(`No content available to copy. Please generate a ${F2E.Utils.getContentTypeDisplayName()} first.`);
			F2E.State.isCopying = false;
			return;
		}
		if (contentType === 'devio') {
			Actions.copyRawContent(generatedContent);
			F2E.State.isCopying = false;
		} else {
			Actions.copyHTMLContent(generatedContent);
			F2E.State.isCopying = false;
		}
	},
	copyRawContent: function(content) {
		navigator.clipboard.writeText(content)
			.then(() => Actions.showCopiedMessage($copyButton))
			.catch(err => {
				console.log('Clipboard copy failed:', err);
				Actions.fallbackCopyToClipboard(content);
			});
	},
	copyHTMLContent: function(htmlContent) {
		const tempDiv = document.createElement('div');
		tempDiv.style.cssText = `
			position: fixed;
			left: -9999px;
			top: 0;
			width: 740px;
			padding: 40px 20px;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			line-height: 1.6;
			background: white;
			color: #242424;
			max-width: 740px;
			margin: 0 auto;
		`;
		let cleanContent = htmlContent.replace(/```html|```/g, '').trim();
		tempDiv.innerHTML = cleanContent;
		document.body.appendChild(tempDiv);
		const range = document.createRange();
		range.selectNodeContents(tempDiv);
		const selection = window.getSelection();
		selection.removeAllRanges();
		selection.addRange(range);
		try {
			const successful = document.execCommand('copy');
			if (!successful) throw new Error('Copy command failed');
			Actions.showCopiedMessage($copyButton);
		} catch (err) {
			console.log('Fallback copy failed:', err);
			Actions.copyAsRichText(cleanContent);
		}
		selection.removeAllRanges();
		document.body.removeChild(tempDiv);
	},
	copyAsRichText: function(html) {
		const blob = new Blob([html], { type: 'text/html' });
		if (typeof ClipboardItem !== 'undefined') {
			const clipboardItem = new ClipboardItem({ 'text/html': blob });
			navigator.clipboard.write([clipboardItem])
				.then(() => Actions.showCopiedMessage($copyButton))
				.catch(err => {
					console.log('Rich text (Blob) copy failed:', err);
					const tempDiv = document.createElement('div');
					tempDiv.innerHTML = html;
					const plainText = tempDiv.textContent || tempDiv.innerText || '';
					navigator.clipboard.writeText(plainText)
						.then(() => Actions.showCopiedMessage($copyButton))
						.catch(finalErr => {
							console.log('All copy methods failed:', finalErr);
							alert('Copy failed. Please try selecting the content manually from the preview.');
						});
				});
		} else {
			const tempDiv = document.createElement('div');
			tempDiv.innerHTML = html;
			const plainText = tempDiv.textContent || tempDiv.innerText || '';
			navigator.clipboard.writeText(plainText)
				.then(() => Actions.showCopiedMessage($copyButton))
				.catch(finalErr => {
					console.log('All copy methods failed:', finalErr);
					alert('Copy failed. Please try selecting the content manually from the preview.');
				});
		}
	},
	fallbackCopyToClipboard: function(text) {
		const $temp = $('<textarea>');
		$('body').append($temp);
		$temp.val(text).select();
		document.execCommand('copy');
		$temp.remove();
		Actions.showCopiedMessage($copyButton);
	},
	downloadContent: function() {
		const generatedContent = $resultCode.text().trim();
		const contentType = F2E.Utils.getContentType();
		if (!generatedContent || generatedContent === 'No content generated yet.') {
			alert(`No content available to download. Please generate a ${F2E.Utils.getContentTypeDisplayName().toLowerCase()} first.`);
			return;
		}
		if (contentType === 'devio') {
			Actions.downloadRawContent(generatedContent, contentType);
		} else {
			Actions.downloadGeneralContent(generatedContent, contentType);
		}
	},
	downloadRawContent: function(content, contentType) {
		const metadata = F2E.State.lastGeneratedMetadata || {};
		const tagsArray = Array.isArray(metadata.tags) ? metadata.tags : [];
		const featuredImage = metadata.image || '';
		const imageUrl = F2E.Utils.getFeaturedImageUrl(featuredImage);

		// Build the YAML Frontmatter (Markdown metadata)
		let frontmatter = '---\n';
		frontmatter += `title: ${metadata.title || 'Untitled Post'}\n`;

		if (tagsArray.length > 0) {
		    frontmatter += 'tags:\n';
		    tagsArray.forEach(tag => {
		        frontmatter += `  - ${tag}\n`;
		    });
		}

		let imageMarkdown = '';
		if (imageUrl) {
		    // Add the image URL to frontmatter
		    frontmatter += `cover_image: ${imageUrl}\n`;
		    // Also include the image as a standard Markdown image tag right after the frontmatter
		    imageMarkdown = `\n![Featured Image: ${featuredImage || metadata.title || ''}](${imageUrl})\n\n`;
		}

		frontmatter += '---\n';

		// Combine frontmatter, image (if applicable), and original content
		const contentWithMetadata = frontmatter + imageMarkdown + content;
		const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
		const contentTypeName = contentType === 'devio' ? 'Devio-Post' : 'Post';
		const filename = `${contentTypeName}-${timestamp}.md`;
		const blob = new Blob([contentWithMetadata], { type: 'text/markdown' });
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		URL.revokeObjectURL(url);
		Actions.showDownloadMessage($downloadButton);
	},
	downloadGeneralContent: function(htmlContent, contentType) {
		const generatedContent = htmlContent.trim();
		if (!generatedContent || generatedContent === 'No post generated yet.') {
			alert('No content available to download. Please generate a post first.');
			return;
		}
		let cleanContent = generatedContent.replace(/```html|```/g, '').trim();
		const wordHtml = Actions.createWordCompatibleHTML(cleanContent, contentType);
		contentType = contentType === "x-twitter" ? "X" : Actions.ucFirst(contentType);
		const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
		const filename = `${contentType}-Post-${timestamp}.docx`;
		const blob = new Blob([wordHtml], { type: 'text/html' });
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		URL.revokeObjectURL(url);
		Actions.showDownloadMessage($downloadButton);
	},
	createWordCompatibleHTML: function(htmlContent, contentType) {
		let title = "";
		if (contentType === 'devio'){title="Dev.io Post";}
		else if (contentType === 'medium'){title="Medium Post";}
		else if (contentType === 'x-twitter'){title="X Post";}
		else if (contentType === 'linkedin'){title="Linkedin Post";}
		else if (contentType === 'facebook'){title="Facebook Post";}
		else if (contentType === 'website'){title="Website Post";}
		// --- START NEW CODE: Image and Tags inclusion for DOCX ---
		const metadata = F2E.State.lastGeneratedMetadata || {};
		const tagsArray = Array.isArray(metadata.tags) ? metadata.tags : [];
		const featuredImage = metadata.image || '';
		const imageUrl = F2E.Utils.getFeaturedImageUrl(featuredImage);

		let WORD_IMAGE_TAG = '';
		if (imageUrl) {
		    WORD_IMAGE_TAG = `
		    <div style="margin-bottom: 30pt; border: 1pt solid #ccc; padding: 10pt; background: #f9f9f9;">
		        <p style="text-align: center; margin-top: 20pt;"><img src="${imageUrl}" alt="Featured Image" style="width: 100%; max-width: 600px; height: auto; display: block; margin: 0 auto; border: 1px solid #ddd;"/></p>
		    </div>
		    `;
		}

		let WORD_TAGS_META = '';
		if (tagsArray.length > 0 || metadata.title) {
		    WORD_TAGS_META = `
		    <div style="margin-top: 40pt; padding-top: 15pt; border-top: 2pt solid #000000;">
		        <h3 style="margin-bottom: 12pt; margin-top: 0; color: #555;">Metadata and Tags</h3>
		        ${metadata.title ? `<p><strong>Title</strong>: ${metadata.title}</p>` : ''}
		        ${tagsArray.length > 0 ? `<p><strong>Tags</strong>: ${tagsArray.join(', ')}</p>` : ''}
		        <p style="margin-bottom: 0;">**Image URL**: <a href="${imageUrl}">${imageUrl}</a></p>
		        <p style="font-size: 9pt; color: #999;"><em>Note: Image and Tags metadata is included at the top and bottom of this document for convenience. The main article content is between them.</em></p>
		    </div>
		    `;
		}
		// --- END NEW CODE ---
		
		return `<!DOCTYPE html>
			<html xmlns:o="urn:schemas-microsoft-com:office:office"
			xmlns:w="urn:schemas-microsoft-com:office:word">
			<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>${title}</title>
			<style>
			body { font-family: 'Calibri', 'Segoe UI', sans-serif; line-height: 1.5; color: #000000; margin: 1in; max-width: 8.5in; }
			h1 { font-size: 24pt; font-weight: bold; margin: 36pt 0 12pt 0; color: #000000; }
			h2 { font-size: 18pt; font-weight: bold; margin: 24pt 0 8pt 0; color: #000000; }
			h3 { font-size: 14pt; font-weight: bold; margin: 18pt 0 6pt 0; color: #000000; }
			p { font-size: 11pt; margin: 12pt 0; }
			ul, ol { margin: 12pt 0; padding-left: 36pt; }
			li { font-size: 11pt; margin: 6pt 0; }
			code { font-family: 'Consolas', 'Courier New', monospace; font-size: 10pt; background: #f5f5f5; padding: 2pt 4pt; }
			pre { font-family: 'Consolas', 'Courier New', monospace; font-size: 10pt; background: #f5f5f5; padding: 12pt; margin: 12pt 0; border: 1pt solid #ddd; white-space: pre-wrap; }
			blockquote { border-left: 3pt solid #007bff; padding-left: 12pt; margin: 12pt 0; color: #666; font-style: italic; }
			hr { border: none; border-top: 1pt solid #ccc; margin: 24pt 0; }
			strong, b { font-weight: bold; }
			em, i { font-style: italic; }
			</style>
			</head>
			<body>
			${WORD_IMAGE_TAG}
			${htmlContent}
			${WORD_TAGS_META}
		</body></html>`;
	},
	ucFirst: function(string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
	}
};
// --- Expose and Initialize (Original, Unchanged) ---
window.F2E.Loader = Loader;
window.F2E.Preview = Preview;
window.F2E.Metadata = Metadata;
window.F2E.Actions = Actions;
window.F2E.Modal = Modal;
$(document).ready(function() {
	if (window.F2E.Modal && window.F2E.Modal.initImageModal) {
		window.F2E.Modal.initImageModal();
	}
});
})(jQuery);