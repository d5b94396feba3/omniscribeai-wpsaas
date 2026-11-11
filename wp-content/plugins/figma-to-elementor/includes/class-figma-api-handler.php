<?php
namespace FigmaToElementor;

defined('ABSPATH') || exit;

class FigmaAPI_Handler {
    private $api_key;
    private $default_url; // Consider if this is still necessary if always passing a URL or key

    public function __construct() {
        $this->api_key = defined('FIGMA_API_KEY') ? FIGMA_API_KEY : '';
        $this->default_url="https://www.figma.com/design/U7Ci92WexNR3aQv8xV6wvo/TEST-SAMPLE-HOMEPAGE?node-id=0-1&p=f";
    }

    public function generate_content_without_agentic(array $options) {
        // 1. Initial message: Validating input
        $this->update_progress("Validating Figma input...");

        if (empty($this->api_key)) {
            $this->update_progress("Error: Figma API key not configured.");
            throw new \Exception(__('Figma API key not configured.', 'figma-to-elementor'));
        }
    
        try {
            $response = $this->make_figma_api_request($options);
            $processed_content = $this->process_figma_response($response);
            
            // 5. Final message: Content generation completed
            $this->update_progress("Figma content generation completed.");
            return $processed_content; // This now directly returns the array from process_figma_response
        } catch (\Exception $e) {
            $this->update_progress("Error: " . $e->getMessage()); // Update progress on exception
            error_log("Figma content generation failed: " . $e->getMessage());
            throw $e; // Re-throw the exception to be caught upstream
        }
    }
    
    private function make_figma_api_request(array $options) {
        $file_key = '';
        $node_id = '';

        // Determine file_key and node_id from options
        if (isset($options['url'])) {
            $this->update_progress("Parsing Figma URL...");
            $url_components = parse_url($options['url']);
            parse_str($url_components['query'] ?? '', $query_params);
            
            $path_parts = explode('/', trim($url_components['path'], '/'));
            $file_key = $path_parts[1] ?? '';
            $node_id = str_replace('-', ':', $query_params['node-id'] ?? '');
        } else {
            $file_key = $options['file_key'] ?? '';
            $node_id = $options['node_ids'] ?? '';
        }
    
        if (empty($file_key)) {
            $this->update_progress("Error: Figma file key is required.");
            throw new \Exception(__('Figma file key is required.', 'figma-to-elementor'));
        }
    
        $url = "https://api.figma.com/v1/files/{$file_key}";

        // Add node_id if provided
        if (!empty($node_id)) {
            $url .= '?node_ids=' . urlencode($node_id);
            $this->update_progress("Fetching Figma file: {$file_key} (Node: {$node_id})...");
        } else {
            $this->update_progress("Fetching entire Figma file: {$file_key}...");
        }
        
        error_log('Figma API Request URL: ' . $url); // Keep for debugging specific URL calls

        $response = wp_remote_get($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Figma-Token' => $this->api_key
            ],
            'timeout' => 300, // Timeout for the API request
            'sslverify' => true
        ]);
    
        return $response;
    }
    
    private function process_figma_response($response) {
        // 3. Message: Processing API response
        $this->update_progress("Processing Figma API response...");

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $this->update_progress("Error: Figma API request failed - " . $error_message);
            throw new \Exception($error_message);
        }
    
        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
    
        if ($status_code !== 200) {
            $error_message = $body['message'] ?? __('Unknown error occurred while fetching from Figma API.', 'figma-to-elementor');
            $this->update_progress("Error: Figma API returned status {$status_code} - " . $error_message);
            throw new \Exception($error_message);
        }

        // 4. Message: Extracting Figma data
        $this->update_progress("Extracting and structuring Figma data...");
        $processed_content = $this->process_figma_data($body);

        return [
            'content' => $processed_content,
            'usage' => [
                'file_key' => $body['file_key'] ?? '',
                'name' => $body['name'] ?? '',
                'last_modified' => $body['last_modified'] ?? '',
                'version' => $body['version'] ?? ''
            ]
        ];
    }
    
    private function process_figma_data(array $figma_data) {
        // Implement your logic to convert Figma data to your desired format
        // This could include extracting layers, converting to HTML/CSS, etc.
        
        $result = [
            'document' => $this->extract_document_structure($figma_data['document'] ?? []),
            'components' => $figma_data['components'] ?? [],
            'styles' => $figma_data['styles'] ?? []
        ];
        
        return $result;
    }
    
    private function extract_document_structure(array $node) {
        $structure = [
            'id' => $node['id'] ?? '',
            'name' => $node['name'] ?? '',
            'type' => $node['type'] ?? '',
            'visible' => $node['visible'] ?? true,
            'children' => []
        ];
        
        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $structure['children'][] = $this->extract_document_structure($child);
            }
        }
        
        // Add additional properties based on node type
        switch ($node['type']) {
            case 'FRAME':
            case 'GROUP':
                $structure['size'] = $node['size'] ?? [];
                $structure['position'] = $node['position'] ?? [];
                break;
            case 'RECTANGLE':
            case 'ELLIPSE':
                $structure['fills'] = $node['fills'] ?? [];
                $structure['strokes'] = $node['strokes'] ?? [];
                $structure['cornerRadius'] = $node['cornerRadius'] ?? 0;
                break;
            case 'TEXT':
                $structure['characters'] = $node['characters'] ?? '';
                $structure['style'] = $node['style'] ?? [];
                break;
        }
        
        return $structure;
    }

    /**
     * Updates the progress transient with a new message for Figma.
     * @param string $msg The message to display.
     */
    private function update_progress($msg) {
        
        $progress_data = [
            'source' => "Figma URL", // Keep this consistent with your frontend check
            'message' => $msg,
            'timestamp' => time()
        ];
        try {
            set_transient(
                'f2e_progress_' . get_current_user_id(),
                $progress_data,
                60 // Expire after 1 minutes (longer for potentially large Figma files)
            );
        } catch (\Exception $e) {
            error_log("Failed to set progress transient for Figma: " . $e->getMessage());
        }

        // Uncomment for debugging if needed, but be mindful of log file size
        // error_log('Figma Progress updated: ' . print_r($progress_data, true));
    }
    
}