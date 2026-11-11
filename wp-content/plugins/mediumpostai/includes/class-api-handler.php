<?php
namespace MediumPostAI;

defined('ABSPATH') || exit;

class ApiHandler {

    public static $site_url_datas = [];

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('medium-post-ai/v1', '/generate', [
            'methods' => 'POST',
            'callback' => ['MediumPostAI\GenerateHandler', 'handle'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
            'args' => [
                'mode' => [
                    'required' => false,
                    'validate_callback' => function($param) {
                        return in_array($param, ['screenshot', 'url']);
                    }
                ],
                'output_type' => [
                    'required' => false,
                    'validate_callback' => function($param) {
                        return in_array($param, ['html', 'elementor', 'redesign']);
                    }
                ],
                'iterations' => [
                    'required' => false,
                    'validate_callback' => function($param) {
                        return in_array($param, ['1', '2', '3', '4', '5']);
                    },
                    'default' => '1'
                ],
                'url' => [
                    'required' => false,
                    'sanitize_callback' => 'esc_url_raw'
                ],
                'description' => [
                    'required' => false,
                ],
                'current_code' => [
                    'required' => false,
                ],
                'siteURL' => [
                    'required' => false,
                    'sanitize_callback' => 'esc_url_raw'
                ],
                'content_type' => [
                    'required' => false,
                ],
            ]
        ]);

        register_rest_route('medium-post-ai/v1', '/refine', [
            'methods' => 'POST',
            'callback' => ['MediumPostAI\RefineHandler', 'handle'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
            'args' => [
                'mode' => [ // This is required!
                    'required' => false,
                    'validate_callback' => function($param) {
                        return in_array($param, ['screenshot', 'url']);
                    }
                ],
                'output_type' => [ // This is required!
                    'required' => false,
                    'validate_callback' => function($param) {
                        return in_array($param, ['html', 'elementor', 'redesign']);
                    }
                ],
                // 'iterations' is not required for refine, which is good.
                'iterations' => [
                    'required' => false, // This is already false, good.
                    'validate_callback' => function($param) {
                        return in_array($param, ['1', '2', '3', '4', '5']);
                    },
                    'default' => '1'
                ],
                'url' => [
                    'required' => false,
                    'sanitize_callback' => 'esc_url_raw'
                ],
                'description' => [
                    'required' => false,
                ],
                'desc_tag_name' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'update_description' => [
                    'required' => false,
                ],
                'current_code' => [
                    'required' => false,
                ],
                'siteURL' => [
                    'required' => false,
                    'sanitize_callback' => 'esc_url_raw'
                ],
                'content_type' => [
                    'required' => false,
                ],
            ]
        ]);
        
        // NEW ENDPOINT: Refine Image
        register_rest_route('medium-post-ai/v1', '/refine-image', [
            'methods' => 'POST',
            'callback' => ['MediumPostAI\ImageHandler', 'handle_refine_image'], // Assuming a new ImageHandler class
            'permission_callback' => function() {
                return is_user_logged_in();
            },
            'args' => [
                'base_concept' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'refinement_instruction' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'post_title' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'content_type' => ['required' => false, 'sanitize_callback' => 'sanitize_text_field'],
            ]
        ]);

        // NEW ENDPOINT: Regenerate Image
        register_rest_route('medium-post-ai/v1', '/regenerate-image', [
            'methods' => 'POST',
            'callback' => ['MediumPostAI\ImageHandler', 'handle_regenerate_image'], // Assuming a new ImageHandler class
            'permission_callback' => function() {
                return is_user_logged_in();
            },
            'args' => [
                'base_concept' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'], // Will be the post title
                'post_title' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'content_type' => ['required' => false, 'sanitize_callback' => 'sanitize_text_field'],
            ]
        ]);


        register_rest_route('medium-post-ai/v1', '/progress', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_progress'],
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ]);
    }

    public static function get_progress() {
        $progress = get_transient('MPAI_progress_' . get_current_user_id());
        
        if ($progress === false) {
            return new \WP_REST_Response([
                'status' => 'pending',
                'current' => 1,
                'total' => 1,
                'message' => 'No progress data available'
            ], 200);
        }
        return new \WP_REST_Response([
            'status' => 'success',
            'current' => $progress['current'],
            'total' => $progress['total'],
            'source' => $progress['source'],
            'message' => $progress['message'],
            'timestamp' => $progress['timestamp']
        ], 200);
    }

   

