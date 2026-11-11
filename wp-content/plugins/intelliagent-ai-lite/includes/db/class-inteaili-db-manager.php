<?php
/**
 * Database Management for the IntelliAgent AI Lite plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles all database operations including table creation, CRUD for logs,
 * feedback, FAQs, and the knowledge base.
 *
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 * @author     IntelliAgent Team
 */
class INTEAILI_DB_Manager {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The ID of this plugin.
	 */
	private $plugin_slug;

	/**
	 * The sanitized slug used for table prefixes.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $tbl_prefix     Sanitized version of slug (underscores).
	 */
	private $tbl_prefix;

	/**
	 * The WordPress Database Object.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      wpdb      $wpdb           WordPress database instance.
	 */
	protected $wpdb;

	/**
	 * The chat logs table name.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $table_chat_logs Table name with prefix.
	 */
	protected $table_chat_logs;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_slug    The slug of the plugin.
	 */
	public function __construct( $plugin_slug ) {
		global $wpdb;
		$this->wpdb            = $wpdb;
		$this->plugin_slug     = $plugin_slug;
		$this->tbl_prefix      = 'inteaili';
		$this->table_chat_logs = $this->wpdb->prefix . $this->tbl_prefix . '_chat_logs';
	}

	/**
	 * Create the required database tables on activation.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_slug    The slug of the plugin.
	 * @return   void
	 */
	public static function create_table( $plugin_slug ) {
		global $wpdb;
		$tbl_prefix = 'inteaili';

		$t1 = $wpdb->prefix . $tbl_prefix . '_chat_logs';
		$t2 = $wpdb->prefix . $tbl_prefix . '_feedback';
		$t3 = $wpdb->prefix . $tbl_prefix . '_knowledge_base';
		$t4 = $wpdb->prefix . $tbl_prefix . '_faqs';

		$charset = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( "CREATE TABLE $t1 (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, session_id VARCHAR(255) NOT NULL, full_history LONGTEXT NOT NULL, last_updated DATETIME NOT NULL, PRIMARY KEY (id), KEY session_id (session_id(191))) $charset;" );
		dbDelta( "CREATE TABLE $t2 (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, user_query TEXT NOT NULL, ai_response TEXT NOT NULL, rating VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) $charset;" );
		dbDelta( "CREATE TABLE $t3 (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, source_url VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, last_synced DATETIME NOT NULL, PRIMARY KEY (id), UNIQUE KEY source_url (source_url(191))) $charset;" );
		dbDelta( "CREATE TABLE $t4 (id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, title VARCHAR(255) NOT NULL, question TEXT NOT NULL, answer TEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) $charset;" );
	}

	/**
	 * Get the knowledge base table name.
	 *
	 * @since    1.0.0
	 * @return   string    Table name with prefix.
	 */
	public function get_kb_table_name() {
		return $this->wpdb->prefix . $this->tbl_prefix . '_knowledge_base'; }

	/**
	 * Get the feedback table name.
	 *
	 * @since    1.0.0
	 * @return   string    Table name with prefix.
	 */
	private function get_feedback_table_name() {
		return $this->wpdb->prefix . $this->tbl_prefix . '_feedback'; }

	/**
	 * Get the FAQ table name.
	 *
	 * @since    1.0.0
	 * @return   string    Table name with prefix.
	 */
	private function get_faq_table_name() {
		return $this->wpdb->prefix . $this->tbl_prefix . '_faqs'; }

