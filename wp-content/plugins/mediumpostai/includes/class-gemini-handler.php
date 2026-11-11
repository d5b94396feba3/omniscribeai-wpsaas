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
        $this->model = 'gemini-2.5-flash';
        $this->temperature = 0.7;
        $this->max_tokens = 4096;
        $this->top_p = 0.9;
        $this->thinking_budget = 2048;
    }

    public function generate_content_without_agentic(array $messages) {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        $payload = $this->prepare_payload($messages);
        $response = $this->make_api_request($payload);

        return $this->process_response($response);
    }

/**
     * Generates Base64 image using Gemini Image API via the v1beta/interactions endpoint.
     */
    public function generate_image_base64(string $blog_title, string $system_prompt, string $content_type = 'medium'): string {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        $aspect_ratio = $this->get_image_aspect_ratio($content_type);
        $clean_title = !empty($blog_title) ? sanitize_text_field($blog_title) : 'Professional Blog Cover';

        // Endpoint and model per official Gemini API specifications
        $image_model = 'gemini-2.5-flash-image'; 
        $url = "https://generativelanguage.googleapis.com/v1beta/interactions";

        $base_prompt = "Main subject: A professional, high-quality cover image illustrating the theme of '{$clean_title}'. Style: Corporate, clean, and modern.";
        $prompt = !empty($system_prompt) ? trim($system_prompt) . " Subject matter: " . $base_prompt : $base_prompt;

        $payload = [
            'model' => $image_model,
            'input' => [
                [
                    'type' => 'text',
                    'text' => $prompt
                ]
            ],
            'response_format' => [
                'type' => 'image',
                'aspect_ratio' => $aspect_ratio
            ]
        ];

        $response = wp_remote_post($url, [
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
        $raw_body = wp_remote_retrieve_body($response);
        $body = json_decode($raw_body, true);

        if ($status_code !== 200) {
            $error_detail = $body['error']['message'] ?? $raw_body;
            throw new \Exception("Image Generation API Error: HTTP $status_code. Details: " . $error_detail);
        }

        // Extract base64 image data from returned response steps
        if (isset($body['steps'])) {
            foreach ($body['steps'] as $step) {
                if (isset($step['content'])) {
                    foreach ($step['content'] as $block) {
                        if (isset($block['type']) && $block['type'] === 'image' && !empty($block['data'])) {
                            return $block['data'];
                        }
                    }
                }
            }
        }

        if (isset($body['output_image']['data'])) {
            return $body['output_image']['data'];
        }

        throw new \Exception(__('Invalid response format from Image Generation API.', 'medium-post-ai'));
    }

    /**
     * Generates an image for a blog post, saves to WP uploads, returns file URL.
     */
    public function generate_image(string $blog_title, string $system_prompt, string $content_type = 'medium'): string {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        $base64_image = $this->generate_image_base64($blog_title, $system_prompt, $content_type);

        $binary_data = base64_decode($base64_image);
        if ($binary_data === false) {
            throw new \Exception(__('Failed to decode Base64 image data.', 'medium-post-ai'));
        }

        $upload_dir = wp_upload_dir();
        $file_title = sanitize_title($blog_title) ?: 'generated-image';
        $file_name = $file_title . '-' . time() . '.png';

        $file_path = $upload_dir['path'] . '/' . $file_name;
        $file_url = $upload_dir['url'] . '/' . $file_name;

        $file_put_result = file_put_contents($file_path, $binary_data);

        if ($file_put_result === false) {
            throw new \Exception(__('Failed to save image file to disk. Check directory permissions.', 'medium-post-ai'));
        }

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

        return $file_url;
    }

    private function get_image_aspect_ratio(string $content_type): string {
        switch (strtolower($content_type)) {
            case 'facebook':
            case 'linkedin':
            case 'x-twitter':
                return '16:9';
            case 'instagram':
                return '3:4'; 
            case 'medium':
            default:
                return '16:9'; 
        }
    }

    public function generate_content_with_agentic(array $messages, int $iterations) {
        if (empty($this->api_key)) {
            throw new \Exception(__('Google API key not configured.', 'medium-post-ai'));
        }

        $result = null;
        $initial_payload = $this->prepare_payload($messages);
        $total_iterations = max(1, $iterations);

        $this->clear_progress_from_transient();

        for ($turn_count = 1; $turn_count <= $total_iterations; $turn_count++) {
            try {
                error_log("Turn $turn_count of $total_iterations");
                $this->update_progress($turn_count, $total_iterations);

                $payload = $initial_payload;
                $token_count = $this->count_tokens($payload);

                $response = $this->make_api_request($payload);
                $result = $this->process_response($response);

                if ($turn_count < $total_iterations) {
                    $payload['contents'][] = [
                        'role' => 'model',
                        'parts' => [
                            ['text' => $result['content']]
                        ]
                    ];

                    $payload['contents'][] = [
                        'role' => 'user',
                        'parts' => [
                            ['text' => "Please review and update the post to make 100% appropriate for the platform."]
                        ]
                    ];
                }

            } catch (\Exception $e) {
                error_log("Iteration $turn_count failed: " . $e->getMessage());
                throw $e;
            }
        }

        $this->clear_progress_from_transient();
        return $result;
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
                120
            );
        } catch (\Exception $e) {
            error_log("Failed to set progress transient: " . $e->getMessage());
        }
    }

    private function prepare_payload(array $messages) {
        $contents = [];

        foreach ($messages as $message) {
            $content = ['role' => $message['role']];
            $parts = [];

            if (isset($message['parts'])) {
                foreach ($message['parts'] as $part) {
                    if (isset($part['text'])) {
                        $parts[] = ['text' => $part['text']];
                    } elseif (isset($part['fileData'])) {
                        $parts[] = [
                            'fileData' => [
                                'mimeType' => $part['fileData']['mimeType'],
                                'fileUri' => $part['fileData']['fileUri']
                            ]
                        ];
                    } elseif (isset($part['inlineData'])) {
                        $parts[] = $part; 
                    }
                }
            } elseif (is_array($message['content'])) {
                foreach ($message['content'] as $part) {
                    if (isset($part['type'])) {
                        if ($part['type'] === 'text') {
                            $parts[] = ['text' => $part['text']];
                        } elseif ($part['type'] === 'image_url') {
                            $parts[] = [
                                'inlineData' => [
                                    'mimeType' => 'image/png',
                                    'data' => str_replace('data:image/png;base64,', '', $part['image_url']['url'])
                                ]
                            ];
                        } elseif ($part['type'] === 'file_data') {
                            $parts[] = [
                                'fileData' => [
                                    'mimeType' => $part['fileData']['mimeType'],
                                    'fileUri' => $part['fileData']['fileUri']
                                ]
                            ];
                        }
                    }
                }
            } else {
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
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        return wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key,
            ],
            'body' => json_encode($payload),
            'timeout' => 900,
            'sslverify' => true
        ]);
    }

    private function process_response($response) {
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);
        $body = json_decode($raw_body, true);

        if ($status_code !== 200) {
            $error = $body['error']['message'] ?? __('Unknown error occurred with Gemini API.', 'medium-post-ai');
            throw new \Exception($error);
        }

        $generated_text = '';
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $generated_text = $body['candidates'][0]['content']['parts'][0]['text'];
        } else {
            throw new \Exception(__('Invalid response format from Gemini API: No text part.', 'medium-post-ai'));
        }

        $extracted_data = $this->extract_json_data($generated_text); 
        $final_content = $extracted_data['content'] ?? $generated_text;

        return [
            'content' => $final_content,
            'usage' => $this->calculate_usage($body['usageMetadata'] ?? []),
            'extracted_data' => $extracted_data
        ];
    }

    private function extract_json_data(string $generated_text): array {
        if (preg_match('/({[\s\S]*})/s', $generated_text, $matches)) {
            $clean_json = str_replace(["\r", "\n"], " ", $matches[1]);
            $json_data = json_decode($clean_json, true);

            if (json_last_error() === JSON_ERROR_NONE && !empty($json_data['title'])) {
                return [
                    'title'   => trim($json_data['title']),
                    'content' => $json_data['content'], 
                    'tags'    => (array)($json_data['tags'] ?? []),
                    'image'   => $json_data['image'] ?? ''
                ];
            }
        }

        return $this->manual_regex_fallback($generated_text);
    }

    private function manual_regex_fallback(string $text): array {
        $data = ['title' => '', 'content' => '', 'tags' => [], 'image' => ''];

        if (preg_match('/"title"\s*:\s*"([\s\S]*?)"\s*,\s*"content"/i', $text, $m)) {
            $data['title'] = trim($m[1]);
        }

        if (preg_match('/"tags"\s*:\s*\[([\s\S]*?)\]/i', $text, $m)) {
            preg_match_all('/"([^"]+)"/', $m[1], $tag_matches);
            $data['tags'] = $tag_matches[1] ?? [];
        }

        if (preg_match('/"content"\s*:\s*"([\s\S]*?)"\s*,\s*"(tags|image)"/i', $text, $m)) {
            $data['content'] = stripslashes($m[1]);
        }

        return $data;
    }
    
    /**
     * Prepares messages for Gemini when generating a blog post from a description.
     */
    public function prepare_messages_gemini_for_description(
        $mode,
        $output_type,
        $system_prompt,
        $url,
        $logo,
        $description,
        $target_keyword = '',
        $audience_level = '',
        $desired_tone = '',
        $language = '',
        $required_length = '',
        $call_to_action = ''
    ) {
        $messages = [];

        // System prompt as first user message
        if (!empty($system_prompt)) {
            $messages[] = [
                'role' => 'user',
                'content' => $system_prompt
            ];
        }

        // Build prompt instructions with advanced settings
        $user_instruction = "Generate a comprehensive blog post based on the following context:\n\n" . $description;

        if (!empty($target_keyword)) {
            $user_instruction .= "\nTarget SEO Keyword: " . $target_keyword;
        }
        if (!empty($audience_level)) {
            $user_instruction .= "\nAudience Level: " . $audience_level;
        }
        if (!empty($desired_tone)) {
            $user_instruction .= "\nDesired Tone: " . $desired_tone;
        }
        if (!empty($language)) {
            $user_instruction .= "\nOutput Language: " . $language;
        }
        if (!empty($required_length)) {
            $user_instruction .= "\nRequired Length: " . $required_length;
        }
        if (!empty($call_to_action)) {
            $user_instruction .= "\nMANDATORY Call to Action: " . $call_to_action;
        }

        $messages[] = [
            'role' => 'user',
            'content' => $user_instruction
        ];

        return $messages;
    }

    public static function prepare_refine_description_messages_gemini_yt(
        $refine_system_prompt,
        $current_code, 
        $update_description,
        $original_description,
        $logo,
        $yt_url,
        $target_keyword, 
        $audience_level, 
        $desired_tone,
        $language,
        $required_length,
        $call_to_action
    ) {
        $messages = [];
        $final_user_parts = [];

        $messages[] = [
            'role' => 'user',
            'content' => $refine_system_prompt
        ];

        $advanced_instruction = "";
        if (!empty($target_keyword)) $advanced_instruction .= " Target SEO Keyword: '" . trim($target_keyword) . "'.";
        if (!empty($audience_level)) $advanced_instruction .= " Audience Level: '" . trim($audience_level) . "'.";
        if (!empty($desired_tone)) $advanced_instruction .= " Desired Tone: '" . trim($desired_tone) . "'.";
        if (!empty($language)) $advanced_instruction .= " Output Language: '" . trim($language) . "'.";
        if (!empty($required_length)) $advanced_instruction .= " Required Length: '" . trim($required_length) . "'.";
        if (!empty($call_to_action)) $advanced_instruction .= " MANDATORY Call to Action: '" . trim($call_to_action) . "'.";

        if (!empty($yt_url)) {
            $final_user_parts[] = [
                'type' => 'file_data', 
                'fileData' => [
                    'mimeType' => 'video/*', 
                    'fileUri' => $yt_url 
                ]
            ];
            $instruction_text = "Convert the ATTACHED YOUTUBE VIDEO content into a blog post.";
        } else {
            $instruction_text = "Convert the provided context into a blog post.";
        }

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

        $instruction_parts = [
            'type' => 'text',
            'text' => $instruction_text . " Apply these specific changes: " . $update_description . $advanced_instruction
        ];

        $final_user_parts = array_merge($final_user_parts, $context_parts, [$instruction_parts]);

        $messages[] = [
            'role' => 'user',
            'content' => $final_user_parts
        ];

        return $messages;
    }

    private function calculate_usage(array $usage) {
        $prompt_tokens = $usage['promptTokenCount'] ?? 0;
        $completion_tokens = $usage['candidatesTokenCount'] ?? 0;
        $total_tokens = $usage['totalTokenCount'] ?? 0;

        return [
            'prompt_tokens' => $prompt_tokens,
            'completion_tokens' => $completion_tokens,
            'total_tokens' => $total_tokens
        ];
    }
}