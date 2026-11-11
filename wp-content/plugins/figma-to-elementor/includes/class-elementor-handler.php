<?php
namespace FigmaToElementor;

defined('ABSPATH') || exit;

class Elementor_Handler {
   

    public static function handle_generate_elementor_json($llm_source, $mode, $html_code, $iterations) {
        self::clean_output_buffers();
        
        try {
            if (!class_exists('FigmaToElementor\OpenAI_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-openai-handler.php';
            }
            if (!class_exists('FigmaToElementor\Gemini_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-gemini-handler.php';
            }

            // $system_prompt = self::get_elementor_system_prompt_content();
            $system_prompt = self::get_screenshot_elementor_system_prompt_content();

            if($llm_source=="OpenAI"){

                // Open AI
                $ai_handler = new OpenAI_Handler();
                $messages = self::prepare_elementor_messages_openai($mode, $system_prompt, $html_code);
                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);

                $content = self::parse_elementor_content_open_ai($result['content']);
                $action_content = self::parse_elementor_action_response_openai($result['content']);

            }
            else if($llm_source=="Google Gemini"){

                // Gemini API
                $ai_handler = new Gemini_Handler();
                $messages = self::prepare_elementor_messages_gemini($mode, $system_prompt, $html_code);
                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);
    
                $content = self::parse_elementor_content_gemini($result['content']);
                $action_content = self::parse_elementor_action_response_gemini($result['content']);

            }

            
            // For debugging, use print_r with return parameter set to true
            error_log('parse_elementor_content: ' . print_r($content, true));
            error_log('parse_elementor_action_response: ' . print_r($action_content, true));
            
            return [
                'success' => true,
                'input_type' => $mode,
                'content' => $content,
                'action_response' => $action_content,
                'usage' => $result['usage']
            ];

        } catch (\Exception $e) {
            self::clean_output_buffers();
            error_log('API Handler Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => is_numeric($e->getCode()) ? $e->getCode() : 400,
            ];
        }
    }


    public static function handle_generate_elementor_json_screenshot($llm_source, $mode, $html_code, $iterations) {
        self::clean_output_buffers();
        
        try {
            if (!class_exists('FigmaToElementor\OpenAI_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-openai-handler.php';
            }
            if (!class_exists('FigmaToElementor\Gemini_Handler')) {
                require_once F2E_PLUGIN_DIR . 'includes/class-gemini-handler.php';
            }

            // $system_prompt = self::get_elementor_system_prompt_content();
            $system_prompt = self::get_screenshot_elementor_system_prompt_content();

            if($llm_source=="OpenAI"){

                // Open AI
                $ai_handler = new OpenAI_Handler();
                $messages = self::prepare_elementor_messages_openai($mode, $system_prompt, $html_code);
                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);

                $content = self::parse_elementor_content_open_ai($result['content']);
                $action_content = self::parse_elementor_action_response_openai($result['content']);

            }
            else if($llm_source=="Google Gemini"){

                // Gemini API
                $ai_handler = new Gemini_Handler();
                $messages = self::prepare_elementor_messages_gemini($mode, $system_prompt, $html_code);
                $result = $ai_handler->generate_content_with_agentic($messages, $iterations);
    
                $content = self::parse_elementor_content_gemini($result['content']);
                $action_content = self::parse_elementor_action_response_gemini($result['content']);

            }

            
            // For debugging, use print_r with return parameter set to true
            error_log('parse_elementor_content: ' . print_r($content, true));
            error_log('parse_elementor_action_response: ' . print_r($action_content, true));
            
            return [
                'success' => true,
                'input_type' => $mode,
                'content' => $content,
                'action_response' => $action_content,
                'usage' => $result['usage']
            ];

        } catch (\Exception $e) {
            self::clean_output_buffers();
            error_log('API Handler Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => is_numeric($e->getCode()) ? $e->getCode() : 400,
            ];
        }
    }
    

    private static function clean_output_buffers() {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }


    private static function prepare_elementor_messages_openai($mode, $system_prompt, $html) {
        return [
            [
                "role" => "system",
                "content" => $system_prompt
            ],
            [
                "role" => "user",
                "content" => sprintf(__("Convert this HTML into a JSON layout for Elementor: %s", 'figma-to-elementor'), $html)
            ]
        ];
    }