    public static function store_preview_content($output_type, $generated_content) {
        try {
            $preview_id = 'f2e-' . uniqid();
            
            // Additional validation
            if (empty($generated_content)) {
                throw new \RuntimeException('Content empty');
            }

            // // Convert 'redesign' to 'html' since they use the same format
            // if ($output_type === 'redesign') {
            //     $output_type = 'html';
            // }

            error_log('store_preview_content: ' . $output_type);
            
            // Store for 1 hour (3600 seconds)
            $stored = set_transient(
                'MPAI_preview_' . $preview_id, 
                [
                    'content' => $generated_content,
                    'output_type' => ($output_type === 'redesign') ? 'html' : $output_type,
                    'created_at' => time(),
                    'user_id' => get_current_user_id()
                ], 
                3600
            );
            
            if (!$stored) {
                throw new \RuntimeException('Failed to store preview data');
            }
            
            return $preview_id;
            
        } catch (\Exception $e) {
            error_log('Failed to store preview: ' . $e->getMessage());
            return false;
        }
    }
    

    public static function content_error_validation($output_type, $generated_content) {
        if ($output_type === 'html') {
            return self::validate_html_content($generated_content);
        } else if ($output_type === 'elementor') {
            // Handle both array and JSON string input
            if (is_array($generated_content)) {
                return self::validate_elementor_json($generated_content);
            } else {
                // Try to decode if it's a string
                $decoded = json_decode($generated_content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException(
                        __('Invalid Elementor JSON format: ', 'medium-post-ai') . json_last_error_msg(),
                        400
                    );
                }
                return self::validate_elementor_json($decoded);
            }
        }
        return true;
    }
    
    public static function validate_html_content($html) {
        // Basic HTML structure validation
        if (empty($html)) {
            error_log('validate_html_content: Generated HTML content is empty.');
            throw new \RuntimeException(__('Generated HTML content is empty.', 'medium-post-ai'), 400);
            return false;
        }
    
        // Check for basic HTML tags and ```html
        // if (!preg_match('/<html|<body|<div|<section|<header|<footer|```html/i', $html)) {
        //     error_log('validate_html_content: Generated HTML appears to be invalid. Missing essential HTML tags or ```html.');
        //     throw new \RuntimeException(
        //         __('Generated HTML appears to be invalid. Missing essential HTML tags or ```html.', 'medium-post-ai'),
        //         400
        //     );
        //     return false;
        // }
    
        return true;
    }
    
    public static function validate_elementor_json($content) {
        // If content is already an array (from your error log), use it directly
        $decoded = is_array($content) ? $content : json_decode($content, true);
        
        // If content was a string and failed to decode
        if (!is_array($content) && json_last_error() !== JSON_ERROR_NONE) {
            error_log('validate_elementor_json: Invalid JSON format');
            throw new \RuntimeException(
                __('Invalid JSON format: ', 'medium-post-ai') . json_last_error_msg(),
                400
            );
        }
    
        // Check for basic Elementor structure
        // if (!isset($decoded['elType'])) {
        //     error_log('validate_elementor_json: Generated JSON is not valid Elementor data. Missing "elType" property');
        //     throw new \RuntimeException(
        //         __('Generated JSON is not valid Elementor data. Missing "elType" property.', 'medium-post-ai'),
        //         400
        //     );
        // }
    
    
        return true;
    }

    /**
     * Stores site URL data in a transient for later retrieval
     * * @param array $site_data The site data to store (should contain 'base64Image' and other relevant data)
     * @param int $expiration Optional. Time until expiration in seconds. Default 3600 (1 hour).
     * @return bool True if value was set, false otherwise.
     */
    public static function set_site_url_data($site_data, $expiration = 3600) {
        if (!is_array($site_data)) {
            error_log('Site data must be an array');
            return false;
        }
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            error_log('No user ID available for storing site data');
            return false;
        }
        
