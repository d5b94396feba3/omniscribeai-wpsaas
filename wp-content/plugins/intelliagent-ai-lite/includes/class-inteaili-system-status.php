<?php
/**
 * System Status Logic for AICA IntelliAgent.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class INTEAILI_System_Status
 *
 * Responsible for generating the diagnostic report for the AI Agent environment,
 * checking server requirements, and verifying plugin configurations.
 */
class INTEAILI_System_Status {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The ID of this plugin.
	 */
	private $plugin_slug;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin_slug = defined( 'INTEAILI_SLUG' ) ? INTEAILI_SLUG : 'intelliagent-ai-lite';
	}

	/**
	 * Compile the system status report data.
	 *
	 * @since  1.0.0
	 * @return array The compiled report containing environment, knowledge, and info sections.
	 */
	public function get_report() {
		$db           = new INTEAILI_DB_Manager( $this->plugin_slug );
		$cron_enabled = ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );
		$next_cron    = wp_next_scheduled( $this->plugin_slug . '_daily_sync_event' );

		$kb_count  = $db->get_kb_count();
		$woo_count = $db->get_woo_product_count();

		$environment_items = array(
			'wp_version'  => $this->check_wp_version(),
			'php_version' => $this->check_php_version(),
			'memory'      => $this->check_memory_limit(),
			'execution'   => $this->check_execution_time(),
			'cron_status' => array(
				'label'   => esc_html__( 'WP-Cron Status', 'intelliagent-ai-lite' ),
				'status'  => $cron_enabled ? 'pass' : 'failed',
				'details' => $cron_enabled ? esc_html__( 'Enabled', 'intelliagent-ai-lite' ) : esc_html__( 'Disabled', 'intelliagent-ai-lite' ),
			),
		);

		// Only add WooCommerce Version if it is detected/active.
		if ( defined( 'WC_VERSION' ) ) {
			$environment_items['woo_version'] = $this->check_woocommerce_version();
		}

		return array(
			'environment'      => array(
				'label' => __( 'Environment', 'intelliagent-ai-lite' ),
				'items' => $environment_items,
			),
			'knowledge_status' => array(
				'label' => __( 'Knowledge Base', 'intelliagent-ai-lite' ),
				'items' => array(
					'kb_sync'       => array(
						'label' => esc_html__( 'Knowledge Base Sync', 'intelliagent-ai-lite' ),
						'value' => $kb_count > 0 ? esc_html__( 'Synced', 'intelliagent-ai-lite' ) : esc_html__( 'Not Synced', 'intelliagent-ai-lite' ),
					),
					'product_sync'  => array(
						'label' => esc_html__( 'Product Catalog Sync', 'intelliagent-ai-lite' ),
						'value' => $woo_count > 0 ? esc_html__( 'Synced', 'intelliagent-ai-lite' ) : esc_html__( 'Not Synced', 'intelliagent-ai-lite' ),
					),
					'system_prompt' => array(
						'label' => esc_html__( 'AI Persona', 'intelliagent-ai-lite' ),
						'value' => ! empty( get_option( 'inteaili_ai_persona' ) ) ? esc_html__( 'Set (', 'intelliagent-ai-lite' ) . strlen( get_option( 'inteaili_ai_persona' ) ) . ' ' . esc_html__( 'characters)', 'intelliagent-ai-lite' ) : esc_html__( 'Not Set', 'intelliagent-ai-lite' ),
					),
					'next_sync'     => array(
						'label' => esc_html__( 'Next Scheduled Task', 'intelliagent-ai-lite' ),
						'value' => $next_cron ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_cron ) : esc_html__( 'Not Scheduled', 'intelliagent-ai-lite' ),
					),
				),
			),
			'info'             => array(
				'label' => __( 'System Information', 'intelliagent-ai-lite' ),
				'items' => array(
					'plugin_ver' => array(
						'label' => esc_html__( 'Plugin Version', 'intelliagent-ai-lite' ),
						'value' => INTEAILI_VERSION,
					),
					'active_llm' => array(
						'label' => esc_html__( 'AI Engine', 'intelliagent-ai-lite' ),
						'value' => get_option( 'inteaili_llm_model', 'Grok' ),
					),
					'llm_status' => array(
						'label' => esc_html__( 'API Key', 'intelliagent-ai-lite' ),
						'value' => $this->check_ai_api_configured() ? esc_html__( 'Configured', 'intelliagent-ai-lite' ) : esc_html__( 'Key Missing', 'intelliagent-ai-lite' ),
					),
					'mysql_ver'  => array(
						'label' => esc_html__( 'MySQL Version', 'intelliagent-ai-lite' ),
						'value' => $db->get_mysql_version(),
					),
					'db_tables'  => array(
						'label' => esc_html__( 'Database Tables', 'intelliagent-ai-lite' ),
						'value' => $db->check_tables_exist() ? esc_html__( 'OK', 'intelliagent-ai-lite' ) : esc_html__( 'Missing', 'intelliagent-ai-lite' ),
					),
				),
			),
		);
	}

	/**
	 * Verify WordPress Version requirements.
	 *
	 * @since  1.0.0
	 * @return array Status data containing label, status, and details.
	 */
	private function check_wp_version() {
		$ver  = get_bloginfo( 'version' );
		$pass = version_compare( $ver, '6.0', '>=' );
		return array(
			'label'   => esc_html__( 'WordPress Version', 'intelliagent-ai-lite' ),
			'status'  => $pass ? 'pass' : 'warning',
			'details' => $ver . ( $pass ? '' : esc_html__( ' (Update Required)', 'intelliagent-ai-lite' ) ),
		);
	}

	/**
	 * Verify WooCommerce Version requirements.
	 *
	 * @since  1.0.0
	 * @return array Status data containing label, status, and details.
	 */
	private function check_woocommerce_version() {
		$woo_ver = defined( 'WC_VERSION' ) ? WC_VERSION : null;
		$pass    = $woo_ver && version_compare( $woo_ver, '8.2', '>=' );

		return array(
			'label'   => esc_html__( 'WooCommerce Version', 'intelliagent-ai-lite' ),
			'status'  => $pass ? 'pass' : 'warning',
			'details' => $woo_ver ? $woo_ver . ( $pass ? '' : esc_html__( ' (Update Required)', 'intelliagent-ai-lite' ) ) : esc_html__( 'Not Active', 'intelliagent-ai-lite' ),
		);
	}

	/**
	 * Verify PHP Version requirements.
	 *
	 * @since  1.0.0
	 * @return array Status data containing label, status, and details.
	 */
	private function check_php_version() {
		$pass = version_compare( PHP_VERSION, '7.4', '>=' );
		return array(
			'label'   => esc_html__( 'PHP Version', 'intelliagent-ai-lite' ),
			'status'  => $pass ? 'pass' : 'failed',
			'details' => PHP_VERSION,
		);
	}

	/**
     * Verify PHP Memory Limit.
     * * @since  1.0.0
     * @return array Status data containing label, status, and details.
     */
    private function check_memory_limit() {
        $limit = ini_get( 'memory_limit' );
        
        // Convert shorthand (e.g., 512M) to integer for comparison
        $limit_value = (int) $limit;
        
        // Check if value is at least 512
        $pass = $limit_value >= 256;
        
        $details = $limit;
        if ( ! $pass ) {
            $details .= esc_html__( ' (At least 256MB recommended for AI tasks)', 'intelliagent-ai-lite' );
        }

        return array(
            'label'   => esc_html__( 'PHP Memory Limit', 'intelliagent-ai-lite' ),
            'status'  => $pass ? 'pass' : 'warning',
            'details' => $details,
        );
    }

    /**
     * Verify PHP Execution Time.
     * * @since  1.0.0
     * @return array Status data containing label, status, and details.
     */
    private function check_execution_time() {
        $time = (int) ini_get( 'max_execution_time' );
        $pass = $time >= 160 || $time === 0; // 0 is infinite
        
        $details = $time . 's';
        if ( $time === 0 ) {
            $details = esc_html__( 'Unlimited (0s)', 'intelliagent-ai-lite' );
        } elseif ( ! $pass ) {
            $details .= esc_html__( ' (At least 160s recommended for AI content generation)', 'intelliagent-ai-lite' );
        }

        return array(
            'label'   => esc_html__( 'Max Execution Time', 'intelliagent-ai-lite' ),
            'status'  => $pass ? 'pass' : 'warning',
            'details' => $details,
        );
    }

	/**
	 * Verify LLM API Configuration status.
	 *
	 * @since  1.0.0
	 * @return bool True if the key for the active model is configured, false otherwise.
	 */
	private function check_ai_api_configured() {
		$model = get_option( 'inteaili_llm_model', 'Gemini' );
		$key   = '';
		switch ( $model ) {
			case 'OpenAI':
				$key = get_option( 'inteaili_openai_api_key' );
				break;
			case 'Gemini':
				$key = get_option( 'inteaili_gemini_api_key' );
				break;
			default:
				$key = get_option( 'inteaili_gemini_api_key' );
				break;
		}
		return ! empty( $key );
	}
}
