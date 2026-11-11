<?php
namespace FigmaToElementor;

defined('ABSPATH') || exit;

class OpenAI_Handler {
    private $api_key;
    private $model;
    private $temperature;
    private $max_tokens;

    public function __construct() {
        $this->api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
        $this->model = 'gpt-4o'; // Updated to latest vision model
        $this->temperature = 0.7;
        $this->max_tokens = 128000; // Supports 128k context
    }

    // ========================
    // FILE UPLOAD METHODS
    // ========================

    /**
     * Uploads a file to OpenAI's servers
     */
    private function upload_file(string $file_path, string $purpose = 'assistants') {
        if (!file_exists($file_path)) {
            throw new \Exception("File not found: $file_path");
        }

        $response = wp_remote_post('https://api.openai.com/v1/files', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'multipart/form-data'
            ],
            'body' => [
                'purpose' => $purpose,
                'file' => new CURLFile($file_path)
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new \Exception('File upload failed: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['id'] ?? null;
    }

    /**
     * Creates a temporary file with content
     */
    private function create_temp_file($content, $extension = 'json') {
        $temp_file = tempnam(sys_get_temp_dir(), 'openai_') . '.' . $extension;
        file_put_contents($temp_file, $content);
        register_shutdown_function(function() use ($temp_file) {
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
        });
        return $temp_file;
    }

    // ========================
    // CORE PROCESSING METHODS
    // ========================

    /**
     * Main entry point for file-based processing
     */
    public function generate_with_files(
        string $system_prompt,
        string $user_prompt,
        array $figma_json,
        string $image_base64 = null,
        int $iterations = 1,
        string $description = ''
    ) {
        try {
            // 1. Prepare files
            $files = [];
            $json_file = $this->create_temp_file(json_encode($figma_json, JSON_PRETTY_PRINT));
            $files[] = ['type' => 'json', 'path' => $json_file];
            
            if ($image_base64) {
                $image_file = $this->create_temp_file(base64_decode($image_base64), 'png');
                $files[] = ['type' => 'image', 'path' => $image_file];
            }

            // 2. Upload files
            $file_references = [];
            foreach ($files as $file) {
                $file_id = $this->upload_file($file['path']);
                if (!$file_id) {
                    throw new \Exception("Failed to upload {$file['type']} file");
                }
                $file_references[] = [
                    'type' => $file['type'],
                    'file_id' => $file_id
                ];
            }

            // 3. Prepare messages
            $messages = $this->prepare_file_messages(
                $system_prompt,
                $user_prompt,
                $file_references,
                $description
            );

            // 4. Process with agentic flow
            return $this->generate_content_with_agentic($messages, $iterations);

        } catch (\Exception $e) {
            error_log("File processing error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepares messages with file references
     */
    private function prepare_file_messages(
        string $system_prompt,
        string $user_prompt,
        array $file_references,
        string $description = ''
    ) {
        $message_parts = [
            ['type' => 'text', 'text' => $user_prompt]
        ];

        foreach ($file_references as $file) {
            if ($file['type'] === 'json') {
                $message_parts[] = [
                    'type' => 'text',
                    'text' => "FIGMA JSON FILE REFERENCE: {$file['file_id']}"
                ];
            } else {
                $message_parts[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "file://{$file['file_id']}"
                    ]
                ];
            }
        }

        if (!empty($description)) {
            $message_parts[] = [
                'type' => 'text',
                'text' => "ADDITIONAL DETAILS: $description"
            ];
        }

        return [
            [
                'role' => 'system',
                'content' => $system_prompt
            ],
            [
                'role' => 'user',
                'content' => $message_parts
            ]
        ];
    }

    // ========================
    // EXISTING METHODS (UPDATED)
    // ========================

    public function generate_content_with_agentic(array $messages, int $iterations) {
        if (empty($this->api_key)) {
            throw new \Exception(__('API key not configured.', 'figma-to-elementor'));
        }

        $result = null;
        $conversation = $messages;

        for ($turn_count = 1; $turn_count <= $iterations; $turn_count++) {
            try {
                $this->update_progress($turn_count, $iterations);
                error_log("Turn $turn_count of $iterations");

                // Process file references in messages
                $processed_messages = $this->process_message_files($conversation);

                $response = $this->make_api_request($processed_messages);
                $result = $this->process_response($response);

                // Add AI response to history
                $conversation[] = [
                    'role' => 'assistant',
                    'content' => $result['content']
                ];

                if ($turn_count < $iterations) {
                    $conversation[] = [
                        'role' => 'user',
                        'content' => 'Please improve: ' . 
                                     "1. Code structure\n" .
                                     "2. Design accuracy\n" .
                                     "3. Implementation details"
                    ];
                }

            } catch (\Exception $e) {
                $this->clear_progress_from_transient();
                throw $e;
            }
        }

        $this->clear_progress_from_transient();
        return $result;
    }

    private function process_message_files(array $messages) {
        return array_map(function($message) {
            if (!isset($message['content']) || !is_array($message['content'])) {
                return $message;
            }

            $processed_content = [];
            foreach ($message['content'] as $part) {
                if ($part['type'] === 'image_url' && strpos($part['image_url']['url'], 'file://') === 0) {
                    // Convert file reference to direct upload if needed
                    $processed_content[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $part['image_url']['url']
                        ]
                    ];
                } else {
                    $processed_content[] = $part;
                }
            }

            $message['content'] = $processed_content;
            return $message;
        }, $messages);
    }

    // ========================
    // COMPATIBILITY METHODS
    // ========================

    public static function prepare_figma_messages_openai($mode, $system_prompt, $url, $figma_content, $description) {
        $handler = new self();
        return $handler->generate_with_files(
            $system_prompt,
            $mode === 'url' ? "Convert this Figma URL: $url" : "Convert this Figma design",
            $figma_content,
            null,
            1,
            $description
        );
    }

    // ... (keep all your existing helper methods like update_progress, count_tokens, etc.)
}