<?php
/**
 * Plugin Name: Paddle Custom Integration
 * Plugin URI:  https://example.com/paddle-custom-integration
 * Description: A custom WordPress plugin to integrate with Paddle (Paddle.js and Webhooks) without third-party plugins.
 * Version:     1.0.0
 * Author:      ShahiSoft LLC
 * Author URI:  https://example.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: paddle-custom-integration
 * Domain Path: /languages
 */

// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Main class for Paddle Custom Integration.
 */
class My_Paddle_Integration {

    /**
     * Constructor to initialize hooks.
     */
    public function __construct() {
        // Admin menu and settings.
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );

        // Enqueue Paddle.js on frontend.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_paddle_js' ) );

        // Register the shortcode for checkout buttons.
        add_shortcode( 'paddle_checkout', array( $this, 'paddle_checkout_shortcode' ) );

        // New shortcode for thank you page
        add_shortcode( 'paddle_order_summary', array( $this, 'paddle_order_summary_shortcode' ) );

        // Register custom REST API endpoint for webhooks.
        add_action( 'rest_api_init', array( $this, 'register_webhook_endpoint' ) );
        
        
    }

    /**
     * Adds the admin menu page for Paddle settings.
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'Paddle Integration Settings', 'paddle-custom-integration' ), // Page title.
            __( 'Paddle Integration', 'paddle-custom-integration' ),          // Menu title.
            'manage_options',                                                // Capability.
            'paddle-integration',                                            // Menu slug.
            array( $this, 'settings_page_html' )                             // Callback function.
        );
    }

    /**
     * Registers settings and their fields.
     */
    public function settings_init() {
        // Register a new setting for Paddle credentials.
        register_setting( 'paddle_integration_settings_group', 'paddle_client_side_token', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ) );
        register_setting( 'paddle_integration_settings_group', 'paddle_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ) );
        register_setting( 'paddle_integration_settings_group', 'paddle_webhook_secret', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ) );
        register_setting( 'paddle_integration_settings_group', 'paddle_environment', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'sandbox', // Default to sandbox.
        ) );

        // New setting for Thank You Page URL
        register_setting( 'paddle_integration_settings_group', 'paddle_thank_you_page_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ) );


        // Add a settings section.
        add_settings_section(
            'paddle_integration_section',
            __( 'Paddle API Credentials', 'paddle-custom-integration' ),
            array( $this, 'settings_section_callback' ),
            'paddle-integration' // Page slug.
        );

        // Add fields to the section.
        add_settings_field(
            'paddle_client_side_token_field',
            __( 'Client-Side Token', 'paddle-custom-integration' ),
            array( $this, 'client_side_token_callback' ),
            'paddle-integration',
            'paddle_integration_section'
        );
        add_settings_field(
            'paddle_api_key_field',
            __( 'API Key (Server-Side)', 'paddle-custom-integration' ),
            array( $this, 'api_key_callback' ),
            'paddle-integration',
            'paddle_integration_section'
        );
        add_settings_field(
            'paddle_webhook_secret_field',
            __( 'Webhook Secret', 'paddle-custom-integration' ),
            array( $this, 'webhook_secret_callback' ),
            'paddle-integration',
            'paddle_integration_section'
        );
        add_settings_field(
            'paddle_environment_field',
            __( 'Environment', 'paddle-custom-integration' ),
            array( $this, 'environment_callback' ),
            'paddle-integration',
            'paddle_integration_section'
        );
        add_settings_field(
            'paddle_webhook_url_info',
            __( 'Your Webhook URL', 'paddle-custom-integration' ),
            array( $this, 'webhook_url_info_callback' ),
            'paddle-integration',
            'paddle_integration_section'
        );
        add_settings_field(
            'paddle_thank_you_page_url_field',
            __( 'Thank You Page URL', 'paddle-custom-integration' ),
            array( $this, 'thank_you_page_url_callback' ),
            'paddle-integration',
            'paddle_integration_section'
        );
    }

    /**
     * Callback for the settings section description.
     */
    public function settings_section_callback() {
        echo '<p>' . esc_html__( 'Enter your Paddle API credentials. You can find these in your Paddle Dashboard under Developer Tools.', 'paddle-custom-integration' ) . '</p>';
    }

    /**
     * Callback for the Client-Side Token field.
     */
    public function client_side_token_callback() {
        $token = get_option( 'paddle_client_side_token' );
        echo '<input type="text" id="paddle_client_side_token_field" name="paddle_client_side_token" value="' . esc_attr( $token ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Required for Paddle.js to open checkouts.', 'paddle-custom-integration' ) . '</p>';
    }

    /**
     * Callback for the API Key field.
     */
    public function api_key_callback() {
        $key = get_option( 'paddle_api_key' );
        echo '<input type="password" id="paddle_api_key_field" name="paddle_api_key" value="' . esc_attr( $key ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Your server-side API key for interacting with Paddle directly (e.g., future API calls). Keep this secure!', 'paddle-custom-integration' ) . '</p>';
    }

    /**
     * Callback for the Webhook Secret field.
     */
    public function webhook_secret_callback() {
        $secret = get_option( 'paddle_webhook_secret' );
        echo '<input type="password" id="paddle_webhook_secret_field" name="paddle_webhook_secret" value="' . esc_attr( $secret ) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__( 'Used to verify incoming webhooks from Paddle. Find this in Paddle Dashboard > Developer Tools > Notifications.', 'paddle-custom-integration' ) . '</p>';
    }

    /**
     * Callback for the Environment field.
     */
    public function environment_callback() {
        $env = get_option( 'paddle_environment' );
        ?>
        <select id="paddle_environment_field" name="paddle_environment">
            <option value="sandbox" <?php selected( $env, 'sandbox' ); ?>>Sandbox</option>
            <option value="live" <?php selected( $env, 'live' ); ?>>Live</option>
        </select>
        <p class="description"><?php esc_html_e( 'Choose Sandbox for testing, Live for real transactions.', 'paddle-custom-integration' ); ?></p>
        <?php
    }

    /**
     * Callback to display the Webhook URL.
     */
    public function webhook_url_info_callback() {
        $webhook_url = get_rest_url( null, 'my-paddle-integration/v1/webhook' );
        echo '<input type="text" class="regular-text" value="' . esc_url( $webhook_url ) . '" readonly />';
        echo '<p class="description">' . sprintf(
            esc_html__( 'Copy this URL and paste it into your Paddle Dashboard under %1$sDeveloper Tools > Notifications%2$s as a Notification Destination.', 'paddle-custom-integration' ),
            '<strong>',
            '</strong>'
        ) . '</p>';
    }

    /**
     * Callback for the Thank You Page URL field.
     */
    public function thank_you_page_url_callback() {
        $url = get_option( 'paddle_thank_you_page_url' );
        echo '<input type="url" id="paddle_thank_you_page_url_field" name="paddle_thank_you_page_url" value="' . esc_url( $url ) . '" class="regular-text" placeholder="https://yourdomain.com/thank-you/" />';
        echo '<p class="description">' . esc_html__( 'Enter the full URL of your thank you page. Users will be redirected here after a successful Paddle checkout.', 'paddle-custom-integration' ) . '</p>';
    }

    /**
     * Renders the Paddle Integration settings page.
     */
    public function settings_page_html() {
        // Check user capabilities.
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                // Output security fields for the registered setting.
                settings_fields( 'paddle_integration_settings_group' );
                // Output setting sections and their fields.
                do_settings_sections( 'paddle-integration' );
                // Output save settings button.
                submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Enqueues Paddle.js and initializes it with provided client-side token.
     */
    public function enqueue_paddle_js() {
        $client_token = get_option('paddle_client_side_token');
        $environment = get_option('paddle_environment', 'sandbox');
        $thank_you_page_url = get_option('paddle_thank_you_page_url');

        if (empty($client_token)) {
            error_log('Paddle Integration: Client-Side Token is not set. Paddle.js will not be loaded.');
            return;
        }

        // Enqueue Paddle.js with proper dependency management
        wp_enqueue_script(
            'paddle-js',
            'https://cdn.paddle.com/paddle/v2/paddle.js',
            array(),
            null,
            true
        );

        // Localize script with settings
        wp_localize_script(
            'paddle-js',
            'myPaddleIntegration',
            array(
                'clientToken' => $client_token,
                'environment' => $environment,
                'thankYouPageUrl' => esc_url($thank_you_page_url),
            )
        );

        // Add initialization script
        wp_add_inline_script(
            'paddle-js',
            "
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Paddle === 'undefined') {
                    console.error('Paddle.js failed to load');
                    return;
                }
                
                // First set the environment
                Paddle.Environment.set(myPaddleIntegration.environment === 'live' ? 'production' : 'sandbox');
                
                // Then initialize
                Paddle.Initialize({
                    token: myPaddleIntegration.clientToken,
                    eventCallback: function(data) {
                        if (data.name === 'checkout.completed') {
                            console.log('Paddle checkout completed:', data);
                            
                            const transactionId = data.data.transaction_id;
                            const thankYouUrl = myPaddleIntegration.thankYouPageUrl;
                            
                            if (transactionId && thankYouUrl) {
                                const separator = thankYouUrl.includes('?') ? '&' : '?';
                                window.location.href = thankYouUrl + separator + 'paddle_transaction_id=' + transactionId;
                            }
                        }
                    }
                });
            });
            ",
            'after'
        );
    }

    /**
     * Shortcode to generate a Paddle checkout button.
     * Example: [paddle_checkout price_id="pri_01gsz8x8sawmvhz1pv30nge1ke" text="Buy My Product" quantity="1" role_on_purchase="premium_member"]
     * 'role_on_purchase' is a custom attribute for this plugin to map Paddle products to WP roles.
     * You can pass 'custom_data_wp_user_id="true"' to automatically send the current logged-in user ID.
     */
    public function paddle_checkout_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'price_id'               => '',
                'text'                   => __('Buy Now', 'paddle-custom-integration'),
                'quantity'               => 1,
                'class'                  => 'paddle-checkout-button mt-auto w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-primary-600 to-accent-600 hover:from-primary-700 hover:to-accent-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors',
                'role_on_purchase'       => '',
                'custom_data_wp_user_id' => 'true',
            ),
            $atts,
            'paddle_checkout'
        );
    
        if (empty($atts['price_id'])) {
            return '<p style="color: red;">' . esc_html__('Error: Missing price_id for Paddle checkout button.', 'paddle-custom-integration') . '</p>';
        }
    
        $is_user_logged_in = is_user_logged_in();
        $custom_data = [];
    
        if ($atts['custom_data_wp_user_id'] === 'true' && $is_user_logged_in) {
            $current_user = wp_get_current_user();
            $custom_data['wp_user_id'] = $current_user->ID;
        }
    
        if (!empty($atts['role_on_purchase'])) {
            $custom_data['role_on_purchase'] = $atts['role_on_purchase'];
        }
    
        $custom_data_json = !empty($custom_data) ? json_encode($custom_data) : '{}';
    
        // Generate a unique ID for this specific button
        $unique_id = 'paddle-checkout-' . esc_attr($atts['price_id']) . '-' . wp_rand(100, 999);
    
        ob_start();
        ?>
        
        <button type="button"
                id="<?php echo $unique_id; ?>"
                class="<?php echo esc_attr($atts['class']); ?>"
                data-paddle-price-id="<?php echo esc_attr($atts['price_id']); ?>"
                data-paddle-quantity="<?php echo esc_attr($atts['quantity']); ?>"
                data-paddle-custom-data='<?php echo esc_attr($custom_data_json); ?>'
        >
            <?php echo esc_html($atts['text']); ?>
        </button>
        <small class="paddle-login-message" style="display: none; color: #c00; margin-top: 8px;"></small>
    
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const button = document.getElementById('<?php echo esc_js($unique_id); ?>');
                
                if (button) {
                    button.addEventListener('click', function(event) {
                        
                        const userIsLoggedIn = <?php echo $is_user_logged_in ? 'true' : 'false'; ?>;
    
                        if (!userIsLoggedIn) {
                            
                            event.preventDefault();
                            event.stopPropagation();
                            
                            const messageElement = this.parentElement.querySelector('.paddle-login-message');
                            
                            if (messageElement) {
                                messageElement.innerText = 'Please login to purchase this item.';
                                messageElement.style.display = 'block';
                            }
                            
                            return; 
                        }
    
                        if (typeof Paddle === 'undefined') {
                            console.error('Paddle.js is not loaded.');
                            return;
                        }
    
                        const priceId = this.getAttribute('data-paddle-price-id');
                        const quantity = parseInt(this.getAttribute('data-paddle-quantity'));
                        const customData = JSON.parse(this.getAttribute('data-paddle-custom-data'));
    
                        Paddle.Checkout.open({
                            items: [{
                                priceId: priceId,
                                quantity: quantity
                            }],
                            customData: customData
                        });
                    });
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Registers the custom REST API endpoint for Paddle webhooks.
     */
    public function register_webhook_endpoint() {
        register_rest_route(
            'my-paddle-integration/v1', // Namespace.
            '/webhook',                  // Route.
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_paddle_webhook' ),
                'permission_callback' => '__return_true', // Permissions handled by signature verification.
                'args'                => array(),
            )
        );
    }


    /**
     * Handles incoming Paddle webhooks.
     * This is the main server-side entry point for Paddle notifications.
     */
    public function handle_paddle_webhook( \WP_REST_Request $request ) { // Ensure WP_REST_Request is namespaced
        // Get the raw request body.
        $body = $request->get_body();
        // Get the Paddle-Signature header.
        $signature = $request->get_header( 'paddle-signature' );
        // Retrieve the webhook secret from options.
        $webhook_secret = get_option( 'paddle_webhook_secret' );

        // --- START DEBUG LOGGING ---
        // error_log('--- Paddle Webhook Debug Start ---');
        // error_log('Raw Request Body Length: ' . strlen($body));
        // Be cautious about logging full body in production if it contains sensitive info
        // error_log('Raw Request Body: ' . $body);
        // error_log('Received Paddle-Signature Header: ' . ($signature ? $signature : 'NOT SET'));
        // error_log('Configured Webhook Secret (last 8 chars): ' . (substr($webhook_secret, -8))); // Log partial secret for security
        // error_log('Configured Webhook Secret Length: ' . strlen($webhook_secret));
        // --- END DEBUG LOGGING ---

        // Basic validation.
        if ( empty( $body ) || empty( $signature ) || empty( $webhook_secret ) ) {
            error_log( 'Paddle Webhook Error: Missing body, signature, or webhook secret.' );
            error_log('--- Paddle Webhook Debug End (Invalid Request) ---');
            return new \WP_REST_Response( 'Invalid request.', 400 );
        }

        // Verify the webhook signature. CRITICAL SECURITY STEP.
        if ( ! $this->verify_webhook_signature( $body, $signature, $webhook_secret ) ) {
            error_log( 'Paddle Webhook Error: Invalid signature detected.' );
            error_log('--- Paddle Webhook Debug End (Invalid Signature) ---');
            return new \WP_REST_Response( 'Invalid signature.', 401 );
        }

        // Parse the JSON payload.
        $payload = json_decode( $body, true );

        // Validate payload structure.
        if ( ! $payload || ! isset( $payload['event_type'] ) || ! isset( $payload['data'] ) || ! isset( $payload['event_id'] ) ) {
            error_log( 'Paddle Webhook Error: Invalid webhook payload structure.' );
            error_log('--- Paddle Webhook Debug End (Invalid Payload) ---');
            return new \WP_REST_Response( 'Invalid payload.', 400 );
        }

        $event_type = $payload['event_type'];
        $event_id   = $payload['event_id'];

        // Idempotency check: Prevent processing the same event multiple times.
        // Using transients is a simple way; for high-volume or critical systems, consider a custom DB table.
        $transient_key = 'paddle_webhook_processed_' . sanitize_key( $event_id );
        if ( get_transient( $transient_key ) ) {
            error_log( 'Paddle Webhook Info: Event ' . $event_id . ' already processed. Skipping.' );
            error_log('--- Paddle Webhook Debug End (Already Processed) ---');
            return new \WP_REST_Response( 'Event already processed.', 200 );
        }

        // Store the event_id as processed to prevent re-processing. Expires in 1 hour.
        set_transient( $transient_key, true, HOUR_IN_SECONDS );

        // error_log( 'Paddle Webhook Received: Event Type - ' . $event_type . ', Event ID - ' . $event_id );
        // error_log( 'Full Payload: ' . print_r( $payload, true ) ); // Uncomment for full payload logging (use with caution in production).

        // Handle different event types.
        switch ( $event_type ) {
            case 'transaction.completed':
                $this->handle_transaction_completed( $payload );
                break;
            case 'subscription.created':
                // $this->handle_subscription_created( $payload );
                break;
            case 'subscription.updated':
                // $this->handle_subscription_updated( $payload );
                break;
            case 'subscription.canceled':
                // $this->handle_subscription_canceled( $payload );
                break;
            // Add more cases for other event types you want to handle.
            default:
                error_log( 'Paddle Webhook Info: Unhandled event type - ' . $event_type );
                break;
        }

        // error_log('--- Paddle Webhook Debug End (Processed Successfully) ---');
        return new \WP_REST_Response( 'Webhook processed.', 200 );
    }

    /**
     * Verifies the webhook signature.
     *
     * @param string $payload   The raw webhook payload (request body).
     * @param string $signature The value of the Paddle-Signature header.
     * @param string $secret    Your Paddle webhook secret.
     * @return bool True if the signature is valid, false otherwise.
     */
    private function verify_webhook_signature( $payload, $signature, $secret ) {
        $parts = explode( ';', $signature );
        $timestamp = null;
        $h1 = null;

        foreach ( $parts as $part ) {
            // FIX: Changed 't=' to 'ts=' and adjusted substr offset.
            if ( strpos( $part, 'ts=' ) === 0 ) {
                $timestamp = substr( $part, 3 ); // 'ts=' is 3 characters long.
            } elseif ( strpos( $part, 'h1=' ) === 0 ) {
                $h1 = substr( $part, 3 ); // 'h1=' is 3 characters long.
            }
        }

        // --- START SIGNATURE DEBUG LOGGING ---
        // error_log('Signature Verification: Timestamp found: ' . ($timestamp ? $timestamp : 'N/A'));
        // error_log('Signature Verification: h1 (provided) found: ' . ($h1 ? $h1 : 'N/A'));
        // error_log('Signature Verification: Raw Payload for signing (first 200 chars): ' . substr($payload, 0, 200) . '...'); // Log start of payload
        // error_log('Signature Verification: Secret used (last 8 chars): ' . substr($secret, -8));
        // --- END SIGNATURE DEBUG LOGGING ---


        if ( is_null( $timestamp ) || is_null( $h1 ) ) {
            error_log('Signature Verification Failed: Missing timestamp or h1 in signature parts after parsing.');
            return false;
        }

        // Prepare the signed payload string.
        $signed_payload = $timestamp . ':' . $payload;

        // Calculate the expected signature.
        $expected_h1 = hash_hmac( 'sha256', $signed_payload, $secret );

        // error_log('Signature Verification: Expected h1: ' . $expected_h1);
        // error_log('Signature Verification: Hash_equals comparison result: ' . (hash_equals($expected_h1, $h1) ? 'TRUE' : 'FALSE'));


        // Compare using hash_equals to prevent timing attacks.
        return hash_equals( $expected_h1, $h1 );
    }
    /**
     * Handles the 'transaction.completed' webhook event.
     * Grants access/roles for one-time purchases or initial subscription payments.
     * Also grants myCRED points based on the purchased product.
     *
     * @param array $payload The webhook payload data.
     */
    private function handle_transaction_completed($payload) {
        $transaction_id = $payload['data']['id'];
        $customer_email = $payload['data']['customer']['email'] ?? null;
        $customer_id = $payload['data']['customer_id'] ?? null;
        $custom_data = $payload['data']['custom_data'] ?? [];
        $items = $payload['data']['items'] ?? [];

        // Define points mapping with package names    
        // sandbox product pricing
        // $points_mapping = [
        //     'pri_01jy4d7qj409nbxx2x6yadweq4' => [
        //         'points' => 1500,
        //         'name' => 'Starter Pack',
        //         'price' => 12
        //     ],
        //     'pri_01jy9812qy0sxvh531aeenepbr' => [
        //         'points' => 4000,
        //         'name' => 'Pro Pack',
        //         'price' => 25
        //     ],
        //     'pri_01jy9853stdmyvjbj7jg0fxkx4' => [
        //         'points' => 16000,
        //         'name' => 'Power Pack',
        //         'price' => 55
        //     ],
        // ];
     
        // live product pricing      
        $points_mapping = [
            'pri_01kdb86rkbyjjn1v2rg01eyken' => [
                'points' => 500,
                'name' => 'Starter Pack',
                'price' => 1.50
            ],
            'pri_01kdb8g0pw8nkp2kh2q4z5m5h3' => [
                'points' => 5000,
                'name' => 'Pro Pack',
                'price' => 12.50
            ],
            'pri_01kdb8pdx3dzb9bfakrb3xxq7r' => [
                'points' => 15000,
                'name' => 'Power Pack',
                'price' => 18.50
            ],
        ];

        // Debug the incoming data
        // error_log('Transaction data: ' . print_r([
        //     'transaction_id' => $transaction_id,
        //     'customer_email' => $customer_email,
        //     'customer_id' => $customer_id,
        //     'custom_data' => $custom_data,
        //     'items_count' => count($items)
        // ], true));
    
        // Try to find user by different methods
        $user = null;
        
        // 1. First try by custom_data wp_user_id if available
        if (isset($custom_data['wp_user_id'])) {
            $user = get_user_by('id', absint($custom_data['wp_user_id']));
            if ($user) {
                error_log("Found user by custom_data wp_user_id: {$user->ID}");
            }
        }
        
        // 2. Then try by email if available
        if (!$user && !empty($customer_email)) {
            $user = get_user_by('email', $customer_email);
            if ($user) {
                error_log("Found user by email: $customer_email");
            }
        }
        
        // 3. Then try by Paddle customer_id
        if (!$user && !empty($customer_id)) {
            $users = get_users([
                'meta_key' => '_paddle_customer_id',
                'meta_value' => $customer_id,
                'number' => 1
            ]);
            if (!empty($users)) {
                $user = $users[0];
                error_log("Found user by customer_id: $customer_id");
            }
        }
    
        // If we still don't have a user, try to create one if we have an email
        if (!$user && !empty($customer_email)) {
            $username = sanitize_user(str_replace(['@', '.'], '_', $customer_email));
            if (username_exists($username)) {
                $username .= '_' . wp_rand(100, 999);
            }
            
            $user_id = wp_create_user($username, wp_generate_password(12), $customer_email);
            if (!is_wp_error($user_id)) {
                $user = get_user_by('id', $user_id);
                error_log("Created new user for email: $customer_email");
            }
        }
    
        // Final fallback - check if we can get email from custom_data
        // if (!$user && isset($custom_data['wp_user_email'])) {
        //     $user = get_user_by('email', $custom_data['wp_user_email']);
        //     if ($user) {
        //         error_log("Found user by custom_data email: {$custom_data['wp_user_email']}");
        //     }
        // }
    
        // If we still don't have a user, log and exit
        if (!$user) {
            error_log("Could not find or create user for transaction. Available data: " . print_r([
                'transaction_id' => $transaction_id,
                'customer_email' => $customer_email,
                'customer_id' => $customer_id,
                'custom_data' => $custom_data,
                'items' => $items
            ], true));
            return;
        }
    
        // Store customer ID if we have it
        if (!empty($customer_id)) {
            update_user_meta($user->ID, '_paddle_customer_id', $customer_id);
        }

        update_user_meta($user->ID, '_paddle_last_transaction_id', $transaction_id);
    
        // Process the transaction (points, roles, etc.)
        $this->process_transaction_items($user, $items, $transaction_id, $custom_data, $points_mapping);

        // --- WooCommerce Integration ---
        $this->create_woocommerce_order( $user, $items, $custom_data, $transaction_id, $points_mapping);

    }

    private function process_transaction_items($user, $items, $transaction_id, $custom_data, $points_mapping) {
        // Load MyCred handler if available
        $handler_path = plugin_dir_path(__FILE__) . 'class-mycred-handler.php';
        if (file_exists($handler_path)) {
            require_once $handler_path;
        }
    
        foreach ($items as $item) {
            $price_id = $item['price_id'] ?? '';
            $quantity = $item['quantity'] ?? 1;
            
            // Grant points if mapping exists
            if (isset($points_mapping[$price_id]) && class_exists('PaddleToMycred\Mycred_Handler')) {
                $package = $points_mapping[$price_id];
                $points = $package['points'];   
                
                $mycred_handler = new PaddleToMycred\Mycred_Handler();
                $result = $mycred_handler->grant_points_on_paddle_purchase(
                    $user->ID,
                    $points,
                    $package['name'], // Use the package name from our mapping
                    [
                        'id' => $transaction_id,
                        'price_id' => $price_id,
                        'quantity' => $quantity,
                        'package_name' => $package['name'] // Include package name in details
                    ]
                );
                
                error_log("Points grant for {$package['name']} (ID: $price_id): " . ($result ? 'Success' : 'Failed'));
                
                // Update mycred_rank with package name
                if ($result && function_exists('mycred_update_users_rank')) {
                    mycred_update_users_rank($user->ID);
                    error_log("Updated mycred_rank for user {$user->ID} with package {$package['name']}");
                }
            }
    
            // Handle role assignment if specified
            if (!empty($custom_data['role_on_purchase'])) {
                $user->add_role(sanitize_key($custom_data['role_on_purchase']));
                error_log("Assigned role: {$custom_data['role_on_purchase']}");
            }
        }

    }

    /**
     * Handles the 'subscription.created' webhook event.
     *
     * @param array $payload The webhook payload data.
     */
    private function handle_subscription_created( $payload ) {
        $subscription_id = $payload['data']['id'];
        $customer_email  = $payload['data']['customer']['email'] ?? null;
        $status          = $payload['data']['status'] ?? ''; // e.g., 'active', 'trialing'.
        $price_id        = $payload['data']['items'][0]['price_id'] ?? null; // Assuming one item per subscription for simplicity.
        $user = get_user_by( 'email', $customer_email );

        if ( ! $user ) {
            error_log( "Paddle Webhook Error: Subscription created for unknown email $customer_email. Manual intervention may be needed." );
            return;
        }

        // Store subscription details.
        update_user_meta( $user->ID, '_paddle_subscription_id', $subscription_id );
        update_user_meta( $user->ID, '_paddle_subscription_status', $status );
        update_user_meta( $user->ID, '_paddle_subscription_price_id', $price_id ); // Store the price ID of the subscription.

        // Grant access based on subscription status.
        if ( in_array( $status, array( 'active', 'trialing' ) ) ) {
            // Map Paddle Price ID to a WordPress role for subscription access.
            if ( $price_id === 'pri_YOUR_MONTHLY_SUB_PRICE_ID' ) { // Replace with actual Price ID
                $user->add_role( 'monthly_subscriber' );
                error_log( 'Paddle Webhook: Granted "monthly_subscriber" role to user ' . $user->ID . '.' );
            } elseif ( $price_id === 'pri_YOUR_ANNUAL_SUB_PRICE_ID' ) { // Replace with actual Price ID
                $user->add_role( 'annual_subscriber' );
                error_log( 'Paddle Webhook: Granted "annual_subscriber" role to user ' . $user->ID . '.' );
            }
        }

        error_log( "Paddle Webhook: Subscription $subscription_id created for user {$user->ID} with status $status." );
    }

    /**
     * Handles the 'subscription.updated' webhook event.
     * Adjusts user roles/meta based on subscription status changes (e.g., failed payment, plan change).
     *
     * @param array $payload The webhook payload data.
     */
    private function handle_subscription_updated( $payload ) {
        $subscription_id = $payload['data']['id'];
        $customer_email  = $payload['data']['customer']['email'] ?? null;
        $status          = $payload['data']['status'] ?? ''; // e.g., 'active', 'paused', 'canceled', 'past_due'.
        $old_status      = get_user_meta( $user->ID, '_paddle_subscription_status', true ); // Get previous status.
        $price_id        = $payload['data']['items'][0]['price_id'] ?? null;

        $user = get_user_by( 'email', $customer_email );

        if ( ! $user ) {
            error_log( "Paddle Webhook Error: Subscription updated for unknown email $customer_email. Manual intervention may be needed." );
            return;
        }

        // Update stored subscription status.
        update_user_meta( $user->ID, '_paddle_subscription_status', $status );
        update_user_meta( $user->ID, '_paddle_subscription_price_id', $price_id );

        // Handle status changes.
        if ( in_array( $status, array( 'active', 'trialing' ) ) ) {
            // Re-grant roles if subscription becomes active again (e.g., from past_due).
            if ( $price_id === 'pri_YOUR_MONTHLY_SUB_PRICE_ID' ) {
                $user->add_role( 'monthly_subscriber' );
            } elseif ( $price_id === 'pri_YOUR_ANNUAL_SUB_PRICE_ID' ) {
                $user->add_role( 'annual_subscriber' );
            }
        } else {
            // Remove roles if subscription is no longer active.
            $user->remove_role( 'monthly_subscriber' );
            $user->remove_role( 'annual_subscriber' );
            // You might remove other specific access roles here too.
        }

        error_log( "Paddle Webhook: Subscription $subscription_id for user {$user->ID} updated to status $status." );
    }

    /**
     * Handles the 'subscription.canceled' webhook event.
     * Revokes access/roles for canceled subscriptions.
     *
     * @param array $payload The webhook payload data.
     */
    private function handle_subscription_canceled( $payload ) {
        $subscription_id = $payload['data']['id'];
        $customer_email  = $payload['data']['customer']['email'] ?? null;
        $status          = $payload['data']['status'] ?? ''; // Should be 'canceled'.

        $user = get_user_by( 'email', $customer_email );

        if ( ! $user ) {
            error_log( "Paddle Webhook Error: Subscription canceled for unknown email $customer_email. Manual intervention may be needed." );
            return;
        }

        // Update stored subscription status.
        update_user_meta( $user->ID, '_paddle_subscription_status', $status );

        // Remove subscription-related roles.
        $user->remove_role( 'monthly_subscriber' );
        $user->remove_role( 'annual_subscriber' );
        // Also remove any general 'subscriber' or 'premium_access' roles if this was the only way they had it.
        $user->remove_role( 'subscriber' ); // Example: if 'subscriber' is a role you assign.
        $user->remove_role( 'premium_access' ); // Example: if 'premium_access' is a role you assign.


        error_log( "Paddle Webhook: Subscription $subscription_id canceled for user {$user->ID}. Access revoked." );
    }


    /**
     * Creates a WooCommerce order programmatically after a successful Paddle transaction.
     *
     * This function creates a virtual, non-shippable order containing "Points Packages"
     * as line items, based on the data received from Paddle.
     *
     * @param WP_User $user The WordPress user object for whom the order is created.
     * @param array   $items The transaction items array from the Paddle payload.
     * @param array   $custom_data Custom data that was sent with the transaction.
     * @param string  $transaction_id The unique transaction ID from Paddle.
     * @param array   $points_mapping An associative array mapping Paddle Price IDs to package details (name, points, price).
     * @return int|false The new WooCommerce order ID on success, or false on failure.
     */
    public function create_woocommerce_order($user, $items, $custom_data, $transaction_id, $points_mapping) {
        error_log("Starting create_woocommerce_order for Paddle transaction: {$transaction_id}");
    
        if (!class_exists('WooCommerce')) {
            error_log("WooCommerce is not active. Cannot create order.");
            return false;
        }
    
        // Ensure the user object is valid.
        if (!($user instanceof WP_User) || !$user->exists()) {
            error_log("Invalid WP_User object provided.");
            return false;
        }
    
        try {
            // --- 1. Create the Order ---
            // We start by creating a new order and associating it with the customer.
            $order = wc_create_order([
                'customer_id' => $user->ID,
                'created_via' => 'paddle-integration' // Useful for identifying how the order was created.
            ]);
    
            if (is_wp_error($order)) {
                error_log("Error creating WC order: " . $order->get_error_message());
                return false;
            }
    
            $total_points = 0;
    
            // --- 2. Process and Add Line Items ---
            foreach ($items as $item) {
                // Map the Paddle Price ID to your internal package structure.
                $price_id = $item['price_id'] ?? '';
                $package = $points_mapping[$price_id] ?? null;
    
                if (!$package) {
                    error_log("Warning: Could not find points package mapping for Paddle Price ID: {$price_id}. Skipping item.");
                    continue;
                }
    
                $points = $package['points'] ?? 0;
                $unit_price = $package['price'] ?? 0;
                $package_name = $package['name'] ?? 'Points Package';
    
                $total_points += $points;
    
                // To add a product that doesn't exist in the catalog, we create a temporary
                // product object and set its properties directly on the order item.
                $product = new WC_Product();
                $product->set_name("$package_name ($points points)");
                $product->set_regular_price($unit_price);
                $product->set_price($unit_price);
                $product->set_virtual(true); // Mark as a virtual, non-shippable product.
                $product->set_tax_status('none'); // No tax.
    
                // Add the product to the order.
                $item_id = $order->add_product($product, 1); // Quantity is 1.
    
                // Add custom meta to the line item for future reference.
                if ($item_id) {
                    wc_add_order_item_meta($item_id, '_paddle_price_id', $price_id);
                }
            }
            
            // If no valid items were added, we shouldn't proceed.
            if (count($order->get_items()) === 0) {
                error_log("No valid items were added to the order for transaction {$transaction_id}. Aborting.");
                // Optional: Delete the empty order draft.
                // $order->delete(true); 
                return false;
            }
    
    
            // --- 3. Set Addresses and Customer Details ---
            $address = [
                'first_name' => $user->first_name ?: $user->display_name,
                'last_name'  => $user->last_name,
                'email'      => $user->user_email,
            ];
            $order->set_address($address, 'billing');
    
            // --- 4. Set Payment and Order Details ---
            $order->set_payment_method('paddle');
            $order->set_payment_method_title('Paddle Gateway');
            $order->set_currency(get_woocommerce_currency()); // Use the store's default currency.
    
            // --- 5. Calculate Totals and Finalize ---
            // This calculates totals based on the items added.
            $order->calculate_totals();
    
            // Add the Paddle transaction ID and total points granted as order meta.
            // This is crucial for the thank-you page to find the order.
            $order->update_meta_data('_paddle_transaction_id', $transaction_id);
            $order->update_meta_data('_paddle_total_points_granted', $total_points);
    
            // Add a descriptive note for administrators.
            $order->add_order_note(
                sprintf(
                    'Order created via Paddle API. Transaction ID: %s. Awarded %s points.',
                    $transaction_id,
                    number_format($total_points)
                )
            );
    
            // Set the final order status to 'completed' as payment was confirmed by the webhook.
            $order->update_status('completed', 'Payment received via Paddle.');
    
            // The order->save() is implicitly called by many of the methods above (like update_status),
            // but an explicit save is a good final step.
            $order_id = $order->save();
    
            error_log("Successfully created and finalized WooCommerce order #{$order_id} for Paddle transaction {$transaction_id}.");
    
            return $order_id;
    
        } catch (Exception $e) {
            error_log("An exception occurred in create_woocommerce_order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Shortcode to display Paddle transaction summary and myCred package upgrade status.
     *
     * Usage: [paddle_order_summary]
     * This shortcode expects `paddle_transaction_id` in the URL query string.
     * It finds the corresponding order to identify the user and display their new rank and balance.
     */
    public function paddle_order_summary_shortcode($atts) {
        // 1. Basic Prerequisite Checks
        if (!class_exists('WooCommerce') || !function_exists('mycred')) {
            return '<p>' . __('Required plugins (WooCommerce, myCred) are not active.', 'paddle-custom-integration') . '</p>';
        }

        // 2. Get Transaction ID from URL
        $transaction_id = isset($_GET['paddle_transaction_id']) ? sanitize_text_field($_GET['paddle_transaction_id']) : '';
        if (empty($transaction_id)) {
            return '<p>' . __('No transaction specified.', 'paddle-custom-integration') . '</p>';
        }

        // 4. Load Handler and Fetch myCred Data
        $handler_path = plugin_dir_path(__FILE__) . 'class-mycred-handler.php';
        if (!file_exists($handler_path)) {
            return '<p>' . __('Error: MyCred handler file is missing.', 'paddle-custom-integration') . '</p>';
        }
        require_once $handler_path;

        // CORRECTED: Pass the user_id to the handler's constructor.
        $mycred_handler = new PaddleToMycred\Mycred_Handler();
        $mycred_data = $mycred_handler->mycred_balance_with_rank();

        if (!$mycred_data) {
            return '<p>' . __('Could not retrieve points information.', 'paddle-custom-integration') . '</p>';
        }

        $balance = $mycred_data['balance'];
        $rank_name = $mycred_data['rank_name'];

        // 5. Build the HTML Output
        ob_start();

        ?>
        <div class="paddle-upgrade-success">
            <!-- Header with gradient text to match logo -->
            <h2 class="paddle-success-header">
                <?php _e('Thank you for your purchase!', 'paddle-custom-integration'); ?>
            </h2>
            
            <!-- Success icon -->
            <div class="paddle-success-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.86" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 4L12 14.01L9 11.01" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <h3 class="paddle-success-subheader">
                <?php _e('Your Package Has Been Upgraded!', 'paddle-custom-integration'); ?>
            </h3>
            
            <!-- Divider -->
            <div class="paddle-divider"></div>
            
            <!-- Next steps -->
            <p class="paddle-next-steps">
                <?php _e('You can now use your points to generate code or view current balance.', 'paddle-custom-integration'); ?>
            </p>
            
            <!-- Action buttons with gradient to match brand -->
            <div class="paddle-action-links">
                <a href="<?php echo esc_url(home_url('/generate-code/')); ?>" class="paddle-primary-btn">
                    <?php _e('Generate Code', 'paddle-custom-integration'); ?>
                    <svg class="paddle-btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 3H21V11" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11 21H3V13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 3L11 13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                
                <a href="<?php echo esc_url(home_url('/my-balance/')); ?>" class="paddle-secondary-btn">
                    <?php _e('My Balance', 'paddle-custom-integration'); ?>
                    <!-- Updated SVG for 'My Balance' - representing a stack of coins -->
                    <svg class="paddle-btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                        <path d="M16 2a4 4 0 0 1 4 4v1H4V6a4 4 0 0 1 4-4h8z"></path>
                        <line x1="12" y1="14" x2="12" y2="10"></line>
                    </svg>
                </a>
            </div>  

        </div>
        <?php
        return ob_get_clean();
    }


    /**
     * Gets a WooCommerce order by Paddle transaction ID
     * 
     * @param string $transaction_id The Paddle transaction ID
     * @return int|false The order ID if found, false otherwise
     */
    private function get_order_by_transaction_id($transaction_id) {
        $orders = wc_get_orders(array(
            'limit' => 1,
            'return' => 'ids',
            'meta_key' => '_paddle_transaction_id',
            'meta_value' => $transaction_id
        ));
        
        return !empty($orders) ? $orders[0] : false;
    }

    // Add more helper methods as needed (e.g., for custom logging, email notifications).
}

// Instantiate the plugin class.
new My_Paddle_Integration();

// Custom roles for demonstration. You might already have these or define them elsewhere.
// This ensures the roles exist for the plugin to assign.
add_action( 'after_setup_theme', 'my_paddle_register_custom_roles' );
function my_paddle_register_custom_roles() {
    add_role( 'starter_access', __( 'Starter Users Access', 'paddle-custom-integration' ), get_role( 'subscriber' )->capabilities );
    add_role( 'pro_access', __( 'Pro Users Access', 'paddle-custom-integration' ), get_role( 'subscriber' )->capabilities );
    add_role( 'power_access', __( 'Power Users Access', 'paddle-custom-integration' ), get_role( 'subscriber' )->capabilities );
}

/**
 * Deactivation hook.
 * Optional: Clean up plugin data on deactivation.
 * Note: This only runs when the plugin is deactivated, not uninstalled.
 */
function my_paddle_deactivate_plugin() {
    // You might want to remove transients, but generally, don't delete user data on deactivation.
}
register_deactivation_hook( __FILE__, 'my_paddle_deactivate_plugin' );

/**
 * Uninstallation hook.
 * IMPORTANT: This runs when the plugin is DELETED.
 * Use with extreme caution as it removes data.
 */
function my_paddle_uninstall_plugin() {
    // Remove plugin options.
    delete_option( 'paddle_client_side_token' );
    delete_option( 'paddle_api_key' );
    delete_option( 'paddle_webhook_secret' );
    delete_option( 'paddle_environment' );

    // Optional: Remove custom roles. Only do this if you're sure no other plugin uses them.
    remove_role( 'starter_access' );
    remove_role( 'monthly_subscriber' );
    remove_role( 'annual_subscriber' );
    remove_role( 'power_access' );
    remove_role( 'pro_access' );

    // Optional: Remove user meta added by the plugin.
    // This could be heavy on a large site and might affect user access if not carefully managed.
    // global $wpdb;
    // $wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE '_paddle_%'" );
}
register_uninstall_hook( __FILE__, 'my_paddle_uninstall_plugin' );