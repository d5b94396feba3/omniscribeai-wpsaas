<?php
namespace FigmaToElementor;

use Spatie\Browsershot\Browsershot;
use Exception;

defined('ABSPATH') || exit;

class SiteURL_Handler {

    public function __construct() {
        // Initialize any required dependencies
    }


    /**
     * Capture website screenshot and return as base64
     */
    public function take_screenshot_from_site_url_without_delay($siteURL) {
        try {
            // Validate URL
            if (!filter_var($siteURL, FILTER_VALIDATE_URL)) {
                throw new Exception("Invalid website URL provided");
            }

            // Configure Browsershot
            $browsershot = new Browsershot();
            
            // Capture screenshot and get as base64
            $base64Image = $browsershot
                ->setUrl($siteURL)
                ->setOption('fullPage', true)
                ->deviceScaleFactor(2)    // Higher quality rendering
                ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
                ->waitUntilNetworkIdle(true)  // Strict network idle detection
                ->setDelay(10000)
                ->dismissDialogs()             // Dismiss any popups/dialogs
                ->setScreenshotType('png')
                ->blockDomains([
                    'cookie', 'consent', 'cookiebot.com', 'cookielaw.org',
                    'trustarc.com', 'onetrust.com', 'gdpr', 'privacypolicy',
                    'termsfeed.com', 'iubenda.com'    
                ])
                ->base64Screenshot();

            return $base64Image;

        } catch (Exception $e) {
            error_log("Screenshot capture failed: " . $e->getMessage());
            return false;
        }
    }

    
    /**
     * Capture website screenshot and extract site data
     *
     * @param string $siteURL The URL of the website to screenshot and extract data from.
     * @return array|false Structured array of site data on success, false on failure.
     */
    public function take_screenshot_from_site_url_with_data($siteURL) {
        try {
            if (!filter_var($siteURL, FILTER_VALIDATE_URL)) {
                throw new \Exception("Invalid website URL provided: " . $siteURL);
            }

            $console_msg = "Browsing the website URL for capturing a screenshot, and website data";

            // 1. Initial message: Browse
            $this->update_progress("Browse the website URL...");
    
            // Initialize data structure
            $site_datas = [
                'base64Image' => '',
                'site_text' => '',
                'site_images' => [],
                'site_videos' => []
            ];
    
            // Create storage directory if it doesn't exist
            // F2E_PLUGIN_DIR should be defined elsewhere, e.g., in your main plugin file
            $storage_dir = F2E_PLUGIN_DIR . 'storage/screenshots/';
            if (!file_exists($storage_dir)) {
                // Use wp_mkdir_p for WordPress-safe directory creation
                if (!wp_mkdir_p($storage_dir)) {
                    throw new \Exception("Failed to create storage directory: " . $storage_dir);
                }
            }
    
            // Generate unique filename for the screenshot (even if not saved, useful for logging)
            $filename = 'screenshot_' . md5($siteURL . time()) . '.png';
            $filepath = $storage_dir . $filename; // Retained for consistency, though base64Screenshot doesn't use it
    
            // Configure Browsershot with bypass techniques
            $browsershot = Browsershot::url($siteURL)
                ->fullPage()              // Capture the entire scrollable page
                ->windowSize(1920, 1080)  // Standard desktop viewport size
                ->deviceScaleFactor(1) // Or 1.5 1 - 2 for a good balance
                ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
                ->waitUntil('load')       // Relaxed wait condition to 'load' event, less strict than network idle
                ->timeout(120000)         // Overall process timeout to 120 seconds (2 minutes)
                ->ignoreHttpsErrors()     // Bypass HTTPS errors (e.g., self-signed certs)
                ->disableGpu()            // Disable GPU acceleration (can help stability in some environments)
                ->noSandbox();

            // 2. Message: Extracting data (before evaluate)
            $this->update_progress("Extracting data from the website...");
        
            // JavaScript to collect page complexity metrics AND extract site content
            // IMPORTANT: The return value must be a JSON string for PHP to parse it correctly.
            $jsDataExtraction = <<<JS
                (function() {

                    // Extract all visible text content
                    const textContent = (document.body.innerText || document.body.textContent || '')
                        .replace(/\\s+/g, ' ')
                        .trim()
                        .substring(0, 5000); // Limit to 5000 chars to avoid excessively large data

                    // Extract images with alt text and src
                    const images = Array.from(document.querySelectorAll('img'))
                        .filter(img => img.src && img.src.startsWith('http')) // Filter out data URIs or relative paths initially
                        .map((img, index) => ({
                            top_to_bottom_position: index,
                            image_alt: img.alt || '',
                            image_link: img.src
                        }));

                    // Extract videos with src (checking video tags and common iframe embeds like YouTube/Vimeo)
                    const videos = Array.from(document.querySelectorAll('video, iframe[src*="youtube"], iframe[src*="vimeo"]'))
                        .map((el, index) => {
                            const src = el.src || el.getAttribute('data-src') || '';
                            // For embedded videos (iframes), the actual video title is often not in the DOM.
                            // It's usually fetched via an API call to YouTube/Vimeo.
                            // We can only get the 'title' attribute of the iframe itself, if it's provided by the embedding site.
                            const alt = el.alt || el.title || el.getAttribute('aria-label') || '';
                            return {
                                top_to_bottom_position: index,
                                video_alt: alt, // This will be empty if the iframe's title/alt/aria-label is not set
                                video_link: src
                            };
                        })
                        .filter(video => video.video_link); // Filter out entries without a valid link

                    return JSON.stringify({ // Stringify the entire object to return a JSON string
                        elementCount: document.querySelectorAll('*').length,
                        scriptCount: document.querySelectorAll('script[src]').length,
                        iframeCount: document.querySelectorAll('iframe').length,
                        scrollHeight: document.body.scrollHeight,
                        siteText: textContent,
                        siteImages: images,
                        siteVideos: videos
                    });
                })();
            JS;
    
            // Execute JavaScript and get metrics and content data in PHP
            // This will return a JSON string from the browser.
            $extractedDataJson = $browsershot->evaluate($jsDataExtraction);
            
            // Decode the JSON string into a PHP associative array
            $extractedData = json_decode($extractedDataJson, true);
    
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($extractedData)) {
                // Fallback if metrics cannot be retrieved or decoded
                error_log("Failed to retrieve or decode page metrics/data. Using default delay and empty content. JSON Error: " . json_last_error_msg());
                $calculatedDelay = 5000; // Default to 5 seconds if data extraction fails
                $siteText = '';
                $siteImages = [];
                $siteVideos = [];

                $this->update_progress("Warning: Failed to extract detailed data. Using default settings."); // Update progress on partial failure

            } else {

                // Calculate dynamic delay based on page complexity metrics
                $delay = 1000; // Base 3 seconds
                $delay += ($extractedData['elementCount'] ?? 0) / 100; // 10ms per 1000 elements
                $delay += ($extractedData['scriptCount'] ?? 0) * 500; // 500ms per script
                $delay += ($extractedData['iframeCount'] ?? 0) * 1000; // 1000ms per iframe
                $delay += min(5000, ($extractedData['scrollHeight'] ?? 0) / 100); // 10ms per 1000px height, capped at 5s added
    
                // Cap the delay between 3 and 20 seconds (adjust as needed)
                $calculatedDelay = max(3000, min(20000, (int)$delay));
    
                error_log("Auto-delay calculated: {$calculatedDelay}ms (Elements: " . ($extractedData['elementCount'] ?? 'N/A') . ", Scripts: " . ($extractedData['scriptCount'] ?? 'N/A') . ", Iframes: " . ($extractedData['iframeCount'] ?? 'N/A') . ", Height: " . ($extractedData['scrollHeight'] ?? 'N/A') . "px)");
    
                // Assign extracted content data
                $siteText = $extractedData['siteText'] ?? '';
                $siteImages = $extractedData['siteImages'] ?? [];
                $siteVideos = $extractedData['siteVideos'] ?? [];
            }
    
            // Apply the calculated delay
            $browsershot->setDelay($calculatedDelay);

            // 4. Message: Taking screenshot
            $this->update_progress("Taking screenshot of the website...");

            // Complete the screenshot capture and get as base64
            $base64Image = $browsershot
                ->setScreenshotType('jpeg')    // Set image type to JPEG
                ->blockDomains([ // Re-added for general blocking of known tracking/consent domains
                    'cookie', 'consent', 'cookiebot.com', 'cookielaw.org',
                    'trustarc.com', 'onetrust.com', 'gdpr', 'privacypolicy',
                    'termsfeed.com', 'iubenda.com'    
                ])
                ->base64Screenshot(); // Get the screenshot directly as Base64
    
            // Prepare the final structured data array
            $site_datas = [
                'base64Image' => $base64Image,
                'site_texts' => $siteText,
                'site_images' => $siteImages,
                'site_videos' => $siteVideos,
            ];

            // $site_datas = [
            //     'base64Image' => $base64Image,
            //     'site_texts' => '',
            //     'site_images' => [],
            //     'site_videos' => [],
            // ];
    
            // Log the screenshot process completion (for debugging context)
            // error_log('Screenshot and data extraction process completed for: ' . print_r($site_datas, true));
            error_log('Screenshot and data extraction process completed for: ' . $siteURL);
            return $site_datas;
    
        } catch (\Exception $e) {
            error_log("Screenshot capture and data extraction failed: " . $e->getMessage());
            return false;
        }
    }


    private function update_progress($msg) {
        
        $progress_data = [
            'total' => "",
            'current' => "",
            'source' => "Website URL",
            'message' => $msg,
            'timestamp' => time()
        ];
        try {
            set_transient(
                'f2e_progress_' . get_current_user_id(),
                $progress_data,
                60 // Expire after 1 minute
            );
        } catch (\Exception $e) {
            error_log("Failed to set progress transient: " . $e->getMessage());
        }

        // error_log('Progress updated: ' . print_r($progress_data, true));

    }

    private function clear_progress_from_transient() {
        try {
            $progress_key = 'f2e_progress_' . get_current_user_id();
            delete_transient($progress_key);
        } catch (\Exception $e) {
            error_log("Failed to clear progress transient: " . $e->getMessage());
        }
    }



}

