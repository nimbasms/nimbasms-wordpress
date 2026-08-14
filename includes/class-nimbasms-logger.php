<?php
/**
 * SMS send log stored in a custom table.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger.
 */
class NimbaSMS_Logger {

	/**
	 * Table name (with prefix).
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'nimbasms_logs';
	}

	/**
	 * Create the log table on activation.
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$table           = self::table();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			recipients TEXT NOT NULL,
			message TEXT NOT NULL,
			channel VARCHAR(20) NOT NULL DEFAULT 'sms',
			status VARCHAR(40) NOT NULL DEFAULT '',
			message_id VARCHAR(100) NOT NULL DEFAULT '',
			message_cost INT UNSIGNED NOT NULL DEFAULT 0,
			error TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Record a send attempt.
	 *
	 * @param array          $numbers Recipients.
	 * @param string         $message Message body.
	 * @param array|WP_Error $result  API result.
	 * @param string         $channel Channel used (sms, whatsapp).
	 */
	public static function log( $numbers, $message, $result, $channel = 'sms' ) {
		global $wpdb;

		$is_error = is_wp_error( $result );

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			self::table(),
			array(
				'recipients'   => implode( ', ', array_map( 'sanitize_text_field', $numbers ) ),
				'message'      => sanitize_textarea_field( $message ),
				'channel'      => sanitize_key( $channel ),
				'status'       => $is_error ? 'error' : ( isset( $result['status'] ) ? sanitize_text_field( (string) $result['status'] ) : 'sent' ),
				'message_id'   => ( ! $is_error && isset( $result['messageid'] ) ) ? sanitize_text_field( (string) $result['messageid'] ) : '',
				'message_cost' => ( ! $is_error && isset( $result['message_cost'] ) ) ? absint( $result['message_cost'] ) : 0,
				'error'        => $is_error ? $result->get_error_message() : null,
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
		);
	}

	/**
	 * Update the status of a logged message by its API message id.
	 *
	 * @param string $message_id API message UUID.
	 * @param string $status     New delivery status.
	 * @return int|false Number of rows updated, or false on error.
	 */
	public static function update_status( $message_id, $status ) {
		global $wpdb;

		return $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			self::table(),
			array( 'status' => sanitize_key( $status ) ),
			array( 'message_id' => sanitize_text_field( $message_id ) ),
			array( '%s' ),
			array( '%s' )
		);
	}

	/**
	 * Fetch recent log entries.
	 *
	 * @param int $limit Number of rows.
	 * @return array
	 */
	public static function recent( $limit = 50 ) {
		global $wpdb;

		$table = esc_sql( self::table() );

		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY id DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $limit )
			)
		);
	}
}
