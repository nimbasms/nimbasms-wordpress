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
delete_option( 'nimbasms_webhook_secret' );

$table = $wpdb->prefix . 'nimbasms_logs';
$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore
