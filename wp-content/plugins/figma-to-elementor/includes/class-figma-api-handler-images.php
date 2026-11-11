<?php
namespace FigmaToElementor;

defined('ABSPATH') || exit;

class FigmaAPI_Handler {
    private $api_key;
    private $default_url;
    private $image_cache_dir;
    private $image_cache_url;

    public function __construct() {
        $this->api_key = defined('FIGMA_API_KEY') ? FIGMA_API_KEY : '';
        $this->default_url = "https://www.figma.com/design/U7Ci92WexNR3aQv8xV6wvo/TEST-SAMPLE-HOMEPAGE?node-id=0-1&p=f";
        
        // Set up image cache directories
        $upload_dir = wp_upload_dir();
        $this->image_cache_dir = trailingslashit($upload_dir['basedir']) . 'figma-cache/';
        $this->image_cache_url = trailingslashit($upload_dir['baseurl']) . 'figma-cache/';
        
        // Create directory if it doesn't exist
        if (!file_exists($this->image_cache_dir)) {
            wp_mkdir_p($this->image_cache_dir);
        }
    }

    public function generate_content_without_agentic(array $options) {
        if (empty($this->api_key)) {
            throw new \Exception(__('Figma API key not configured.', 'figma-to-elementor'));
        }
    
        $response = $this->make_figma_api_request($options);
        $processed_data = $this->process_figma_response($response);
        
        // If images are requested, fetch them
        if (isset($options['include_images']) && $options['include_images']) {
            $image_data = $this->get_figma_images($options);
            $processed_data['images'] = $image_data;
        }
        error_log("processed_data". print_r($processed_data, true));
        
        return $processed_data;
    }
    
    private function make_figma_api_request(array $options) {
        // If URL is provided instead of file_key
        if (isset($options['url'])) {
            $url_components = parse_url($options['url']);
            parse_str($url_components['query'] ?? '', $query_params);
            
            // Extract file key from path
            $path_parts = explode('/', trim($url_components['path'], '/'));
            $file_key = $path_parts[1] ?? '';
            
            // Convert node-id from URL format (0-1) to API format (0:1)
            $node_id = str_replace('-', ':', $query_params['node-id'] ?? '');
        } else {
            $file_key = $options['file_key'] ?? '';
            $node_id = $options['node_ids'] ?? '';
        }
    
        if (empty($file_key)) {
            throw new \Exception(__('Figma file key is required.', 'figma-to-elementor'));
        }
    
        $url = "https://api.figma.com/v1/files/{$file_key}";

        // Add node_id if provided
        if (!empty($node_id)) {
            $url .= '?node_ids=' . urlencode($node_id);
        }
    
        $response = wp_remote_get($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Figma-Token' => $this->api_key
            ],
            'timeout' => 300,
            'sslverify' => true
        ]);
    
        return $response;
    }
    
    private function process_figma_response($response) {
        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }
    
        $status_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
    
        if ($status_code !== 200) {
            $error_message = $body['message'] ?? __('Unknown error occurred while fetching from Figma API.', 'figma-to-elementor');
            throw new \Exception($error_message);
        }
    
        // Process the Figma response into a format suitable for your application
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
            case 'IMAGE':
                $structure['image_hash'] = $node['imageHash'] ?? '';
                $structure['fills'] = $node['fills'] ?? [];
                break;
        }
        
        return $structure;
    }

    /**
     * Get images from Figma nodes
     */
    private function get_figma_images(array $options) {
        if (isset($options['url'])) {
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
            throw new \Exception(__('Figma file key is required.', 'figma-to-elementor'));
        }

        // First get the file data to identify image nodes
        $file_url = "https://api.figma.com/v1/files/{$file_key}?node_ids=" . urlencode($node_id);
        $response = wp_remote_get($file_url, [
            'headers' => [
                'X-Figma-Token' => $this->api_key
            ]
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $file_data = json_decode(wp_remote_retrieve_body($response), true);
        $image_ids = $this->extract_image_ids($file_data['document']);

        if (empty($image_ids)) {
            return [];
        }

        // Now get the image URLs
        $images_url = "https://api.figma.com/v1/images/{$file_key}?ids=" . urlencode(implode(',', $image_ids)) . "&format=png";
        $response = wp_remote_get($images_url, [
            'headers' => [
                'X-Figma-Token' => $this->api_key
            ]
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $images_data = json_decode(wp_remote_retrieve_body($response), true);
        $images = $images_data['images'] ?? [];

        // Download and cache the images
        $downloaded_images = [];
        foreach ($images as $node_id => $image_url) {
            $downloaded_images[$node_id] = $this->download_and_cache_image($image_url);
        }

        return $downloaded_images;
    }

    /**
     * Recursively extract image IDs from document structure
     */
    private function extract_image_ids(array $node) {
        $image_ids = [];

        if (isset($node['imageHash'])) {
            $image_ids[] = $node['id'];
        }

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $image_ids = array_merge($image_ids, $this->extract_image_ids($child));
            }
        }

        return $image_ids;
    }

    /**
     * Download and cache an image from Figma
     */
    private function download_and_cache_image($image_url) {
        if (empty($image_url)) {
            return false;
        }

        $filename = md5($image_url) . '.png';
        $filepath = $this->image_cache_dir . $filename;

        // Return cached version if exists
        if (file_exists($filepath)) {
            return [
                'url' => $this->image_cache_url . $filename,
                'path' => $filepath
            ];
        }

        // Download the image
        $response = wp_remote_get($image_url, [
            'timeout' => 300
        ]);

        if (is_wp_error($response)) {
            error_log('Failed to download image: ' . $response->get_error_message());
            return false;
        }

        $image_data = wp_remote_retrieve_body($response);
        if (empty($image_data)) {
            error_log('Empty image data received');
            return false;
        }

        // Save to cache
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once (ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }

        if (!$wp_filesystem->put_contents($filepath, $image_data, FS_CHMOD_FILE)) {
            error_log('Failed to save image to cache');
            return false;
        }

        return [
            'url' => $this->image_cache_url . $filename,
            'path' => $filepath
        ];
    }

    /**
     * Clear image cache
     */
    public function clear_image_cache() {
        $files = glob($this->image_cache_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
}