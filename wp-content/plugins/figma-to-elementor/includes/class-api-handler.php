<?php
namespace FigmaToElementor;

defined('ABSPATH') || exit;

class ApiHandler {

    public static $site_url_datas = [];

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('figma-to-elementor/v1', '/generate', [
            'methods' => 'POST',
            'callback' => ['FigmaToElementor\GenerateHandler', 'handle'],
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
            ]
        ]);

        register_rest_route('figma-to-elementor/v1', '/refine', [
            'methods' => 'POST',
            'callback' => ['FigmaToElementor\RefineHandler', 'handle'],
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
            ]
        ]);

        register_rest_route('figma-to-elementor/v1', '/progress', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_progress'],
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ]);
    }

    public static function get_progress() {
        $progress = get_transient('f2e_progress_' . get_current_user_id());
        
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
                'f2e_preview_' . $preview_id, 
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
                        __('Invalid Elementor JSON format: ', 'figma-to-elementor') . json_last_error_msg(),
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
            throw new \RuntimeException(__('Generated HTML content is empty.', 'figma-to-elementor'), 400);
            return false;
        }
    
        // Check for basic HTML tags and ```html
        if (!preg_match('/<html|<body|<div|<section|<header|<footer|```html/i', $html)) {
            error_log('validate_html_content: Generated HTML appears to be invalid. Missing essential HTML tags or ```html.');
            throw new \RuntimeException(
                __('Generated HTML appears to be invalid. Missing essential HTML tags or ```html.', 'figma-to-elementor'),
                400
            );
            return false;
        }
    
        return true;
    }
    
    public static function validate_elementor_json($content) {
        // If content is already an array (from your error log), use it directly
        $decoded = is_array($content) ? $content : json_decode($content, true);
        
        // If content was a string and failed to decode
        if (!is_array($content) && json_last_error() !== JSON_ERROR_NONE) {
            error_log('validate_elementor_json: Invalid JSON format');
            throw new \RuntimeException(
                __('Invalid JSON format: ', 'figma-to-elementor') . json_last_error_msg(),
                400
            );
        }
    
        // Check for basic Elementor structure
        // if (!isset($decoded['elType'])) {
        //     error_log('validate_elementor_json: Generated JSON is not valid Elementor data. Missing "elType" property');
        //     throw new \RuntimeException(
        //         __('Generated JSON is not valid Elementor data. Missing "elType" property.', 'figma-to-elementor'),
        //         400
        //     );
        // }
    
    
        return true;
    }

    /**
     * Stores site URL data in a transient for later retrieval
     * 
     * @param array $site_data The site data to store (should contain 'base64Image' and other relevant data)
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
        
        $transient_key = 'f2e_site_data_' . $user_id;
        return set_transient($transient_key, $site_data, $expiration);
    }

    /**
     * Retrieves stored site URL data from transient
     * 
     * @param string $siteURL Optional. If data doesn't exist, will fetch fresh data using this URL.
     * @param bool $force_refresh Optional. Whether to ignore cached data and fetch fresh.
     * @return array|false The site data array or false on failure.
     */
    public static function get_site_url_data($siteURL = null, $force_refresh = false) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            error_log('No user ID available for retrieving site data');
            return false;
        }
        
        $transient_key = 'f2e_site_data_' . $user_id;
        
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
        if (!class_exists('FigmaToElementor\SiteURL_Handler')) {
            require_once F2E_PLUGIN_DIR . 'includes/class-site-url-handler.php';
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
     * 
     * @return bool True if successful, false otherwise.
     */
    public static function clear_site_url_data() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        
        $transient_key = 'f2e_site_data_' . $user_id;
        return delete_transient($transient_key);
    }

    /**
     * Updates the progress transient with a new message for Figma.
     * This method sets a transient to track the progress of Figma operations.
     * It includes the source of the operation, the message to display, and a timestamp.
     * The transient expires after 1 minute to ensure it doesn't persist unnecessarily.
     * 
     * @param string $msg The message to display indicating the progress.
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
                'f2e_progress_' . get_current_user_id(),
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
                UPLOAD_ERR_INI_SIZE => __('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'figma-to-elementor'),
                UPLOAD_ERR_FORM_SIZE => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'figma-to-elementor'),
                UPLOAD_ERR_PARTIAL => __('The uploaded file was only partially uploaded.', 'figma-to-elementor'),
                UPLOAD_ERR_NO_FILE => __('No file was uploaded.', 'figma-to-elementor'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing a temporary folder.', 'figma-to-elementor'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk.', 'figma-to-elementor'),
                UPLOAD_ERR_EXTENSION => __('A PHP extension stopped the file upload.', 'figma-to-elementor'),
            ];
            
            $error_message = $error_messages[$file['error']] ?? __('Unknown upload error occurred.', 'figma-to-elementor');
            throw new \RuntimeException($error_message, 400);
        }
    
        // Validate file type more thoroughly
        $allowed_mime_types = ['image/jpeg', 'image/png'];
        $file_info = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        
        if (!$file_info['ext'] || !$file_info['type'] || !in_array($file_info['type'], $allowed_mime_types)) {
            throw new \RuntimeException(
                __('Invalid file type. Please upload a JPEG or PNG image.', 'figma-to-elementor'),
                400
            );
        }
    
        // Verify the file is actually an image
        $image_size = @getimagesize($file['tmp_name']);
        if ($image_size === false) {
            throw new \RuntimeException(
                __('The uploaded file is not a valid image.', 'figma-to-elementor'),
                400
            );
        }
    
        // Check file size (limit to 20MB)
        $max_size = 20 * 1024 * 1024; // 20MB
        if ($file['size'] > $max_size) {
            throw new \RuntimeException(
                __('The uploaded file is too large. Maximum size is 20MB.', 'figma-to-elementor'),
                400
            );
        }
    
        $image_content = file_get_contents($file['tmp_name']);
        if ($image_content === false) {
            throw new \RuntimeException(
                __('Could not read uploaded file.', 'figma-to-elementor'),
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
                UPLOAD_ERR_INI_SIZE => __('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'figma-to-elementor'),
                UPLOAD_ERR_FORM_SIZE => __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'figma-to-elementor'),
                UPLOAD_ERR_PARTIAL => __('The uploaded file was only partially uploaded.', 'figma-to-elementor'),
                UPLOAD_ERR_NO_FILE => __('No file was uploaded.', 'figma-to-elementor'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing a temporary folder.', 'figma-to-elementor'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk.', 'figma-to-elementor'),
                UPLOAD_ERR_EXTENSION => __('A PHP extension stopped the file upload.', 'figma-to-elementor'),
            ];
            
            $error_message = $error_messages[$file['error']] ?? __('Unknown upload error occurred.', 'figma-to-elementor');
            throw new \RuntimeException($error_message, 400);
        }
    
        // Validate file type more thoroughly
        $allowed_mime_types = ['image/jpeg', 'image/png'];
        $file_info = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        
        if (!$file_info['ext'] || !$file_info['type'] || !in_array($file_info['type'], $allowed_mime_types)) {
            throw new \RuntimeException(
                __('Invalid file type. Please upload a JPEG or PNG image.', 'figma-to-elementor'),
                400
            );
        }
    
        // Verify the file is actually an image
        $image_size = @getimagesize($file['tmp_name']);
        if ($image_size === false) {
            throw new \RuntimeException(
                __('The uploaded file is not a valid image.', 'figma-to-elementor'),
                400
            );
        }
    
        // Check file size (limit to 20MB)
        $max_size = 5 * 1024 * 1024; // 20MB
        if ($file['size'] > $max_size) {
            throw new \RuntimeException(
                __('The uploaded file is too large. Maximum size is 20MB.', 'figma-to-elementor'),
                400
            );
        }
    
        $image_content = file_get_contents($file['tmp_name']);
        if ($image_content === false) {
            throw new \RuntimeException(
                __('Could not read uploaded file.', 'figma-to-elementor'),
                400
            );
        }
    
        return base64_encode($image_content);
    }


    public static function get_figma_system_prompt_content($output) {
        self::clean_output_buffers();

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        if($output=='elementor'){
            $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/figma/class-agentic-prompt-generate-figma-to-elementor.php';
        }
        else if($output=='redesign'){
            $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/figma/class-agentic-prompt-generate-redesign-figma-to-html.php';
        }else{
            $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/figma/class-agentic-prompt-generate-figma-to-html.php';
        }
        
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

        if (!class_exists('FigmaToElementor\PromptGenerateFigma')) {
            error_log('PromptGenerateFigma class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateFigma::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    /**
     * Gets the file path for the Figma system prompt based on the output type.
     *
     * @param string $output The desired output type.
     * @return string The file path to the system prompt.
     */
    public static function get_figma_system_prompt_file_path(string $output): string {
        // Ensure this constant is defined, e.g., define('F2E_PLUGIN_DIR', plugin_dir_path(__FILE__));
        if (!defined('F2E_PLUGIN_DIR')) {
            error_log('F2E_PLUGIN_DIR is not defined.');
            // Fallback or throw an error
            return '';
        }

        $prompt_file = '';
        if ($output == 'elementor') {
            $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/figma/class-agentic-prompt-generate-figma-to-elementor.php';
        } else if ($output == 'redesign') {
            $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/figma/class-agentic-prompt-generate-redesign-figma-to-html.php';
        } else {
            $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/figma/figma-to-html.docx'; // This should ideally be a .txt or .md for text prompts
        }

        // It's crucial that this file exists and contains a plain text prompt.
        // If it's a .php file that *returns* the prompt, you'd need to include and get its return value.
        // If it's a .docx, you'd need a way to extract text from it, which is complex.
        // For simplicity, let's assume it's a plain text file for direct submission.
        if (!file_exists($prompt_file)) {
            error_log('Prompt file not found: ' . $prompt_file);
            // This is a critical error, you might want to throw an exception or return a default.
            return ''; // Or a path to a default prompt file
        }

        return $prompt_file;
    }


    public static function get_refine_system_prompt_content() {
        self::clean_output_buffers();

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-agentic-prompt-update-html.php';
        
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

        if (!class_exists('FigmaToElementor\PromptGenerateHTML')) {
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
    

    public static function get_description_html_system_prompt_content($desc_tag_name = '') {
        self::clean_output_buffers();

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/3dbadge/class-agentic-prompt-generate-3d-html.php';
        
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

        if (!class_exists('FigmaToElementor\PromptGenerateHTML')) {
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

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-agentic-prompt-generate-html.php';
        
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

        if (!class_exists('FigmaToElementor\PromptGenerateHTML')) {
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


    public static function get_redesign_html_system_prompt_content() {
        self::clean_output_buffers();

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-agentic-prompt-generate-redesign-html.php';
        
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

        if (!class_exists('FigmaToElementor\PromptGenerateHTML')) {
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


    public static function get_screenshot_elementor_system_prompt_content() {
        self::clean_output_buffers();

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/elementor/class-agentic-prompt-generate-elementor-from-screenshot.php';
        
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

        if (!class_exists('FigmaToElementor\PromptGenerateElementorScreenshot')) {
            error_log('PromptGenerateElementorScreenshot class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateElementorScreenshot::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }


    public static function get_description_elementor_system_prompt_content() {
        self::clean_output_buffers();

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/elementor/class-agentic-prompt-generate-elementor-from-description.php';
        
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

        if (!class_exists('FigmaToElementor\PromptGenerateElementorScreenshot')) {
            error_log('PromptGenerateElementorScreenshot class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateElementorScreenshot::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

   
    public static function get_html_content() {
        self::clean_output_buffers();

        $html_file = F2E_PLUGIN_DIR . 'prompts/system/elementor/sample-html.php';
        
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