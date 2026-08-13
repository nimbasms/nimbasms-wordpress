<?php
/**
 * Cleanup on uninstall.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

delete_option( 'nimbasms_settings' );

$table = $wpdb->prefix . 'nimbasms_logs';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore
