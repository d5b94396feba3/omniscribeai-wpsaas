<?php
namespace MediumPostAI;

defined('ABSPATH') || exit;

class ErrorLogger {
    private static $table_name = 'MPAI_error_logs';

    public static function init() {
        add_action('plugins_loaded', [__CLASS__, 'create_error_table']);
    }

    public static function create_error_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            user_name varchar(255) NOT NULL,
            error_code varchar(50) NOT NULL,
            error_message text NOT NULL,
            request_type varchar(50) NOT NULL,
            request_data text,
            stack_trace text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY error_code (error_code),
            KEY request_type (request_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function log_error($error_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $user_name = $current_user->user_login;

        $defaults = [
            'error_code' => 'unknown',
            'error_message' => 'Unknown error occurred',
            'request_type' => 'unknown',
            'request_data' => '',
            'stack_trace' => ''
        ];

        $error_data = wp_parse_args($error_data, $defaults);

        $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'user_name' => $user_name,
                'error_code' => $error_data['error_code'],
                'error_message' => $error_data['error_message'],
                'request_type' => $error_data['request_type'],
                'request_data' => is_array($error_data['request_data']) ? json_encode($error_data['request_data']) : $error_data['request_data'],
                'stack_trace' => $error_data['stack_trace']
            ],
            [
                '%d', // user_id
                '%s', // user_name
                '%s', // error_code
                '%s', // error_message
                '%s', // request_type
                '%s', // request_data
                '%s'  // stack_trace
            ]
        );

        return $wpdb->insert_id;
    }
}

// Initialize the error log class
ErrorLogger::init();