    private static function prepare_elementor_messages_gemini($mode, $system_prompt, $html) {
        $intro_message = [
            'role' => 'user',
            'content' => $system_prompt
        ];
    
        return [
            $intro_message,
            [
                'role' => 'user',
                'content' => "Convert this HTML into a JSON layout for Elementor: {$html}"
            ]
        ];

    }

    private static function parse_elementor_content_open_ai($content) {
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

    // private static function parse_elementor_content_gemini($content) {
    //     // First try to extract the elementor_json section
    //     if (preg_match('/elementor_json\s*\{\s*(\{[\s\S]*?\})\s*\}\s*action_response/s', $content, $matches)) {
    //         $json_content = trim($matches[1]);
            
    //         $decoded = json_decode($json_content, true);
    //         if (json_last_error() === JSON_ERROR_NONE) {
    //             return $decoded;
    //         }
    //         error_log('JSON parse error in elementor-json: ' . json_last_error_msg() . ' Content: ' . $json_content);
    //     }
        
    //     // If not found, try to parse the whole content as JSON
    //     if (preg_match('/^\s*[\{\[]/', $content)) {
    //         // Try to extract JSON between outer braces if they exist
    //         if (preg_match('/^\s*\{([\s\S]*)\}\s*$/s', $content, $outer_matches)) {
    //             $content_to_parse = trim($outer_matches[1]);
    //             $decoded = json_decode('{' . $content_to_parse . '}', true);
    //             if (json_last_error() === JSON_ERROR_NONE) {
    //                 return $decoded;
    //             }
    //         }
            
    //         // If that fails, try parsing the content directly
    //         $decoded = json_decode($content, true);
    //         if (json_last_error() === JSON_ERROR_NONE) {
    //             return $decoded;
    //         }
    //         error_log('JSON parse error in full content: ' . json_last_error_msg() . ' Content: ' . $content);
    //     }
        
    //     return $content;
    // }

    private static function parse_elementor_content_gemini($content) {
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

    private static function parse_elementor_action_response_gemini($content) {
        // Try to extract the action_response section
        if (preg_match('/action_response\s*(\{[\s\S]*?\})\s*$/s', $content, $matches)) {
            $response_content = trim($matches[1]);
            
            // Fix the malformed JSON by removing the extra outer braces if they exist
            if (substr($response_content, 0, 2) === '{{' && substr($response_content, -2) === '}}') {
                $response_content = substr($response_content, 1, -1);
            }
            
            $decoded = json_decode($response_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            error_log('JSON parse error in action_response: ' . json_last_error_msg());
        }
        return null;
    }

    
    private static function parse_elementor_action_response_openai($content) {
        // Try to extract the action_response section
        if (preg_match('/action_response\s*(\{[\s\S]*?\})\s*$/s', $content, $matches)) {
            $response_content = trim($matches[1]);
            
            // Fix the malformed JSON by removing the extra outer braces if they exist
            if (substr($response_content, 0, 2) === '{{' && substr($response_content, -2) === '}}') {
                $response_content = substr($response_content, 1, -1);
            }
            
            $decoded = json_decode($response_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            error_log('JSON parse error in action_response: ' . json_last_error_msg());
        }
        return null;
    }


    private static function get_elementor_system_prompt_content() {
        self::clean_output_buffers();

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/elementor/class-agentic-prompt-generate-elementor-json.php';
        
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

        if (!class_exists('FigmaToElementor\PromptGenerateElementor')) {
            error_log('PromptGenerateElementor class not found after inclusion');
            return self::get_default_system_prompt();
        }

        try {
            $prompt = PromptGenerateElementor::get_system_prompt();
            return is_string($prompt) ? trim($prompt) : self::get_default_system_prompt();
        } catch (\Exception $e) {
            error_log('Prompt generation error: ' . $e->getMessage());
            return self::get_default_system_prompt();
        }
    }

    private static function get_screenshot_elementor_system_prompt_content() {
        self::clean_output_buffers();

        // $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/html/class-prompt-generate-html.php';
        $prompt_file = F2E_PLUGIN_DIR . 'prompts/system/elementor/class-agentic-prompt-generate-elementor-json-screenshot.php';
        
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


    private static function get_default_system_prompt() {
        return "Convert the design into production-ready HTML using semantic markup and modern CSS. " . 
               "Include proper accessibility attributes and responsive design considerations.";
    }
}