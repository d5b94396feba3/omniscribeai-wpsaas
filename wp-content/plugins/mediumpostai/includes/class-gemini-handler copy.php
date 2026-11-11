<?php
namespace MediumPostAI;

defined('ABSPATH') || exit;

class Gemini_Handler {
    private $api_key;
    private $model;
    private $top_p;
    private $temperature;
    private $max_tokens;
    private $thinking_budget;

    public function __construct() {
        $this->api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        // $this->model = 'gemini-2.5-flash-preview-04-17'; // Updated model identifier
        $this->model = 'gemini-2.5-flash'; // Updated model identifier
        $this->temperature = 0.7;
        // $this->temperature = 0.4; // Slightly conservative
        $this->max_tokens = 4096;
        $this->top_p = 0.9;      // Helps with coherent HTML structure
        $this->thinking_budget = 2048; // More reasoning steps
    }

    public function generate_content_without_agentic(array $messages) {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        $payload = $this->prepare_payload($messages);

        // Log the payload for debugging
        // error_log("Gemini API Payload: " . json_encode($payload));

        $response = $this->make_api_request($payload);

        return $this->process_response($response);
    }

    public function generate_content_with_agentic_old(array $messages, int $iterations) {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }
    
        $turn_count = 1;
        $max_turns = $iterations;
        $result = null;
    
        $payload = $this->prepare_payload($messages);
    
        while($turn_count <= $max_turns-1) {
            error_log("Turn $turn_count of $max_turns");
            // error_log("Payload: " . print_r($payload, true));

            // Send progress update
            $this->update_progress($turn_count, $max_turns);
    
            $response = $this->make_api_request($payload);
            $result = $this->process_response($response);

            error_log("Gemini response: " . $result['content']);

            // Add model's response to conversation history
            $payload['contents'][] = [
                'role' => 'model',
                'parts' => [
                    ['text' => $result['content']]
                ]
            ];
    
            // Add user's review prompt (modify this as needed)
            $payload['contents'][] = [
                'role' => 'user',
                'parts' => [
                    ['text' => "Please review the output and update the output 100% correct."]
                ]
            ];
    
            $turn_count++;
            
            // Optional: Add conditions to break early if response is good enough
            // if ($this->is_response_satisfactory($result['content'])) {
            //     break;
            // }
        }
    