	/**
	 * Store user feedback.
	 *
	 * @since    1.0.0
	 * @param    string $query       User's question.
	 * @param    string $response    AI's response.
	 * @param    string $rating      The rating (like/dislike).
	 * @return   int|bool               Number of rows inserted or false on error.
	 */
	public function store_feedback( $query, $response, $rating ) {
		return $this->wpdb->insert(
			$this->get_feedback_table_name(),
			array(
				'user_query'  => $query,
				'ai_response' => $response,
				'rating'      => $rating,
				'created_at'  => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Update or replace knowledge base entries.
	 *
	 * @since    1.0.0
	 * @param    string $url         Source URL.
	 * @param    string $content     Sanitized content.
	 * @return   int|bool               The number of affected rows.
	 */
	public function update_knowledge_base( $url, $content ) {
		return $this->wpdb->replace(
			$this->get_kb_table_name(),
			array(
				'source_url'  => esc_url_raw( $url ),
				'content'     => trim( $content ),
				'last_synced' => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Retrieve KB content as a single formatted string for AI context.
	 * Optimized to prevent memory exhaustion.
	 *
	 * @since    1.0.0
	 * @return   string Formatted content string.
	 */
	public function get_kb_content_as_string() {
		$table = $this->get_kb_table_name();
		
		// 100 entries is usually enough for context without hitting RAM limits.
		$results = $this->wpdb->get_results( "SELECT source_url, content FROM $table LIMIT 100", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		
		if ( ! $results ) {
			return '';
		}

		$text = '';
		foreach ( $results as $row ) {
			$chunk = 'Page URL: ' . esc_url( $row['source_url'] ) . "\n" . $row['content'] . "\n\n";
			
			// Safety Check: If the accumulated string is approaching 512KB, stop.
			if ( ( strlen( $text ) + strlen( $chunk ) ) > 524288 ) {
				$text .= "... [Knowledge Truncated for Performance]";
				break;
			}
			
			$text .= $chunk;
		}
		
		return $text;
	}

	/**
	 * Get feedback based on rating type.
	 *
	 * @since    1.0.0
	 * @param    string $rating      Rating type (like/dislike).
	 * @param    int    $limit       Query limit.
	 * @return   array                  Array of feedback results.
	 */
	public function get_feedback_by_type( string $rating, int $limit = 5 ) {
		$table = $this->get_feedback_table_name();
		return $this->wpdb->get_results( $this->wpdb->prepare( 'SELECT user_query, ai_response FROM ' . $table . ' WHERE rating = %s ORDER BY created_at DESC LIMIT %d', sanitize_key( $rating ), (int) $limit ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Save or update chat history logs for a session.
	 *
	 * @since    1.0.0
	 * @param    string $session_id    The session ID.
	 * @param    array  $history_array The conversation history.
	 * @return   int|bool                 Affected rows.
	 */
	public function save_chat_history( $session_id, $history_array ) {
		$table  = $this->table_chat_logs;
		$s_id   = sanitize_key( $session_id );
		$exists = $this->wpdb->get_var( $this->wpdb->prepare( 'SELECT id FROM ' . $table . ' WHERE session_id = %s', $s_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $exists ) {
			return $this->wpdb->update(
				$table,
				array(
					'full_history' => wp_json_encode( $history_array ),
					'last_updated' => current_time( 'mysql' ),
				),
				array( 'session_id' => $s_id )
			);
		}
		return $this->wpdb->insert(
			$table,
			array(
				'session_id'   => $s_id,
				'full_history' => wp_json_encode( $history_array ),
				'last_updated' => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Retrieve all chat logs.
	 *
	 * @since    1.0.0
	 * @return   array    Array of logs.
	 */
	public function get_all_chat_logs() {
        $table = $this->table_chat_logs;
        return $this->wpdb->get_results( "SELECT * FROM $table ORDER BY last_updated DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

	/**
	 * Get stats for the dashboard.
	 *
	 * @since    1.0.0
	 * @return   array    Total and Recent counts.
	 */
	public function get_chat_stats() {
        $table = $this->table_chat_logs;
        return array(
            'total'  => (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM $table" ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            'recent' => (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE last_updated >= DATE_SUB(NOW(), INTERVAL 7 DAY)" ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        );
    }

	/**
	 * Delete a specific chat log.
	 *
	 * @since    1.0.0
	 * @param    int $id    Log ID.
	 * @return   int|bool         Rows deleted.
	 */
	public function delete_chat_log( $id ) {
		return $this->wpdb->delete( $this->table_chat_logs, array( 'id' => absint( $id ) ), array( '%d' ) );
	}

	/**
	 * Get a log entry by ID.
	 *
	 * @since    1.0.0
	 * @param    int $log_id    Log ID.
	 * @return   array|null           Log data.
	 */
	public function get_chat_log_by_id( $log_id ) {
		$table = $this->table_chat_logs;
		$l_id  = absint( $log_id );
		return $this->wpdb->get_row( $this->wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d LIMIT 1', $l_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Retrieve logs for export purposes.
	 *
	 * @since    1.0.0
	 * @param    int $id    Optional specific ID.
	 * @return   array            Logs array.
	 */
	public function get_logs_for_export( $id = 0 ) {
        $table = $this->table_chat_logs;
        $l_id  = absint( $id );
        if ( $l_id > 0 ) {
            return $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $l_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }
        return $this->wpdb->get_results( "SELECT * FROM $table ORDER BY last_updated DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

	/**
	 * Get sync status for a URL in KB.
	 *
	 * @since    1.0.0
	 * @param    string $url    The URL.
	 * @return   array|null        Synced data.
	 */
	public function get_sync_status( $url ) {
		$table = $this->get_kb_table_name();
		$s_url = esc_url_raw( $url );
		return $this->wpdb->get_row( $this->wpdb->prepare( 'SELECT last_synced FROM ' . $table . ' WHERE source_url = %s', $s_url ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get all feedback entries.
	 *
	 * @since    1.0.0
	 * @return   array    Feedback array.
	 */
	public function get_all_feedback() {
        $table = $this->get_feedback_table_name();
        return $this->wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

	/**
	 * Get a feedback entry by ID.
	 *
	 * @since    1.0.0
	 * @param    int $id    Feedback ID.
	 * @return   array|null       Feedback data.
	 */
	public function get_feedback_by_id( $id ) {
		$table = $this->get_feedback_table_name();
		$f_id  = absint( $id );
		return $this->wpdb->get_row( $this->wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d', $f_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Update feedback data.
	 *
	 * @since    1.0.0
	 * @param    int   $id      Feedback ID.
	 * @param    array $data    Update data.
	 * @return   int|bool           Affected rows.
	 */
	public function update_feedback( $id, $data ) {
		return $this->wpdb->update( $this->get_feedback_table_name(), $data, array( 'id' => absint( $id ) ) );
	}

	/**
	 * Delete a feedback entry.
	 *
	 * @since    1.0.0
	 * @param    int $id    Feedback ID.
	 * @return   int|bool         Rows deleted.
	 */
	public function delete_feedback( $id ) {
		return $this->wpdb->delete( $this->get_feedback_table_name(), array( 'id' => absint( $id ) ), array( '%d' ) );
	}

	/**
	 * Calculate helpfulness statistics.
	 *
	 * @since    1.0.0
	 * @return   array    Stats array.
	 */
	public function get_feedback_stats() {
        $table   = $this->get_feedback_table_name();
        $results = $this->wpdb->get_results( "SELECT rating, COUNT(*) as count FROM $table GROUP BY rating", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $stats   = array(
            'total'     => 0,
            'helpful'   => 0,
            'unhelpful' => 0,
        );
        if ( $results ) {
            foreach ( $results as $row ) {
                $stats['total'] += (int) $row['count'];
                if ( 'like' === $row['rating'] ) {
                    $stats['helpful'] = (int) $row['count']; } else {
                    $stats['unhelpful'] = (int) $row['count']; }
            }
        }
        return $stats;
    }

	/**
	 * Get all FAQ entries.
	 *
	 * @since    1.0.0
	 * @return   array    FAQ array.
	 */
	public function get_all_faqs() {
        $table = $this->get_faq_table_name();
        return $this->wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

	/**
	 * Get an FAQ by ID.
	 *
	 * @since    1.0.0
	 * @param    int $id    FAQ ID.
	 * @return   array|null       FAQ data.
	 */
	public function get_faq_by_id( $id ) {
		$table = $this->get_faq_table_name();
		$f_id  = absint( $id );
		return $this->wpdb->get_row( $this->wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id = %d', $f_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Save or update an FAQ.
	 *
	 * @since    1.0.0
	 * @param    array $data    FAQ content data.
	 * @param    int   $id      Optional ID for update.
	 * @return   int|bool           Affected rows.
	 */
	public function save_faq( $data, $id = 0 ) {
		$table = $this->get_faq_table_name();
		if ( (int) $id > 0 ) {
			return $this->wpdb->update( $table, $data, array( 'id' => absint( $id ) ) ); }
		$data['created_at'] = current_time( 'mysql' );
		return $this->wpdb->insert( $table, $data );
	}

	/**
	 * Delete an FAQ entry.
	 *
	 * @since    1.0.0
	 * @param    int $id    FAQ ID.
	 * @return   int|bool         Rows deleted.
	 */
	public function delete_faq( $id ) {
		return $this->wpdb->delete( $this->get_faq_table_name(), array( 'id' => absint( $id ) ), array( '%d' ) );
	}

	/**
	 * Get MySQL version.
	 *
	 * @since    1.0.0
	 * @return   string    DB version.
	 */
	public function get_mysql_version() {
		return $this->wpdb->db_version(); }

	/**
	 * Check if all required plugin tables exist.
	 *
	 * @since    1.0.0
	 * @return   bool      True if all tables exist.
	 */
	public function check_tables_exist() {
		$tables_list = array( $this->table_chat_logs, $this->get_feedback_table_name(), $this->get_kb_table_name(), $this->get_faq_table_name() );
		foreach ( $tables_list as $single_table ) {
			$escaped_table = $this->wpdb->esc_like( $single_table );
			$check_query   = $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $escaped_table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $this->wpdb->get_var( $check_query ) !== $single_table ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				return false;
			}
		}
		return true;
	}

	/**
	 * Count WooCommerce product entries in the KB.
	 *
	 * @since    1.0.0
	 * @return   int       Product count.
	 */
	public function get_woo_product_count() {
		$table    = $this->get_kb_table_name();
		$esc_like = '%' . $this->wpdb->esc_like( '/product/' ) . '%';
		return (int) $this->wpdb->get_var( $this->wpdb->prepare( 'SELECT COUNT(*) FROM ' . $table . ' WHERE source_url LIKE %s', $esc_like ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get total KB entries count.
	 *
	 * @since    1.0.0
	 * @return   int       KB count.
	 */
	public function get_kb_count() {
        $table = $this->get_kb_table_name();
        return (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

	/**
	 * Get total FAQ entries count.
	 *
	 * @since    1.0.0
	 * @return   int       FAQ count.
	 */
	public function get_faq_count() {
		$table = $this->get_faq_table_name();
		return (int) $this->wpdb->get_var( $this->wpdb->prepare( 'SELECT COUNT(*) FROM ' . $table ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Bulk retrieve logs by session IDs.
	 *
	 * @since    1.0.0
	 * @param    array $session_ids    Array of session strings.
	 * @return   array                     Matching logs.
	 */
	public function get_logs_by_session_ids( array $session_ids ) {
		if ( empty( $session_ids ) ) {
			return array();
		}

		$table        = $this->table_chat_logs;
		$placeholders = implode( ',', array_fill( 0, count( $session_ids ), '%s' ) );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $this->wpdb->prepare(
			"SELECT session_id, full_history, last_updated FROM $table WHERE session_id IN ($placeholders) ORDER BY last_updated DESC",
			$session_ids
		);

		return $this->wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
