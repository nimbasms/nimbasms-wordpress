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
			status VARCHAR(40) NOT NULL DEFAULT '',
			message_id VARCHAR(100) NOT NULL DEFAULT '',
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
	 */
	public static function log( $numbers, $message, $result ) {
		global $wpdb;

		$is_error = is_wp_error( $result );

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			self::table(),
			array(
				'recipients' => implode( ', ', array_map( 'sanitize_text_field', $numbers ) ),
				'message'    => sanitize_textarea_field( $message ),
				'status'     => $is_error ? 'error' : ( isset( $result['status'] ) ? sanitize_text_field( (string) $result['status'] ) : 'sent' ),
				'message_id' => ( ! $is_error && isset( $result['messageid'] ) ) ? sanitize_text_field( (string) $result['messageid'] ) : '',
				'error'      => $is_error ? $result->get_error_message() : null,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
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

		$table = self::table();

		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY id DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $limit )
			)
		);
	}
}