        $transient_key = 'MPAI_site_data_' . $user_id;
        return set_transient($transient_key, $site_data, $expiration);
    }

    /**
     * Retrieves stored site URL data from transient
     * * @param string $siteURL Optional. If data doesn't exist, will fetch fresh data using this URL.
     * @param bool $force_refresh Optional. Whether to ignore cached data and fetch fresh.
     * @return array|false The site data array or false on failure.
     */
    public static function get_site_url_data($siteURL = null, $force_refresh = false) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            error_log('No user ID available for retrieving site data');
            return false;
        }
        
        $transient_key = 'MPAI_site_data_' . $user_id;
        
        // Return cached data unless forcing refresh
        if (!$force_refresh) {
            $cached_data = get_transient($transient_key);
            // error_log('retrieve cached site_data: ' . print_r($cached_data, true));

            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        // If we reach here, either forcing refresh or no cached data exists
        if (empty($siteURL)) {
            error_log('No site URL provided to fetch fresh data');
            return false;
        }
        
        // Fetch fresh data
        if (!class_exists('MediumPostAI\SiteURL_Handler')) {
            require_once MPAI_PLUGIN_DIR . 'includes/class-site-url-handler.php';
        }
        
        $site_url_handler = new SiteURL_Handler();
        $fresh_data = $site_url_handler->take_screenshot_from_site_url_with_data($siteURL);
        
        // Store the fresh data
        if ($fresh_data && !empty($fresh_data['base64Image'])) {
            set_transient($transient_key, $fresh_data, 3600);
            return $fresh_data;
        }
        
        return false;
    }


    /**
     * Clears stored site URL data
     * * @return bool True if successful, false otherwise.
     */
    public static function clear_site_url_data() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        
        $transient_key = 'MPAI_site_data_' . $user_id;
        return delete_transient($transient_key);
    }

    /**
     * Updates the progress transient with a new message for Figma.
     * This method sets a transient to track the progress of Figma operations.
     * It includes the source of the operation, the message to display, and a timestamp.
     * The transient expires after 1 minute to ensure it doesn't persist unnecessarily.
     * * @param string $msg The message to display indicating the progress.
     * @param string $source_name The source of the operation, e.g., "Figma URL".
     */
    public static function update_progress($msg, $source_name) {
        
        $progress_data = [
            'source' => $source_name, // This ensures consistency with frontend checks
            'message' => $msg, // The message to display to the user
            'timestamp' => time() // Timestamp for when the progress was updated
        ];
        try {
            set_transient(
                'MPAI_progress_' . get_current_user_id(),
                $progress_data,
                60 // This sets the transient to expire after 1 minute
            );
        } catch (\Exception $e) {
            error_log("Failed to set progress transient for Figma: " . $e->getMessage());
        }

        // For debugging purposes, uncomment the following line to log the updated progress data
        // error_log('Figma Progress updated: ' . print_r($progress_data, true));
    }

    public static function clean_output_buffers() {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    public static function process_image_upload($mode) {
        if ($mode !== 'screenshot' || !isset($_FILES['screenshot'])) {
            return null;
        }
    
        $file = $_FILES['screenshot'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => __('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'medium-post-ai'),
                UPLOAD_ERR_FORM_SIZE => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'medium-post-ai'),
                UPLOAD_ERR_PARTIAL => __('The uploaded file was only partially uploaded.', 'medium-post-ai'),
                UPLOAD_ERR_NO_FILE => __('No file was uploaded.', 'medium-post-ai'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing a temporary folder.', 'medium-post-ai'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk.', 'medium-post-ai'),
                UPLOAD_ERR_EXTENSION => __('A PHP extension stopped the file upload.', 'medium-post-ai'),
            ];
            
            $error_message = $error_messages[$file['error']] ?? __('Unknown upload error occurred.', 'medium-post-ai');
            throw new \RuntimeException($error_message, 400);
        }
    
        // Validate file type more thoroughly
        $allowed_mime_types = ['image/jpeg', 'image/png'];
        $file_info = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        
        if (!$file_info['ext'] || !$file_info['type'] || !in_array($file_info['type'], $allowed_mime_types)) {
            throw new \RuntimeException(
                __('Invalid file type. Please upload a JPEG or PNG image.', 'medium-post-ai'),
                400
            );
        }
    
        // Verify the file is actually an image
        $image_size = @getimagesize($file['tmp_name']);
        if ($image_size === false) {
            throw new \RuntimeException(
                __('The uploaded file is not a valid image.', 'medium-post-ai'),
                400
            );
        }
    
        // Check file size (limit to 20MB)
        $max_size = 20 * 1024 * 1024; // 20MB
        if ($file['size'] > $max_size) {
            throw new \RuntimeException(
                __('The uploaded file is too large. Maximum size is 20MB.', 'medium-post-ai'),
                400
            );
        }
    
        $image_content = file_get_contents($file['tmp_name']);
        if ($image_content === false) {
            throw new \RuntimeException(
                __('Could not read uploaded file.', 'medium-post-ai'),
                400
            );
        }
    
        return base64_encode($image_content);
    }

    public static function process_logo_upload($mode) {
        if (!isset($_FILES['logo'])) {
            return null;
        }
    
        $file = $_FILES['logo'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => __('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'medium-post-ai'),
                UPLOAD_ERR_FORM_SIZE => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'medium-post-ai'),
                UPLOAD_ERR_PARTIAL => __('The uploaded file was only partially uploaded.', 'medium-post-ai'),
                UPLOAD_ERR_NO_FILE => __('No file was uploaded.', 'medium-post-ai'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing a temporary folder.', 'medium-post-ai'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk.', 'medium-post-ai'),
                UPLOAD_ERR_EXTENSION => __('A PHP extension stopped the file upload.', 'medium-post-ai'),
            ];
            
            $error_message = $error_messages[$file['error']] ?? __('Unknown upload error occurred.', 'medium-post-ai');
            throw new \RuntimeException($error_message, 400);
        }
    
        // Validate file type more thoroughly
        $allowed_mime_types = ['image/jpeg', 'image/png'];
        $file_info = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        
        if (!$file_info['ext'] || !$file_info['type'] || !in_array($file_info['type'], $allowed_mime_types)) {
            throw new \RuntimeException(
                __('Invalid file type. Please upload a JPEG or PNG image.', 'medium-post-ai'),
                400
            );
        }
    
        // Verify the file is actually an image
        $image_size = @getimagesize($file['tmp_name']);
        if ($image_size === false) {
            throw new \RuntimeException(
                __('The uploaded file is not a valid image.', 'medium-post-ai'),
                400
            );
        }
    
        // Check file size (limit to 20MB)
        $max_size = 5 * 1024 * 1024; // 20MB
        if ($file['size'] > $max_size) {
            throw new \RuntimeException(
                __('The uploaded file is too large. Maximum size is 20MB.', 'medium-post-ai'),
                400
            );
        }
    
        $image_content = file_get_contents($file['tmp_name']);
        if ($image_content === false) {
            throw new \RuntimeException(
                __('Could not read uploaded file.', 'medium-post-ai'),
                400
            );
        }
    
        return base64_encode($image_content);
    }

    public static function get_yt_devio_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();
    
        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/youtube/blog/class-agentic-prompt-generate-blog-devio.php';
        
        error_log('In get_yt_devio_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Dev.io prompt file not found: ' . $prompt_file);
            return self::get_default_devio_prompt();
        }
    
        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();
    
        if (!empty($output)) {
            error_log('Unexpected output in devio prompt file: ' . substr($output, 0, 200));
        }
    
        if (!class_exists('MediumPostAI\PromptGenerateDevio')) {
            error_log('PromptGenerateDevio class not found after inclusion');
            return self::get_default_devio_prompt();
        }
    
        try {
            $prompt = PromptGenerateDevio::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_devio_prompt();
        } catch (\Exception $e) {
            error_log('Dev.io prompt generation error: ' . $e->getMessage());
            return self::get_default_devio_prompt();
        }
    }

    public static function get_devio_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();
    
        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/blog/class-agentic-prompt-generate-blog-devio.php';
        
        error_log('In get_devio_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Dev.io prompt file not found: ' . $prompt_file);
            return self::get_default_devio_prompt();
        }
    
        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();
    
        if (!empty($output)) {
            error_log('Unexpected output in devio prompt file: ' . substr($output, 0, 200));
        }
    
        if (!class_exists('MediumPostAI\PromptGenerateDevio')) {
            error_log('PromptGenerateDevio class not found after inclusion');
            return self::get_default_devio_prompt();
        }
    
        try {
            $prompt = PromptGenerateDevio::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_devio_prompt();
        } catch (\Exception $e) {
            error_log('Dev.io prompt generation error: ' . $e->getMessage());
            return self::get_default_devio_prompt();
        }
    }
    
    public static function get_default_devio_prompt() {
        return "Generate a professional Dev.io post with technical depth, code examples, and practical insights. " .
               "Focus on developer tools, programming concepts, and software engineering best practices. " .
               "Include proper code formatting and technical explanations suitable for a developer audience.";
    }

    public static function get_refine_system_prompt_content() {
        self::clean_output_buffers();

        // $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/html/class-agentic-prompt-update-html.php';
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }
    

    public static function get_website_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/blog/class-agentic-prompt-generate-blog-website.php';
        
        error_log('In get_website_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    public static function get_yt_website_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/youtube/blog/class-agentic-prompt-generate-blog-website.php';
        
        error_log('In get_yt_website_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    public static function get_yt_facebook_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/youtube/blog/class-agentic-prompt-generate-blog-facebook.php';
        
        error_log('In get_yt_facebook_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    public static function get_yt_instagram_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/youtube/blog/class-agentic-prompt-generate-blog-instagram.php';
        
        error_log('In get_yt_instagram_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }


    public static function get_instagram_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/blog/class-agentic-prompt-generate-blog-instagram.php';
        
        error_log('In get_instagram_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }


    public static function get_facebook_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/blog/class-agentic-prompt-generate-blog-facebook.php';
        
        error_log('In get_facebook_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }


    public static function get_yt_medium_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/youtube/blog/class-agentic-prompt-generate-blog-medium.php';
        
        error_log('In get_yt_medium_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }


    public static function get_medium_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();


        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/blog/class-agentic-prompt-generate-blog-medium.php';
        
        error_log('In get_medium_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    public static function get_yt_linkedin_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/youtube/blog/class-agentic-prompt-generate-blog-linkedin.php';
        
        error_log('In get_yt_linkedin_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    public static function get_linkedin_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/blog/class-agentic-prompt-generate-blog-linkedin.php';
        
        error_log('In get_linkedin_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    public static function get_imagen_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/imagen/class-agentic-prompt-generate-blog-img.php';
        
        error_log('In get_imagen_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateIMG')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateIMG::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    public static function get_yt_x_twitter_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/youtube/blog/class-agentic-prompt-generate-blog-x-twitter.php';
        
        error_log('In get_yt_x_twitter_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    public static function get_x_twitter_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/blog/class-agentic-prompt-generate-blog-x-twitter.php';
        
        error_log('In get_x_twitter_system_prompt_content: ' . $prompt_file);
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }


    public static function get_html_system_prompt_content() {
        self::clean_output_buffers();

        // $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = MPAI_PLUGIN_DIR . 'prompts/system/html/class-agentic-prompt-generate-html.php';
        
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            return self::get_default_system_prompt();
        }

        ob_start();
        $included = include $prompt_file;
        $output = ob_get_clean();

        if (!empty($output)) {
            error_log('Unexpected output in prompt file: ' . substr($output, 0, 200));
        }

        if (!class_exists('MediumPostAI\PromptGenerateHTML')) {
            error_log('PromptGenerateHTML class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateHTML::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }
   
    public static function get_html_content() {
        self::clean_output_buffers();

        $html_file = MPAI_PLUGIN_DIR . 'prompts/system/elementor/sample-html.php';
        
        if (!file_exists($html_file)) {
            error_log('Sample HTML file not found: ' . $html_file);
            return self::get_default_system_prompt();
        }

        $html_content = file_get_contents($html_file);

        return $html_content;
    }

    public static function get_default_system_prompt() {
        return "Convert the design into production-ready HTML using semantic markup and modern CSS. " . 
               "Include proper accessibility attributes and responsive design considerations.";
    }
}