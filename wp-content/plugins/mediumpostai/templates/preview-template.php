<?php
/**
 * Design to Code Preview Template - Fixed Version
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure $preview_data is available, whether called directly or via shortcode
if (!isset($preview_data)) {
    // This block runs if the template is called directly (e.g., via URL with preview_id)
    $preview_id = isset($_GET['preview_id']) ? sanitize_text_field($_GET['preview_id']) : '';

    if (empty($preview_id)) {
        wp_die(__('Invalid preview link.', 'medium-post-ai'));
    }

    $preview_data = get_transient('MPAI_preview_' . $preview_id);

    if (!$preview_data || !is_array($preview_data) || !isset($preview_data['content'])) {
        wp_die(__('Preview expired or not found.', 'medium-post-ai'));
    }

    if (apply_filters('MPAI_verify_preview_user', true) && 
        get_current_user_id() !== (int) $preview_data['user_id']) {
        wp_die(__('You do not have permission to view this preview.', 'medium-post-ai'));
    }
}
// --- START: Content Preparation Logic (MOVED OUTSIDE the if (!isset($preview_data)) block) ---

// Prepare the content
$content = ''; // Initialize to an empty string to avoid undefined variable warnings
$output_type = 'html'; // Default output type

if (isset($preview_data['content'])) {
    if (is_array($preview_data['content'])) {
        $content = json_encode($preview_data['content'], JSON_PRETTY_PRINT);
        $output_type = 'json';
    } elseif (is_string($preview_data['content'])) {
        $decoded = json_decode($preview_data['content']);
        if (json_last_error() === JSON_ERROR_NONE) {
            $content = json_encode($decoded, JSON_PRETTY_PRINT);
            $output_type = 'json';
        } else {
            $content = wp_unslash($preview_data['content']);
            $output_type = isset($preview_data['output_type']) ? $preview_data['output_type'] : 'html';
            if ($output_type === 'redesign') {
                $output_type = 'html'; // Treat redesign as HTML for preview purposes
            }
        }
    }
}

// Set default output type if not specified in $preview_data, or use the determined $output_type
$output_type = isset($preview_data['output_type']) ? $preview_data['output_type'] : $output_type;

// --- END: Content Preparation Logic ---

// Enqueue styles and scripts (these can remain here as they depend on $output_type)
wp_enqueue_style(
    'f2e-preview-frontend',
    plugins_url('assets/css/preview-frontend.css', MPAI_PLUGIN_FILE),
    [],
    filemtime(MPAI_PLUGIN_DIR . 'assets/css/preview-frontend.css')
);

wp_enqueue_style(
    'f2e-preview-base',
    plugins_url('assets/css/preview-base.css', MPAI_PLUGIN_FILE),
    [],
    filemtime(MPAI_PLUGIN_DIR . 'assets/css/preview-frontend.css')
);

wp_enqueue_script('prism', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js', [], '1.29.0');
if ($output_type === 'html') {
    wp_enqueue_script('prism-html', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-html.min.js', ['prism'], '1.29.0');
} else {
    wp_enqueue_script('prism-json', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-json.min.js', ['prism'], '1.29.0');
}
wp_enqueue_style('prism-theme', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css');

wp_enqueue_style('fira-code', 'https://cdn.jsdelivr.net/npm/firacode@6.2.0/distr/fira_code.css');

wp_enqueue_script('prism-line-numbers', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/line-numbers/prism-line-numbers.min.js', ['prism'], '1.29.0');
wp_enqueue_style('prism-line-numbers', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/line-numbers/prism-line-numbers.css');

wp_enqueue_script('jquery');


echo '<!DOCTYPE html><html><head>';
echo '<meta charset="' . get_bloginfo('charset') . '">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

// // Remove WordPress default favicon
// remove_action('wp_head', 'wp_site_icon', 99);

// // Add your custom SVG favicon
// echo '<link rel="icon" href="data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\'><defs><linearGradient id=\'logo-gradient\' x1=\'0%\' y1=\'0%\' x2=\'100%\' y2=\'0%\'><stop offset=\'0%\' stop-color=\'%234F46E5\'/><stop offset=\'100%\' stop-color=\'%23EA580C\'/></linearGradient></defs><path d=\'M5 3H19C20.1046 3 21 3.89543 21 5V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3Z\' stroke=\'url(%23logo-gradient)\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/><path d=\'M9 15L15 9\' stroke=\'url(%23logo-gradient)\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/><path d=\'M10 9H9V10\' stroke=\'url(%23logo-gradient)\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/><path d=\'M15 14H14V15\' stroke=\'url(%23logo-gradient)\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>" type="image/svg+xml">';

// // Fallback for older browsers
// echo '<link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH6AkEBh0Y3v0w1QAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAAJklEQVQ4y2NgGAXDFmzatMmKAQ38//+fHqyBEdqAUTBswSgYtQAAX0YVAVp5nz4AAAAASUVORK5CYII=">';

wp_head();
echo '</head><body class="f2e-preview-body">';
?>

<div class="f2e-preview-wrapper">
    <div class="f2e-preview-container">
        <div class="f2e-preview-header">
            <div class="f2e-header-content">
                <h1>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <?php esc_html_e('3DBadgeMakerAI Code Preview', 'medium-post-ai'); ?>
                </h1>
                <div class="f2e-header-actions">
                    <div class="f2e-preview-meta">
                        <span class="f2e-meta-badge">
                            <?php echo esc_html(ucfirst($output_type)); ?>
                        </span>
                        <span class="f2e-meta-badge expiry">
                            <?php 
                            $expires_in = human_time_diff(
                                $preview_data['created_at'] + 3600, 
                                current_time('timestamp')
                            );
                            printf(esc_html__('Expires in %s', 'medium-post-ai'), $expires_in);
                            ?>
                        </span>
                    </div>
                    <a href="<?php echo esc_url(home_url('/generate-code')); ?>" class="f2e-back-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <?php esc_html_e('Back to Generator', 'medium-post-ai'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="f2e-preview-tabs">
            <nav class="f2e-tab-nav">
                <button class="f2e-tab-button active" data-tab="preview">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-monitor">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <?php esc_html_e('Preview', 'medium-post-ai'); ?>
                </button>
                <button class="f2e-tab-button" data-tab="code">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-code">
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                    </svg>
                    <?php esc_html_e('Code', 'medium-post-ai'); ?>
                </button>
                <button class="f2e-tab-button" data-tab="raw">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <?php esc_html_e('Raw', 'medium-post-ai'); ?>
                </button>
            </nav>
            
            <div class="f2e-tab-content active" id="preview-tab">
                <?php if ($output_type === 'html') : ?>
                    <div class="f2e-preview-frame-container">
                    <iframe srcdoc="<?php echo esc_attr($content); ?>" class="f2e-html-preview" sandbox="allow-same-origin allow-scripts"></iframe>                        <div class="f2e-preview-toolbar">
                            <button class="f2e-refresh-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw">
                                    <polyline points="23 4 23 10 17 10"></polyline>
                                    <polyline points="1 20 1 14 7 14"></polyline>
                                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                </svg>
                                <?php esc_html_e('Refresh', 'medium-post-ai'); ?>
                            </button>
                            <div class="f2e-device-simulator">
                                <button class="f2e-device-button active" data-device="desktop">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-monitor">
                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                        <line x1="8" y1="21" x2="16" y2="21"></line>
                                        <line x1="12" y1="17" x2="12" y2="21"></line>
                                    </svg>
                                </button>
                                <button class="f2e-device-button" data-device="tablet">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tablet">
                                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2" transform="rotate(180 12 12)"></rect>
                                        <line x1="12" y1="18" x2="12" y2="18"></line>
                                    </svg>
                                </button>
                                <button class="f2e-device-button" data-device="mobile">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-smartphone">
                                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                        <line x1="12" y1="18" x2="12" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="f2e-preview-notice">
                        <div class="f2e-notice-content">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12" y2="8"></line>
                            </svg>
                            <div>
                                <h3><?php esc_html_e('Elementor JSON Preview', 'medium-post-ai'); ?></h3>
                                <p><?php esc_html_e('This content cannot be directly previewed in this view. Please export the JSON and then import it as an Elementor template. For more details, watch this video ', 'medium-post-ai'); ?><a href="https://www.youtube.com/watch?v=dnV6wDycJVo" target="_blank" style="text-decoration: underline; color: blue;">here</a>.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="f2e-tab-content" id="code-tab">
                <div class="f2e-code-container">
                    <div class="f2e-code-toolbar">
                        <button class="f2e-copy-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            <?php esc_html_e('Copy Code', 'medium-post-ai'); ?>
                        </button>
                    </div>
                    <pre class="line-numbers"><code class="language-<?php echo esc_attr($output_type); ?>"><?php echo esc_html($content); ?></code></pre>
                </div>
            </div>
            
            <div class="f2e-tab-content" id="raw-tab">
                <div class="f2e-raw-container">
                    <div class="f2e-raw-toolbar">
                        <button class="f2e-copy-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            <?php esc_html_e('Copy Content', 'medium-post-ai'); ?>
                        </button>
                    </div>
                    <textarea class="f2e-raw-content" readonly><?php 
                        // UNCOMMENT THIS LINE:
                        echo esc_textarea($content); 
                    ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Use vanilla JS instead of jQuery to avoid conflicts
    const tabButtons = document.querySelectorAll('.f2e-tab-button');
    const tabContents = document.querySelectorAll('.f2e-tab-content');
    const rawContent = document.querySelector('.f2e-raw-content');
    const copyButtons = document.querySelectorAll('.f2e-copy-button');
    const refreshButton = document.querySelector('.f2e-refresh-button');
    const deviceButtons = document.querySelectorAll('.f2e-device-button');
    const iframe = document.querySelector('.f2e-html-preview');
    const backButton = document.querySelector('.f2e-back-button');

    // Tab switching functionality
    function switchTab(tabId) {
        // Update active tab button
        tabButtons.forEach(button => {
            button.classList.remove('active');
            if (button.dataset.tab === tabId) {
                button.classList.add('active');
            }
        });

        // Update active content
        tabContents.forEach(content => {
            content.classList.remove('active');
            if (content.id === `${tabId}-tab`) {
                content.classList.add('active');
                content.style.display = 'block';
                
                // Trigger Prism highlighting if needed
                if (tabId === 'code' && typeof Prism !== 'undefined') {
                    Prism.highlightAll();
                }
            } else {
                content.style.display = 'none';
            }
        });
    }

    // Initialize tabs
    function initTabs() {
        // alert('hi') // You can remove this alert now
        tabButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.dataset.tab;
                // alert(tabId); // You can remove this alert now
                switchTab(tabId);
            });
        });

        // Show only the active tab initially
        tabContents.forEach(content => {
            content.style.display = content.classList.contains('active') ? 'block' : 'none';
        });
    }

    // Copy functionality
    function initCopyButtons() {
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const container = this.closest('.f2e-code-container, .f2e-raw-container');
                const content = container.querySelector('code') ? container.querySelector('code').textContent : 
                              container.querySelector('textarea').value;
                
                navigator.clipboard.writeText(content).then(() => {
                    const originalHTML = this.innerHTML;
                    this.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <?php esc_html_e('Copied!', 'medium-post-ai'); ?>
                    `;
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                    }, 2000);
                });
            });
        });
    }

    // Other initialization functions
    function initRawContent() {
        if (rawContent) {
            rawContent.addEventListener('click', function() {
                this.select();
            });
        }
    }

    function initRefreshButton() {
        if (refreshButton && iframe) {
            refreshButton.addEventListener('click', function() {
                const srcdoc = iframe.getAttribute('srcdoc');
                iframe.setAttribute('srcdoc', '');
                iframe.setAttribute('srcdoc', srcdoc);
            });
        }
    }

    function initDeviceButtons() {
        deviceButtons.forEach(button => {
            button.addEventListener('click', function() {
                const device = this.dataset.device;
                const iframeContainer = document.querySelector('.f2e-preview-frame-container');
                
                deviceButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                iframeContainer.className = 'f2e-preview-frame-container ' + device;
                
                switch(device) {
                    case 'desktop':
                        iframe.style.width = '100%';
                        iframe.style.height = '100%';
                        iframe.style.transform = 'none';
                        iframe.style.margin = '0';
                        break;
                    case 'tablet':
                        iframe.style.width = '768px';
                        iframe.style.height = '1024px';
                        iframe.style.transform = 'scale(0.8)';
                        iframe.style.margin = '0 auto';
                        iframe.style.transformOrigin = 'top center';
                        break;
                    case 'mobile':
                        iframe.style.width = '375px';
                        iframe.style.height = '667px';
                        iframe.style.transform = 'scale(0.7)';
                        iframe.style.margin = '0 auto';
                        iframe.style.transformOrigin = 'top center';
                        break;
                }
            });
        });
    }

    function initBackButton() {
        if (backButton) {
            if (window.opener === null && window === window.top) {
                backButton.style.display = 'inline-flex';
            } else {
                backButton.style.display = 'none';
            }
        }
    }

    function secureIframe() {
        const iframe = document.querySelector('.f2e-html-preview');
        if (!iframe) return;

        iframe.addEventListener('load', function() {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                const iframeWindow = iframe.contentWindow;
                
                // 1. Disable all links
                iframeDoc.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Link click prevented:', link.href);
                    });
                    
                    // Remove any target="_blank"
                    link.removeAttribute('target');
                });
                
                // 2. Disable forms
                iframeDoc.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        console.log('Form submission prevented');
                    });
                });
                
                // 3. Override navigation methods
                iframeWindow.open = function() {
                    console.log('window.open() disabled in preview');
                    return null;
                };
                
                iframeWindow.addEventListener('beforeunload', function(e) {
                    e.preventDefault();
                    return e.returnValue = 'Navigation disabled in preview';
                });
                
            } catch (error) {
                console.error('Error securing iframe:', error);
            }
        });
    }
    

    // Initialize everything
    initTabs();
    initCopyButtons();
    initRawContent();
    initRefreshButton();
    initDeviceButtons();
    initBackButton();
    secureIframe();


    // Initialize Prism if available
    if (typeof Prism !== 'undefined') {
        Prism.highlightAll();
    }
});
</script>

<?php
// Output closing tags when header is disabled
echo '</body></html>';