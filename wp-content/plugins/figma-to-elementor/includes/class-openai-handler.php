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
        $this->model = 'gpt-4.1';
        $this->temperature = 0.7;
        $this->max_tokens = 32768;
    }

    public function generate_content_without_agentic(array $messages) {
        if (empty($this->api_key)) {
            throw new \Exception(__('API key not configured.', 'figma-to-elementor'));
        }

        $response = $this->make_api_request($messages);
        return $this->process_response($response);
    }

    public function generate_content_with_agentic_old(array $messages, int $iterations) {
        if (empty($this->api_key)) {
            throw new \Exception(__('API key not configured.', 'figma-to-elementor'));
        }

        $turn_count = 1;
        $max_turns = $iterations;
        $result = null;

        while($turn_count <= $max_turns) {
            error_log("Turn $turn_count of $max_turns");

            // Send progress update
            $this->update_progress($turn_count, $max_turns);

            $response = $this->make_api_request($messages);

            $result = $this->process_response($response);

            error_log("Open AI response: " . print_r($result['content'],true));

            $messages[]=['role'=>'user', 'content'=>$result['content']];

            $turn_count++;
            
            // Optional: Add conditions to break early if response is good enough
            // if ($this->is_response_satisfactory($result['content'])) {
            //     break;
            // }
        }

        // $response = $this->make_api_request($messages);
        return $result;
    }

    public function generate_content_with_agentic(array $messages, int $iterations) {
        if (empty($this->api_key)) {
            throw new \Exception(__('API key not configured.', 'figma-to-elementor'));
        }
    
        $result = null;
        // $conversation = $messages; // Initialize conversation ONCE before loop
    
        for ($turn_count = 1; $turn_count <= $iterations; $turn_count++) {
            try {
                // Remove this line - it resets the conversation each iteration
                $conversation = $messages;
                
                $this->update_progress($turn_count, $iterations);
                error_log("Turn $turn_count of $iterations");

                // Token counting and trimming
                $token_count = $this->count_tokens($conversation);
                error_log("Token count before request: $token_count");

                if ($token_count > 120000) {
                    error_log("Trimming conversation due to token limit");
                    // $conversation = $this->trim_conversation($conversation);
                }
    
                $response = $this->make_api_request($conversation);
                $result = $this->process_response($response);
                
                error_log("OpenAI response: " . print_r($result['content'], true));
    
                // Add AI response to conversation
                $conversation[] = [
                    'role' => 'assistant',
                    'content' => $result['content'] // Removed 'Generated Output: ' prefix
                ];
                
                // Prepare next user input if more iterations remain
                if ($turn_count < $iterations) {
                    $conversation[] = [
                        'role' => 'user',
                        'content' => 'Please review and update the output as described in the description.'
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

    public static function parse_elementor_content_open_ai($content) {
        // First try to extract the elementor_json section
        if (preg_match('/elementor_json\s*\{\s*(\{[\s\S]*?\})\s*\}\s*action_response/s', $content, $matches)) {
            $json_content = trim($matches[1]);
            
            $decoded = json_decode($json_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            error_log('JSON parse error in elementor-json: ' . json_last_error_msg() . ' Content: ' . $json_content);
        }
        
        // If not found, try to parse the whole content as JSON
        if (preg_match('/^\s*[\{\[]/', $content)) {
            // Try to extract JSON between outer braces if they exist
            if (preg_match('/^\s*\{([\s\S]*)\}\s*$/s', $content, $outer_matches)) {
                $content_to_parse = trim($outer_matches[1]);
                $decoded = json_decode('{' . $content_to_parse . '}', true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            
            // If that fails, try parsing the content directly
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            error_log('JSON parse error in full content: ' . json_last_error_msg() . ' Content: ' . $content);
        }
        
        return $content;
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
        $lock_key = 'f2e_progress_lock_' . get_current_user_id();
        $progress_key = 'f2e_progress_' . get_current_user_id();
        delete_transient($lock_key);
        delete_transient($progress_key);
    }

    private function update_progress($current, $total) {
        // Use a lock to prevent race conditions
        $lock_key = 'f2e_progress_lock_' . get_current_user_id();
        
        // Try to get a lock (wait up to 1 second)
        $lock_acquired = false;
        for ($i = 0; $i < 10; $i++) {
            if (!get_transient($lock_key)) {
                set_transient($lock_key, '1', 1); // 1 second lock
                $lock_acquired = true;
                break;
            }
            usleep(100000); // 0.1 second
        }
        
        if ($lock_acquired) {
            set_transient(
                'f2e_progress_' . get_current_user_id(),
                [
                    'current' => $current,
                    'total' => $total,
                    'timestamp' => time()
                ],
                60 // Expire after 1 minute
            );
            delete_transient($lock_key); // Release lock
        }
    }

    private function make_api_request(array $messages) {
        $body = [
            "model" => $this->model,
            "messages" => $messages,
            "temperature" => $this->temperature,
            "max_tokens" => $this->max_tokens
        ];

        $response = wp_remote_post("https://api.openai.com/v1/chat/completions", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            'body' => json_encode($body),
            'timeout' => 10000,
            'sslverify' => true
        ]);

        return $response;
    }

    private function process_response($response) {
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($status_code !== 200) {
            throw new \Exception($body['error']['message'] ?? __('Unknown error occurred.', 'figma-to-elementor'));
        }

        $content = $body['choices'][0]['message']['content'] ?? '';
        
        // Extract JSON from markdown if present
        if (strpos($content, '```json') !== false) {
            preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $matches);
            $content = $matches[1] ?? $content;
        }

        return [
            'content' => $this->parse_content_openai($content),
            'usage' => $this->calculate_usage($body['usage'] ?? [])
        ];
    }

    public static function prepare_figma_messages_openai($mode, $system_prompt, $url, $figma_content, $description) {
        $figma_json = json_encode($figma_content, JSON_PRETTY_PRINT);
    
        $messages = [
            [
                "role" => "system",
                "content" => $system_prompt
            ],
            [
                "role" => "user",
                "content" => "Here is the Figma design to convert:"
            ],
            [
                "role" => "user",
                "content" => "FIGMA JSON: " . $figma_json
            ]
        ];
    
        if (!empty($description)) {
            $messages[] = [
                'role' => 'user', // Changed to valid role
                'content' => "Additional details: " . $description
            ];
        }
    
        return $messages;
    }
    
    public static function prepare_messages_openai($mode, $system_prompt, $url, $screenshot, $description) {
        $messages = [
            [
                "role" => "system",
                "content" => $system_prompt
            ]
        ];
    
        if ($mode === 'url') {
            $messages[] = [
                "role" => "user",
                "content" => sprintf(__("Convert this Figma URL into a JSON layout for Elementor: %s", 'figma-to-elementor'), $url)
            ];
        } else {
            $messages[] = [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => "Convert this screenshot to HTML:"
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => [
                            "url" => "data:image/png;base64," . $screenshot
                        ]
                    ]
                ]
            ];
        }
    
        if (!empty($description)) {
            $messages[] = [
                'role' => 'user', // Changed to valid role
                'content' => "Additional details: " . $description
            ];
        }
    
        return $messages;
    }

    private static function parse_content_openai($content) {
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

    private function calculate_usage(array $usage) {
        $rates = [
            'gpt-4o' => ['prompt' => 0.005, 'completion' => 0.015],
            'gpt-4' => ['prompt' => 0.03, 'completion' => 0.06],
            'gpt-3.5-turbo' => ['prompt' => 0.001, 'completion' => 0.002]
        ];

        $model = $this->model;
        if (!isset($rates[$model])) return [];

        $prompt_cost = ($usage['prompt_tokens'] / 1000) * $rates[$model]['prompt'];
        $completion_cost = ($usage['completion_tokens'] / 1000) * $rates[$model]['completion'];

        return [
            'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
            'completion_tokens' => $usage['completion_tokens'] ?? 0,
            'estimated_cost' => $prompt_cost + $completion_cost
        ];
    }
}