        return $result;
    }

    /**
     * Generates an image for a blog post using the Imagen model 
     * via the Generative Language API, strictly following the REST API ':predict' example.
     */
    public function generate_image_base64_old(string $blog_title, string $system_prompt, string $content_type): string {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        // 1. Define Model and Endpoint based on the official REST example
        // NOTE: This uses the slightly different ':predict' endpoint and its specific payload.
        $image_model = 'imagen-4.0-generate-001'; 
        $image_generation_url = "https://generativelanguage.googleapis.com/v1beta/models/{$image_model}:predict";
        
        // 2. Prepare Prompt
        $base_prompt = "High-quality, professional cover image for a blog post titled: '{$blog_title}'";
        $prompt = !empty($system_prompt) ? $system_prompt . ". " . $base_prompt : $base_prompt;

        // 3. Prepare Payload (Strictly following the official REST format: 'instances' and 'parameters')
        $payload = [
            'instances' => [
                [
                    'prompt' => $prompt
                ]
            ],
            'parameters' => [
                'sampleCount' => 1, // Set to 1 as you typically want one header image
                'outputMimeType' => 'image/png',
            ],
        ];

        // 4. Make Request (Using 'x-goog-api-key' header, as per the official cURL example)
        $response = wp_remote_post($image_generation_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                // CRITICAL: Use the header method as shown in the official cURL example
                'x-goog-api-key' => $this->api_key, 
            ],
            'body' => json_encode($payload),
            'timeout' => 60,
            'sslverify' => true
        ]);

        if (is_wp_error($response)) {
            throw new \Exception("Image generation API request failed: " . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code !== 200) {
            $error_detail = $body['error']['message'] ?? wp_remote_retrieve_body($response);
            $error = "HTTP $status_code Error. Details: " . $error_detail;
            throw new \Exception("Image Generation API Error: " . $error);
        }
        
        // 5. Process Response (The ':predict' response returns in the 'predictions' array)
        if (isset($body['predictions'][0]['bytesBase64Encoded'])) {
            return $body['predictions'][0]['bytesBase64Encoded'];
        }

        throw new \Exception(__('Invalid response format from Image Generation API.', 'medium-post-ai'));
    }

    /**
     * Generates an image for a blog post using the Imagen model via the Generative Language API,
     * saves the resulting Base64 image to the WordPress uploads folder, and returns the public URL.
     * * @param string $blog_title The title of the blog post, used for the file name.
     * @param string $system_prompt The system prompt for image style/rules.
     * @return string The public URL of the saved image file.
     * @throws \Exception If the API call fails or file saving fails.
     */
    public function generate_image_old(string $blog_title, string $system_prompt): string {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        // 1. Define Model and Endpoint (Based on official REST API :predict format)
        $image_model = 'imagen-4.0-generate-001'; 
        $image_generation_url = "https://generativelanguage.googleapis.com/v1beta/models/{$image_model}:predict";
        
        // 2. Prepare Prompt
        $base_prompt = "High-quality, abstract, professional cover image for a blog post titled: '{$blog_title}'";
        $prompt = !empty($system_prompt) ? $system_prompt . ". " . $base_prompt : $base_prompt;

        // 3. Prepare Payload
        $payload = [
            'instances' => [
                [
                    'prompt' => $prompt
                ]
            ],
            'parameters' => [
                'sampleCount' => 1, 
                'outputMimeType' => 'image/png',
            ],
        ];

        // 4. Make API Request
        $response = wp_remote_post($image_generation_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key, 
            ],
            'body' => json_encode($payload),
            'timeout' => 60,
            'sslverify' => true
        ]);

        if (is_wp_error($response)) {
            throw new \Exception("Image generation API request failed: " . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code !== 200) {
            $error_detail = $body['error']['message'] ?? wp_remote_retrieve_body($response);
            $error = "HTTP $status_code Error. Details: " . $error_detail;
            throw new \Exception("Image Generation API Error: " . $error);
        }
        
        // 5. Process Response and Save File (to return URL)
        if (isset($body['predictions'][0]['bytesBase64Encoded'])) {
            $base64_image = $body['predictions'][0]['bytesBase64Encoded'];
            
            // --- DECODE and SAVE using WordPress functions ---
            
            // a. Decode the Base64 string into binary data
            $binary_data = base64_decode($base64_image);
            if ($binary_data === false) {
                throw new \Exception(__('Failed to decode Base64 image data.', 'medium-post-ai'));
            }

            // b. Get WordPress upload directory info
            $upload_dir = wp_upload_dir();

            // c. Sanitize and create a unique file name
            // The file name ensures it's clean and includes a timestamp to prevent collisions
            $file_title = sanitize_title($blog_title) ?: 'generated-image';
            $file_name = $file_title . '-' . time() . '.png';
            
            // d. Define full path and URL
            // $upload_dir['path'] includes the year/month folders (e.g., /wp-content/uploads/2025/11)
            $file_path = $upload_dir['path'] . '/' . $file_name;
            // $upload_dir['url'] includes the year/month folders (e.g., http://example.com/wp-content/uploads/2025/11)
            $file_url = $upload_dir['url'] . '/' . $file_name;

            // e. Save the binary data to the file path
            $file_put_result = file_put_contents($file_path, $binary_data);
            
            if ($file_put_result === false) {
                throw new \Exception(__('Failed to save image file to disk. Check directory permissions.', 'medium-post-ai'));
            }

            // OPTIONAL: Uncomment this block if you need the image to appear in the WordPress Media Library 
            // This requires the 'file.php' and 'image.php' includes to be available.

            if ( ! function_exists( 'wp_handle_sideload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
            }

            $file_type = wp_check_filetype( $file_name, null );
            $attachment = [
                'post_mime_type' => $file_type['type'],
                'post_title'     => $file_title,
                'post_content'   => '',
                'post_status'    => 'inherit',
            ];
            $attach_id = wp_insert_attachment( $attachment, $file_path );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
            wp_update_attachment_metadata( $attach_id, $attach_data );


            // f. Return the public URL
            return $file_url;
        }

        throw new \Exception(__('Invalid response format from Image Generation API.', 'medium-post-ai'));
    }

    /**
     * Helper to determine the correct aspect ratio for the target platform, 
     * constrained by the Imagen API's supported list (1:1, 9:16, 16:9, 4:3, 3:4).
     *
     * @param string $content_type The target platform ('facebook', 'medium', 'linkedin', etc.).
     * @return string The closest supported Imagen aspectRatio string.
     */
    private function get_image_aspect_ratio(string $content_type): string {
        // Supported Imagen Ratios: 1:1, 9:16, 16:9, 4:3, 3:4.
        
        // Default to the most common landscape blog post format
        $default_ratio = '16:9'; 

        switch (strtolower($content_type)) {
            case 'facebook':
            case 'linkedin':
                // Facebook/LinkedIn link shares commonly use 1.91:1 (landscape). 
                // We must use the closest supported landscape ratio: 16:9.
                return '16:9';
                
            case 'x-twitter':
                // X (Twitter) horizontal posts are typically 16:9.
                return '16:9';

            case 'instagram':
                // Instagram portrait posts use 4:5, which is not supported. 
                // The closest supported vertical ratio is 3:4.
                // Instagram stories use 9:16. We'll default to 3:4 for feed posts.
                return '3:4'; 
                
            case 'medium':
            default:
                // Standard blog post header (landscape)
                return $default_ratio; 
        }
    }

    /**
     * Generates an image for a blog post using the Imagen model 
     * via the Generative Language API, strictly following the REST API ':predict' example.
     * @param string $blog_title The title of the blog post.
     * @param string $system_prompt The system prompt for image style/rules.
     * @param string $content_type The target platform to set aspect ratio.
     * @return string Base64 encoded image data (PNG format).
     * @throws \Exception If the API key is missing or the API call fails.
     */
    public function generate_image_base64(string $blog_title, string $system_prompt, string $content_type = 'medium'): string {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        // 1. Determine Aspect Ratio based on content type
        $aspect_ratio = $this->get_image_aspect_ratio($content_type);
        
        // 2. Define Model and Endpoint 
        $image_model = 'imagen-4.0-generate-001'; 
        $image_generation_url = "https://generativelanguage.googleapis.com/v1beta/models/{$image_model}:predict";
        
        // 3. Prepare Prompt
        $base_prompt = "High-quality, professional cover image for a blog post titled: '{$blog_title}'";
        $prompt = !empty($system_prompt) ? $system_prompt . ". " . $base_prompt : $base_prompt;

        // 4. Prepare Payload (INCLUDES ASPECT RATIO)
        $payload = [
            'instances' => [
                [
                    'prompt' => $prompt
                ]
            ],
            'parameters' => [
                'sampleCount' => 1, 
                'outputMimeType' => 'image/png',
                'aspectRatio' => $aspect_ratio, // CRITICAL ADDITION
            ],
        ];

        // 5. Make Request (Using 'x-goog-api-key' header)
        $response = wp_remote_post($image_generation_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key, 
            ],
            'body' => json_encode($payload),
            'timeout' => 60,
            'sslverify' => true
        ]);

        if (is_wp_error($response)) {
            throw new \Exception("Image generation API request failed: " . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code !== 200) {
            $error_detail = $body['error']['message'] ?? wp_remote_retrieve_body($response);
            $error = "HTTP $status_code Error. Details: " . $error_detail;
            throw new \Exception("Image Generation API Error: " . $error);
        }
        
        // 6. Process Response 
        if (isset($body['predictions'][0]['bytesBase64Encoded'])) {
            return $body['predictions'][0]['bytesBase64Encoded'];
        }

        throw new \Exception(__('Invalid response format from Image Generation API.', 'medium-post-ai'));
    }

    /**
     * Generates an image for a blog post using the Imagen model via the Generative Language API,
     * saves the resulting Base64 image to the WordPress uploads folder, and returns the public URL.
     * @param string $blog_title The title of the blog post, used for the file name.
     * @param string $system_prompt The system prompt for image style/rules.
     * @param string $content_type The target platform (e.g., 'facebook', 'medium').
     * @return string The public URL of the saved image file.
     * @throws \Exception If the API call fails or file saving fails.
     */
    public function generate_image(string $blog_title, string $system_prompt, string $content_type = 'medium'): string {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        // 1. Get Base64 image data using the new method signature
        $base64_image = $this->generate_image_base64($blog_title, $system_prompt, $content_type);
        
        // --- DECODE and SAVE using WordPress functions ---
        
        // a. Decode the Base64 string into binary data
        $binary_data = base64_decode($base64_image);
        if ($binary_data === false) {
            throw new \Exception(__('Failed to decode Base64 image data.', 'medium-post-ai'));
        }

        // b. Get WordPress upload directory info
        $upload_dir = wp_upload_dir();

        // c. Sanitize and create a unique file name
        $file_title = sanitize_title($blog_title) ?: 'generated-image';
        $file_name = $file_title . '-' . time() . '.png';
        
        // d. Define full path and URL
        $file_path = $upload_dir['path'] . '/' . $file_name;
        $file_url = $upload_dir['url'] . '/' . $file_name;

        // e. Save the binary data to the file path
        $file_put_result = file_put_contents($file_path, $binary_data);
        
        if ($file_put_result === false) {
            throw new \Exception(__('Failed to save image file to disk. Check directory permissions.', 'medium-post-ai'));
        }

        // Include necessary WordPress files if they aren't already
        if ( ! function_exists( 'wp_handle_sideload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
        }

        // Add to Media Library (optional but useful)
        $file_type = wp_check_filetype( $file_name, null );
        $attachment = [
            'post_mime_type' => $file_type['type'],
            'post_title'     => $file_title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];
        $attach_id = wp_insert_attachment( $attachment, $file_path );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        // f. Return the public URL
        return $file_url;
    }

    public function generate_content_with_agentic(array $messages, int $iterations) {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }
    
        $result = null;
        $initial_payload = $this->prepare_payload($messages);
        $total_iterations = max(1, $iterations); // Ensure at least 1 iteration
        $total_estimated_cost=0;

        $this->clear_progress_from_transient();

    
        for ($turn_count = 1; $turn_count <= $total_iterations; $turn_count++) {
            try {

                error_log("Turn $turn_count of $total_iterations");
                $this->update_progress($turn_count, $total_iterations);

                $payload = $initial_payload;

                // Token counting and trimming
                $token_count = $this->count_tokens($payload);
                error_log("Token count before request: $token_count");

                if ($token_count > 120000) {
                    error_log("Trimming conversation due to token limit");
                    // $conversation = $this->trim_conversation($conversation);
                }
                // Make API request
                $response = $this->make_api_request($payload);

                // error_log("Gemini response: " . print_r($response,true)); // Log first 200 chars
                // return;

                $result = $this->process_response($response);
                $usage = $result['usage'];
                // $total_estimated_cost += $result['usage']['estimated_cost'];
                $total_estimated_cost = 0;

                // error_log("Gemini response: " . print_r($result,true)); // Log first 200 chars
                error_log("Gemini usage: " . print_r($usage,true)); // Log first 200 chars
                error_log("Gemini total_estimated_cost: " . $total_estimated_cost); // Log first 200 chars

                // return;
    
                // Only add to conversation if not final iteration
                if ($turn_count < $total_iterations) {
                    // Add model's response
                    $payload['contents'][] = [
                        'role' => 'model',
                        'parts' => [
                            ['text' => $result['content']]
                        ]
                    ];
    
                    // Add refinement prompt (more specific than generic review)
                    $payload['contents'][] = [
                        'role' => 'user',
                        'parts' => [
                            ['text' => "Please review and update the post to make 100% appropriate for the platform."]
                        ]
                    ];
    
                    // error_log("Gemini payload: " . print_r($payload, true));
                }

                // Token counting and trimming
                $token_count = $this->count_tokens($payload);
                error_log("Token count after request: $token_count");

                if ($token_count > 120000) {
                    error_log("Trimming conversation due to token limit");
                    // $conversation = $this->trim_conversation($conversation);
                }
    
            } catch (\Exception $e) {
                error_log("Iteration $turn_count failed: " . $e->getMessage());
                throw $e;
            }
        }
        // error_log("Gemini result: " . print_r($result['content'], true));
        $this->clear_progress_from_transient();

        return $result;
    }


    private function trim_conversation(array $conversation): array {
        // Keep system prompt and last 2 exchanges
        $system_prompt = array_shift($conversation);
        $recent = array_slice($conversation, -4);
        return array_merge([$system_prompt], $recent);
    }

    private function count_tokens(array $messages): int {
        $count = 0;
        foreach ($messages as $message) {
            $content = $message['content'] ?? '';
            $count += ceil(strlen(is_array($content) ? json_encode($content) : $content) / 4);
        }
        return $count;
    }

    private function clear_progress_from_transient() {
        try {
            $progress_key = 'MPAI_progress_' . get_current_user_id();
            delete_transient($progress_key);
        } catch (\Exception $e) {
            error_log("Failed to clear progress transient: " . $e->getMessage());
        }
    }

    private function update_progress($current, $total, $msg = '') {
        
        $progress_data = [
            'source' => "Upload Design or Figma URL",
            'current' => $current,
            'total' => $total,
            'message' => $msg,
            'timestamp' => time()
        ];
        try {
            set_transient(
                'MPAI_progress_' . get_current_user_id(),
                $progress_data,
                120 // Expire after 1 minute
            );
        } catch (\Exception $e) {
            error_log("Failed to set progress transient: " . $e->getMessage());
        }

        // error_log('Progress updated: ' . print_r($progress_data, true));

    }

    private function prepare_payload_old(array $messages) {
        $contents = [];
    
        foreach ($messages as $message) {
            $content = ['role' => $message['role']];
            $parts = [];
    
            // Handle both old format (content) and new format (parts)
            if (isset($message['parts'])) {
                // New format with explicit parts array
                foreach ($message['parts'] as $part) {
                    if (isset($part['text'])) {
                        $parts[] = ['text' => $part['text']];
                    } 
                    elseif (isset($part['fileData'])) {
                        $parts[] = [
                            'fileData' => [
                                'mimeType' => $part['fileData']['mimeType'],
                                'fileUri' => $part['fileData']['fileUri']
                            ]
                        ];
                    }
                    elseif (isset($part['inlineData'])) {
                        $parts[] = $part; // Keep as-is if already properly formatted
                    }
                }
            }
            elseif (is_array($message['content'])) {
                // Legacy format with content array
                foreach ($message['content'] as $part) {
                    if (isset($part['type'])) {
                        if ($part['type'] === 'text') {
                            $parts[] = ['text' => $part['text']];
                        } 
                        elseif ($part['type'] === 'image_url') {
                            $parts[] = [
                                'inlineData' => [
                                    'mimeType' => 'image/png',
                                    'data' => str_replace('data:image/png;base64,', '', $part['image_url']['url'])
                                ]
                            ];
                        }
                        // Add fileData handling for legacy format if needed
                    }
                }
            }
            else {
                // Simple text message
                $parts[] = ['text' => $message['content']];
            }
    
            $content['parts'] = $parts;
            $contents[] = $content;
        }
    
        return [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->temperature,
            ]
            // 'systemInstruction' => [
            //     'parts' => [
            //         ['text' => 'You are a professional full stack developer converting figma designs, prompts to production-ready code.']
            //     ]
            // ]
        ];
    }

    private function prepare_payload(array $messages) {
        $contents = [];
    
        foreach ($messages as $message) {
            $content = ['role' => $message['role']];
            $parts = [];
    
            // Handle both new format (parts) and legacy format (content array)
            // NOTE: The structure from prepare_refine_description_messages_gemini_yt uses the 'content' array legacy format.
            if (isset($message['parts'])) {
                // New format with explicit parts array (original code)
                foreach ($message['parts'] as $part) {
                    if (isset($part['text'])) {
                        $parts[] = ['text' => $part['text']];
                    } 
                    elseif (isset($part['fileData'])) {
                        $parts[] = [
                            'fileData' => [
                                'mimeType' => $part['fileData']['mimeType'],
                                'fileUri' => $part['fileData']['fileUri']
                            ]
                        ];
                    }
                    elseif (isset($part['inlineData'])) {
                        $parts[] = $part; 
                    }
                }
            }
            elseif (is_array($message['content'])) {
                // Legacy format with content array (where video/image is passed)
                foreach ($message['content'] as $part) {
                    if (isset($part['type'])) {
                        if ($part['type'] === 'text') {
                            $parts[] = ['text' => $part['text']];
                        } 
                        elseif ($part['type'] === 'image_url') {
                            $parts[] = [
                                'inlineData' => [
                                    'mimeType' => 'image/png',
                                    'data' => str_replace('data:image/png;base64,', '', $part['image_url']['url'])
                                ]
                            ];
                        }
                        // *** FIX: ADD MISSING 'file_data' MAPPING FOR YOUTUBE URLS ***
                        elseif ($part['type'] === 'file_data') {
                            $parts[] = [
                                'fileData' => [
                                    'mimeType' => $part['fileData']['mimeType'],
                                    'fileUri' => $part['fileData']['fileUri']
                                ]
                            ];
                        }
                        // *************************************************************
                    }
                }
            }
            else {
                // Simple text message
                $parts[] = ['text' => $message['content']];
            }
    
            $content['parts'] = $parts;
            $contents[] = $content;
        }
    
        return [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->temperature,
            ]
        ];
    }
    

    private function make_api_request(array $payload) {

        error_log('Payload size: ' . round(strlen(json_encode($payload))/1024, 2) . 'KB');
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        return wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key,
            ],
            'body' => json_encode($payload),
            'timeout' => 300, // 5 minutes
            'sslverify' => true
        ]);
    }

    private function process_response_old($response) {
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Log the raw response for debugging
        // error_log("Gemini API Response (Raw): " . wp_remote_retrieve_body($response));

        if ($status_code !== 200) {
            $error = $body['error']['message'] ?? __('Unknown error occurred with Gemini API.', 'medium-post-ai');
            throw new \Exception($error);
        }

        // Extract generated text
        $generated_text = '';
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $generated_text = $body['candidates'][0]['content']['parts'][0]['text'];
        } else {
            throw new \Exception(__('Invalid response format from Gemini API.', 'medium-post-ai'));
        }

        // Extract JSON from markdown if present
        if (strpos($generated_text, '```json') !== false) {
            preg_match('/```json\s*([\s\S]*?)\s*```/', $generated_text, $matches);
            $generated_text = $matches[1] ?? $generated_text;
        }

        return [
            'content' =>  $this->parse_content_gemini($generated_text),
            'usage' => $this->calculate_usage($body['usageMetadata'] ?? [])
        ];
    }

    private function process_response($response) {
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }
    
        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
    
        if ($status_code !== 200) {
            $error = $body['error']['message'] ?? __('Unknown error occurred with Gemini API.', 'medium-post-ai');
            throw new \Exception($error);
        }
    
        // Extract generated text
        $generated_text = '';
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $generated_text = $body['candidates'][0]['content']['parts'][0]['text'];
        } else {
            throw new \Exception(__('Invalid response format from Gemini API.', 'medium-post-ai'));
        }
    
        // Log the raw response for debugging
        error_log("Raw Gemini Response: " . substr($generated_text, 0, 500) . "...");
    
        // Extract JSON from markdown if present
        if (strpos($generated_text, '```json') !== false) {
            preg_match('/```json\s*([\s\S]*?)\s*```/', $generated_text, $matches);
            $generated_text = $matches[1] ?? $generated_text;
            error_log("Extracted JSON from markdown");
        }
    
        // Parse the content and extract JSON data
        $parsed_content = $this->parse_content_gemini($generated_text);
        
        // Check if we have valid JSON structure with required fields
        $extracted_data = $this->extract_json_data($parsed_content, $generated_text);
    
        return [
            'content' => $parsed_content,
            'usage' => $this->calculate_usage($body['usageMetadata'] ?? []),
            'extracted_data' => $extracted_data
        ];
    }
    
    /**
     * Extract and validate JSON data from parsed content
     * Returns array with title, content, tags, and image
     */
    private function extract_json_data($parsed_content, $original_text = '') {
        $extracted_data = [
            'title' => '',
            'content' => '',
            'tags' => [],
            'image' => ''
        ];
    
        error_log("Parsed content type: " . gettype($parsed_content));
        
        // Case 1: Parsed content is already a valid JSON array with the expected structure
        if (is_array($parsed_content) && isset($parsed_content['title']) && isset($parsed_content['content'])) {
            error_log("Found valid JSON structure in parsed content");
            $extracted_data['title'] = $parsed_content['title'] ?? '';
            $extracted_data['content'] = $parsed_content['content'] ?? '';
            $extracted_data['tags'] = $parsed_content['tags'] ?? [];
            $extracted_data['image'] = $parsed_content['image'] ?? '';
            
            error_log("Successfully extracted: Title='" . $extracted_data['title'] . "', Tags=" . count($extracted_data['tags']));
            return $extracted_data;
        }
    
        // Case 2: Parsed content is a string that contains JSON
        if (is_string($parsed_content)) {
            error_log("Parsed content is string, attempting JSON decode");
            
            // Clean the string first
            $clean_content = trim($parsed_content);
            
            // Try to decode the entire string as JSON
            $json_data = json_decode($clean_content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                error_log("Successfully decoded JSON from string");
                
                if (isset($json_data['title']) && isset($json_data['content'])) {
                    $extracted_data['title'] = $json_data['title'] ?? '';
                    $extracted_data['content'] = $json_data['content'] ?? '';
                    $extracted_data['tags'] = $json_data['tags'] ?? [];
                    $extracted_data['image'] = $json_data['image'] ?? '';
                    
                    error_log("Successfully extracted from decoded JSON");
                    return $extracted_data;
                }
            } else {
                error_log("JSON decode failed: " . json_last_error_msg());
            }
    
            // Try regex pattern matching for JSON
            if (preg_match('/\{"title"\s*:\s*"[^"]*"\s*,\s*"content"\s*:\s*"[^"]*"/', $clean_content)) {
                error_log("Found JSON pattern in string, attempting extraction");
                
                // More robust JSON extraction
                if (preg_match('/\{[\s\S]*"title"[\s\S]*"content"[\s\S]*\}/', $clean_content, $matches)) {
                    $json_data = json_decode($matches[0], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                        $extracted_data['title'] = $json_data['title'] ?? '';
                        $extracted_data['content'] = $json_data['content'] ?? '';
                        $extracted_data['tags'] = $json_data['tags'] ?? [];
                        $extracted_data['image'] = $json_data['image'] ?? '';
                        
                        if (!empty($extracted_data['title']) && !empty($extracted_data['content'])) {
                            error_log("Successfully extracted from regex pattern");
                            return $extracted_data;
                        }
                    }
                }
            }
        }
    
        // Case 3: Fallback - use the original generated text
        error_log("Using fallback extraction from original text");
        if (!empty($original_text)) {
            // Try to extract from the original text directly
            if (preg_match('/```json\s*(\{[\s\S]*?\})\s*```/', $original_text, $matches)) {
                $json_data = json_decode($matches[1], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                    $extracted_data['title'] = $json_data['title'] ?? '';
                    $extracted_data['content'] = $json_data['content'] ?? '';
                    $extracted_data['tags'] = $json_data['tags'] ?? [];
                    $extracted_data['image'] = $json_data['image'] ?? '';
                    
                    if (!empty($extracted_data['title']) && !empty($extracted_data['content'])) {
                        error_log("Successfully extracted from original text markdown");
                        return $extracted_data;
                    }
                }
            }
        }
    
        // Final fallback
        error_log("Using final fallback - raw content");
        $extracted_data['content'] = is_string($parsed_content) ? $parsed_content : json_encode($parsed_content);
        $extracted_data['title'] = 'Generated Content';
        
        return $extracted_data;
    }

    private static function parse_content_gemini($content) {
        if (is_array($content) || is_object($content)) {
            return $content;
        }
    
        // Extract JSON from code block if present
        if (strpos($content, '```json') !== false) {
            preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $matches);
            $content = $matches[1] ?? $content;
        }
        // Extract HTML from code block if present
        elseif (strpos($content, '```html') !== false) {
            preg_match('/```html\s*([\s\S]*?)\s*```/', $content, $matches);
            $content = $matches[1] ?? $content;
        }
    
        // Clean up any remaining markdown formatting
        $content = preg_replace('/^`+|`+$/', '', $content);
        
        // Attempt JSON decoding only if content looks like JSON
        if (preg_match('/^\s*[\{\[]/', $content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            error_log('JSON parse error: ' . json_last_error_msg());
        }
    
        // Return clean HTML/plain text
        return trim($content);
    }

    public static function parse_elementor_content_gemini($content) {
        // If content is already an array, check for elementor_json key
        if (is_array($content)) {
            if (isset($content['elementor_json'])) {
                return $content['elementor_json'];
            }
            return $content;
        }
        
        // First try to extract the elementor_json section from markdown code block
        if (preg_match('/```elementor_json\s*([\s\S]*?)\s*```/s', $content, $matches)) {
            $json_content = trim($matches[1]);
            
            // Clean the JSON content by removing potential markdown artifacts
            $json_content = preg_replace('/^[\s]*```(?:elementor_json)?[\s]*/m', '', $json_content);
            $json_content = preg_replace('/```[\s]*$/m', '', $json_content);
            
            $decoded = json_decode($json_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            error_log('JSON parse error in elementor_json markdown: ' . json_last_error_msg());
            error_log('Problematic JSON content: ' . substr($json_content, 0, 200) . '...');
        }
        
        // Then try to extract the elementor_json section from plain text format
        if (preg_match('/elementor_json\s*(\{[\s\S]*?\})\s*action_response/s', $content, $matches)) {
            $json_content = trim($matches[1]);
            
            // Fix malformed JSON by removing extra outer braces if they exist
            $json_content = preg_replace('/^[\s]*\{+/', '{', $json_content);
            $json_content = preg_replace('/\}+[\s]*$/', '}', $json_content);
            
            $decoded = json_decode($json_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            error_log('JSON parse error in elementor_json plain text: ' . json_last_error_msg());
        }
        
        // Then try to decode the entire content as JSON (fallback)
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        // If all else fails, return the content as-is
        return $content;
    }


    public static function prepare_refine_messages_gemini(
        $refine_system_prompt, 
        $current_code, 
        $update_description,
        $original_description,
        $screenshot
    ) {
        $messages = [];

        // 1. Initial system prompt (if treated as a user message or context)
        // For Gemini, it's common to put the "system" instructions in the first user message.
        $messages[] = [
            'role' => 'user',
            'content' => $refine_system_prompt
        ];

        // 2. Provide the "current code" and the "original description" as context
        // This is the code the model needs to work with.
        $context_content = [
            [
                'type' => 'text',
                'text' => "Here is the current blog post that needs to be updated:"
            ],
            [
                'type' => 'text',
                'text' => $current_code // The code itself
            ]
        ];

        if (!empty($original_description)) {
            $context_content[] = [
                'type' => 'text',
                'text' => "Original generation description for context: " . $original_description
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $context_content
        ];

        // 3. Provide the specific refinement instructions
        // This is the action the model needs to take.
        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => "Now, just update this blog section based on the following instructions while keeping the current post: " . $update_description
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "data:image/png;base64,{$screenshot}"
                    ]
                ]
            ]
        ];
        
        // For a single refine step, the above is often sufficient.
        // Example: $messages[] = ['role' => 'model', 'content' => 'Okay, I understand. I will refine the code based on your instructions.'];

        return $messages;
    }

    public static function prepare_refine_gemini_for_site_url_with_description(
        $refine_system_prompt, 
        $current_code, 
        $update_description,
        $description,
        $site_datas,
        $mode,
        $output_type
    ) {
        $messages = [];

        // 1. Initial system prompt (if treated as a user message or context)
        // For Gemini, it's common to put the "system" instructions in the first user message.
        $messages[] = [
            'role' => 'user',
            'content' => $refine_system_prompt
        ];

        // 2. Provide the "current code" and the "original description" as context
        // This is the code the model needs to work with.
        $context_content = [
            [
                'type' => 'text',
                'text' => "Here is the current code that needs to be updated:"
            ],
            [
                'type' => 'text',
                'text' => $current_code // The code itself
            ]
        ];

        if (!empty($original_description)) {
            $context_content[] = [
                'type' => 'text',
                'text' => "Original instructions for context: " . $original_description
            ];
        }

        $user_prompt="";


        $site_screenshot=$site_datas['base64Image'];

        $site_texts=$site_datas['site_texts'];

        $site_images=$site_datas['site_images'];

        $site_videos=$site_datas['site_videos'];

        if ($output_type=='html') {
            $user_prompt = "Generate a production-ready HTML. ";
        }else{
            $user_prompt="Generate a production-ready JSON layout for Elementor. ";
        }

        // Append site text if available
        if (!empty($site_texts)) {
            $user_prompt .= "Website Text for reference: " . $site_texts . ". ";
        }

        // Append site images if available, using json_encode to avoid loops
        if (!empty($site_images)) {
            $user_prompt .= "Website Images for reference, use them appropriately (JSON format): " . json_encode($site_images) . ". ";
        }

        // Append site videos if available, using json_encode to avoid loops
        if (!empty($site_videos)) {
            $user_prompt .= "Website Videos for reference, use them appropriately (JSON format): " . json_encode($site_videos) . ". ";
        }

        if(!empty($site_screenshot)){

        // For screenshot mode (image + optional text)
        $context_content[] = [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $user_prompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/png;base64,{$site_screenshot}",
                            ]
                        ]
                    ]
                ]
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $context_content
        ];

        // 3. Provide the specific refinement instructions
        // This is the action the model needs to take.
        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => "Now, just update this code section based on the following instructions while keeping the current design and code: " . $update_description
                ],
            ]
        ];
        
        // For a single refine step, the above is often sufficient.
        // Example: $messages[] = ['role' => 'model', 'content' => 'Okay, I understand. I will refine the code based on your instructions.'];

        return $messages;
    }


    public static function prepare_refine_description_messages_gemini(
        $refine_system_prompt,
        $current_code, 
        $update_description,
        $original_description,
        $logo
    ) {
        $messages = [];

        // 1. Initial system prompt (if treated as a user message or context)
        // For Gemini, it's common to put the "system" instructions in the first user message.
        $messages[] = [
            'role' => 'user',
            'content' => $refine_system_prompt
        ];
        error_log('prepare_refine_description_messages_gemini: ' . $current_code);
        // 2. Provide the "current code" and the "original description" as context
        // This is the code the model needs to work with.
        $context_content = [
            [
                'type' => 'text',
                'text' => "Here is the title/desc about the blog:"
            ],
            [
                'type' => 'text',
                'text' => $current_code // The code itself
            ]
        ];

        if (!empty($original_description)) {
            $context_content[] = [
                'type' => 'text',
                'text' => "Title/Desc: " . $original_description
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $context_content
        ];

        // 3. Provide the specific refinement instructions
        // This is the action the model needs to take.
        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => "Convert it into blog based on the context: " . $update_description
                ],
            ]
        ];
        
        // For a single refine step, the above is often sufficient.
        // Example: $messages[] = ['role' => 'model', 'content' => 'Okay, I understand. I will refine the code based on your instructions.'];

        return $messages;
    }


    public static function prepare_refine_description_messages_gemini_yt(
        $refine_system_prompt,
        $current_code, 
        $update_description,
        $original_description,
        $logo,
        $yt_url
    ) {
        $messages = [];
        $final_user_parts = [];

        // 1. Initial system prompt (as the first user message)
        $messages[] = [
            'role' => 'user',
            'content' => $refine_system_prompt
        ];
        
        // 2. Add Video URL (if not empty) and its primary instruction
        if (!empty($yt_url)) {
            // Add the video file data part (this maps to 'fileData' in prepare_payload)
            $final_user_parts[] = [
                'type' => 'file_data', 
                'fileData' => [
                    'mimeType' => 'video/*', 
                    'fileUri' => $yt_url 
                ]
            ];
            // Add the instruction to use the video as the main source
            $instruction_text = "Convert the ATTACHED YOUTUBE VIDEO content into a blog post.";
        } else {
            // If NO URL, base instruction on the update description
            $instruction_text = "Convert the provided context into a blog post.";
        }
        
        // 3. Add context text and refinement instructions

        // Add context for the existing title/desc (current_code)
        $context_parts = [
            [
                'type' => 'text',
                'text' => "Here is the existing title/description content (if applicable): " . $current_code
            ]
        ];
        if (!empty($original_description)) {
            $context_parts[] = [
                'type' => 'text',
                'text' => "Original instructions for context: " . $original_description
            ];
        }

        // Add the main instruction
        $instruction_parts = [
            'type' => 'text',
            'text' => $instruction_text . " Apply these specific changes: " . $update_description
        ];
        
        // Combine all parts for the final user message
        $final_user_parts = array_merge(
            $final_user_parts, // Contains video Part
            $context_parts,    // Contains current_code context
            [$instruction_parts] // Contains the final prompt
        );

        $messages[] = [
            'role' => 'user',
            'content' => $final_user_parts
        ];
        
        return $messages;
    }

    public static function prepare_messages_gemini($mode, $output_type, $system_prompt, $url, $screenshot, $description) {
        
        $intro_message = [
            'role' => 'user',
            'content' => $system_prompt
        ];

        $user_prompt="";

        if ($output_type=='html') {
            $user_prompt = "Convert this screenshot into a production-ready HTML. ";
        }else{
            $user_prompt="Convert this screenshot into a JSON layout for Elementor. ";
        }

        $user_prompt .= !empty($description) ? 'Additional Instructions: '.$description : '';

        if ($mode === 'url') {
            $messages = [
                $intro_message,
                [
                    'role' => 'user',
                    'content' => "Convert this Figma URL into a JSON layout for Elementor: {$url}"
                ]
            ];
            return $messages;
        }
    
        // For screenshot mode (image + optional text)
        return [
            $intro_message,
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $user_prompt
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:image/png;base64,{$screenshot}"
                        ]
                    ]
                ]
            ]
        ];
    }


    public static function prepare_messages_gemini_for_site_url_with_description($mode, $output_type, $system_prompt, $url, $description, $site_datas) {
        
        $intro_message = [
            'role' => 'user',
            'content' => $system_prompt
        ];

        $user_prompt="";

        $site_screenshot=$site_datas['base64Image'];

        $site_texts=$site_datas['site_texts'];

        $site_images=$site_datas['site_images'];

        $site_videos=$site_datas['site_videos'];

        if ($output_type=='html') {
            $user_prompt = "Generate a production-ready HTML. ";
        }else{
            $user_prompt="Generate a production-ready JSON layout for Elementor. ";
        }

        $user_prompt .= !empty($description) ? 'Instructions: '.$description : '';

        // Append site text if available
        if (!empty($site_texts)) {
            $user_prompt .= "Website Text for reference: " . $site_texts . ". ";
        }

        // Append site images if available, using json_encode to avoid loops
        if (!empty($site_images)) {
            $user_prompt .= "Website Images for reference, use them appropriately (JSON format): " . json_encode($site_images) . ". ";
        }

        // Append site videos if available, using json_encode to avoid loops
        if (!empty($site_videos)) {
            $user_prompt .= "Website Videos for reference, use them appropriately (JSON format): " . json_encode($site_videos) . ". ";
        }

        if(!empty($site_screenshot)){
                 // For screenshot mode (image + optional text)
        return [
                $intro_message,
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $user_prompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/png;base64,{$site_screenshot}",
                            ]
                        ]
                    ]
                ]
            ];
        }
    
    }

    

    public static function prepare_messages_gemini_for_description_not_used_blog($mode, $output_type, $system_prompt, $url, $logo, $description) {
        
        $intro_message = [
            'role' => 'user',
            'content' => $system_prompt
        ];

        $user_prompt="";

        if ($output_type=='html') {
            $user_prompt = "Generate a production-ready HTML. ";
        }else{
            $user_prompt="Generate a production-ready JSON layout for Elementor. ";
        }

        $user_prompt .= !empty($description) ? 'Instructions: '.$description : '';

        if(!empty($logo)){
                 // For screenshot mode (image + optional text)
        return [
                $intro_message,
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $user_prompt
                        ],
                        [
                            'type' => 'text',
                            'text' => 'Please use the attached logo and its color palette (primary, secondary, accent colors) derived from the logo to influence the design, typography, and overall visual style. Incorporate the logo prominently into the design where appropriate.'
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/png;base64,{$logo}",
                                'detail' => 'high' // Important for color extraction
                            ]
                        ]
                    ]
                ]
            ];
        }
    
        // For screenshot mode (image + optional text)
        return [
            $intro_message,
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $user_prompt
                    ],
                ]
            ]
        ];
    }


    public static function prepare_redesign_messages_gemini($mode, $output_type, $system_prompt, $url, $screenshot, $description) {
        
        $intro_message = [
            'role' => 'user',
            'content' => $system_prompt
        ];

        $user_prompt="";

        if ($output_type=='redesign') {
            $user_prompt = "Redesign and convert this screenshot into a production-ready HTML. ";
        }else{
            $user_prompt="Redesign and convert this screenshot into a JSON layout for Elementor. ";
        }

        $user_prompt .= !empty($description) ? 'Instructions to strictly follow: '.$description : '';

        if ($mode === 'url') {
            $messages = [
                $intro_message,
                [
                    'role' => 'user',
                    'content' => "Redesign and convert this Figma URL into a JSON layout for Elementor: {$url}"
                ]
            ];
            return $messages;
        }
    
        // For screenshot mode (image + optional text)
        return [
            $intro_message,
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $user_prompt
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:image/png;base64,{$screenshot}"
                        ]
                    ]
                ]
            ]
        ];
    }


    public static function prepare_figma_messages_gemini($mode, $system_prompt, $url, $figma_content, $description) {
        $intro_message = [
            'role' => 'user',
            'content' => $system_prompt
        ];

        if (!empty($description)) {
            $intro_message[] = [
                'role' => 'user', 
                'content' => 'Additional Instructions: '.$description
            ];
        }
        
        $figma_json = json_encode($figma_content, JSON_PRETTY_PRINT);

        return [
            $intro_message,
            [
                "role" => "user",
                "content" => "Here is the Figma design to convert:"
            ],
            [
                "role" => "user",
                "content" => "FIGMA JSON: " . $figma_json
            ],
        ];

    }

    public static function prepare_redesign_figma_messages_gemini($mode, $system_prompt, $url, $figma_content, $description) {
        $intro_message = [
            'role' => 'user',
            'content' => $system_prompt
        ];

        if (!empty($description)) {
            $intro_message[] = [
                'role' => 'user', 
                'content' => 'Instructions to strictly follow: '.$description
            ];
        }
        
        $figma_json = json_encode($figma_content, JSON_PRETTY_PRINT);

        return [
            $intro_message,
            [
                "role" => "user",
                "content" => "Here is the Figma design to redesign and convert:"
            ],
            [
                "role" => "user",
                "content" => "FIGMA JSON: " . $figma_json
            ],
        ];

    }

    public static function prepare_figma_refine_messages_gemini(
        $refine_system_prompt, 
        $current_code, 
        $update_description,
        $original_description,
        $url,
        $figma_content,
    ) {
        $messages = [];

        // 1. Initial system prompt (if treated as a user message or context)
        // For Gemini, it's common to put the "system" instructions in the first user message.
        $messages[] = [
            'role' => 'user',
            'content' => $refine_system_prompt
        ];

        $figma_json = json_encode($figma_content, JSON_PRETTY_PRINT);

        // 2. Provide the "current code" and the "original description" as context
        // This is the code the model needs to work with.
        $context_content = [
            [
                "type" => "text",
                "text" => "FIGMA JSON: " . $figma_json
            ],
            [
                'type' => 'text',
                'text' => "Here is the current HTML code that needs to be updated:"
            ],
            [
                'type' => 'text',
                'text' => $current_code // The code itself
            ]
        ];

        if (!empty($original_description)) {
            $context_content[] = [
                'type' => 'text',
                'text' => "Original generation description for context: " . $original_description
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $context_content
        ];

        // 3. Provide the specific refinement instructions
        // This is the action the model needs to take.
        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => "Now, just update the HTML code section based on the following instructions while keeping the current design and code: " . $update_description
                ],
            ]
        ];
        
        // For a single refine step, the above is often sufficient.
        // Example: $messages[] = ['role' => 'model', 'content' => 'Okay, I understand. I will refine the code based on your instructions.'];

        return $messages;
    }

    /**
     * Prepares messages for Gemini with system prompt as a file attachment
     *
     * @param string $mode The conversion mode
     * @param string $system_prompt_file_path Path to the system prompt file
     * @param string $url Figma URL
     * @param array $figma_content Processed Figma data
     * @param string $description Additional instructions
     * @return array Messages ready for Gemini API
     * @throws \Exception If file operations fail
     */
    public function prepare_figma_messages_gemini_with_file(
        string $mode,
        string $system_prompt_file_path,
        string $url,
        array $figma_content,
        string $description
    ): array {
        // First upload the system prompt file
        try {
            $file_data = $this->upload_file_to_gemini($system_prompt_file_path);
            error_log("File upload successful. File URI: " . $file_data['fileUri']);
        } catch (\Exception $e) {
            error_log("Failed to upload system prompt file: " . $e->getMessage());
            throw new \Exception("Failed to prepare system prompt file");
        }

        $messages = [];

        // First message references the uploaded file
        $messages[] = [
            'role' => 'user',
            'parts' => [
                [
                    'fileData' => [
                        'mimeType' => $file_data['mimeType'],
                        'fileUri' => $file_data['fileUri']
                    ]
                ],
                ['text' => 'Please use the instructions in the attached file as the system prompt.']
            ]
        ];

        // Add additional instructions if provided
        if (!empty($description)) {
            $messages[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => 'Additional Instructions: ' . $description]
                ]
            ];
        }

        // Convert Figma content to JSON
        $figma_json = json_encode($figma_content, JSON_PRETTY_PRINT);
        if ($figma_json === false) {
            throw new \Exception("Failed to encode Figma content as JSON");
        }

        // Add Figma design context
        $messages[] = [
            'role' => 'user',
            'parts' => [
                ['text' => "Here is the Figma design to convert:"]
            ]
        ];

        // Add the actual Figma JSON
        $messages[] = [
            'role' => 'user',
            'parts' => [
                ['text' => "FIGMA JSON: " . $figma_json]
            ]
        ];

        return $messages;
    }

    /**
     * Uploads a file to Gemini's file storage
     *
     * @param string $file_path Path to the file to upload
     * @return array File data including mimeType and fileUri
     * @throws \Exception If upload fails
     */
    private function upload_file_to_gemini(string $file_path): array {
        if (!file_exists($file_path)) {
            throw new \Exception("File not found: " . $file_path);
        }

        // Determine MIME type
        $mime_type = mime_content_type($file_path);
        if ($mime_type === false) {
            $mime_type = 'text/plain'; // Default if detection fails
        }

        // Read file content
        $file_content = file_get_contents($file_path);
        if ($file_content === false) {
            throw new \Exception("Failed to read file: " . $file_path);
        }

        // Prepare upload request
        $upload_url = "https://generativelanguage.googleapis.com/upload/v1beta/files";
        
        $headers = [
            'Content-Type' => $mime_type,
            'X-Goog-Upload-Protocol' => 'raw',
            'x-goog-api-key' => $this->api_key
        ];

        $response = wp_remote_post($upload_url, [
            'headers' => $headers,
            'body' => $file_content,
            'timeout' => 300
        ]);

        if (is_wp_error($response)) {
            throw new \Exception("File upload failed: " . $response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code !== 200) {
            $error = $body['error']['message'] ?? 'Unknown error occurred during file upload';
            throw new \Exception("Gemini file upload error: " . $error);
        }

        error_log("upload_file_to_gemini: Successful response: " . print_r($body, true));

        if (!isset($body['file']) && !isset($body['name'])) {
            throw new \Exception("Invalid response format from Gemini file upload");
        }

        return [
            'mimeType' => $mime_type,
            'fileUri' => $body['file']['uri'] // Use the full URI from the response
        ];
    }
    
    private function calculate_usage(array $usage) {
        $prompt_tokens = $usage['promptTokenCount'] ?? 0;
        $completion_tokens = $usage['candidatesTokenCount'] ?? 0;
        $total_tokens = $usage['totalTokenCount'] ?? 0;
    
        // Pricing for gemini-2.5-flash-preview-04-17 (Approximate - PLEASE VERIFY)
        $rates = [
            'gemini-2.5-flash-preview-04-17' => [
                'input' => 0.00015, // $0.15 per 1000 tokens
                'output' => 0.0006   // $0.60 per 1000 tokens
            ]
        ];
    
        $model = $this->model;
        if (!isset($rates[$model])) {
            return [
                'prompt_tokens' => $prompt_tokens,
                'completion_tokens' => $completion_tokens,
                'total_tokens' => $total_tokens
            ];
        }
    
        $prompt_cost = ($prompt_tokens / 1000) * $rates[$model]['input'];
        $completion_cost = ($completion_tokens / 1000) * $rates[$model]['output'];
    
        return [
            'prompt_tokens' => $prompt_tokens,
            'completion_tokens' => $completion_tokens,
            'total_tokens' => $total_tokens,
            'estimated_cost' => round($prompt_cost + $completion_cost, 4) // Rounded to 4 decimal places
        ];
    }
}
