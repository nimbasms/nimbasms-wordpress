<?php
/**
 * Webhook receiver: Nimba SMS calls this endpoint on message status changes.
 *
 * Endpoint: POST /wp-json/nimbasms/v1/webhook?token=SECRET
 * Payload (per API schema): { "messageid": uuid, "status": "received"|"failed", "contact": "...", "metadata": { "message_type": "API" } }
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST endpoint for delivery status notifications.
 */
class NimbaSMS_Webhook {

	/**
	 * Hook REST route registration.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_route' ) );
	}

	/**
	 * Get (and lazily create) the webhook secret token.
	 *
	 * @return string
	 */
	public static function secret() {
		$secret = get_option( 'nimbasms_webhook_secret', '' );

		if ( '' === $secret ) {
			$secret = wp_generate_password( 32, false, false );
			update_option( 'nimbasms_webhook_secret', $secret, false );
		}

		return $secret;
	}

	/**
	 * Full webhook URL to paste in the "URL Webhook" field at https://www.nimbasms.com/app/api-keys.
	 *
	 * @return string
	 */
	public static function url() {
		return add_query_arg( 'token', self::secret(), rest_url( 'nimbasms/v1/webhook' ) );
	}

	/**
	 * Regenerate the secret (invalidates the previous URL).
	 *
	 * @return string New secret.
	 */
	public static function regenerate_secret() {
		delete_option( 'nimbasms_webhook_secret' );
		return self::secret();
	}

	/**
	 * Register the REST route.
	 */
	public static function register_route() {
		register_rest_route(
			'nimbasms/v1',
			'/webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle' ),
				'permission_callback' => array( __CLASS__, 'verify' ),
			)
		);
	}

	/**
	 * Verify the shared-secret token (constant-time comparison).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public static function verify( $request ) {
		$token = (string) $request->get_param( 'token' );
		return '' !== $token && hash_equals( self::secret(), $token );
	}

	/**
	 * Handle a status notification.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function handle( $request ) {
		$messageid = sanitize_text_field( (string) $request->get_param( 'messageid' ) );
		$status    = sanitize_key( (string) $request->get_param( 'status' ) );

		if ( '' === $messageid ) {
			return new WP_REST_Response( array( 'status' => 'ignored', 'reason' => 'missing messageid' ), 400 );
		}

		$allowed = array( 'tosend', 'sent', 'received', 'read', 'failure', 'failed', 'not_available' );
		if ( ! in_array( $status, $allowed, true ) ) {
			$status = 'unknown';
		}

		$updated = NimbaSMS_Logger::update_status( $messageid, $status );

		/**
		 * Fires when a delivery status notification is received from Nimba SMS.
		 *
		 * @param string          $messageid Message UUID.
		 * @param string          $status    New status.
		 * @param WP_REST_Request $request   Full request (contact, metadata...).
		 */
		do_action( 'nimbasms_webhook_received', $messageid, $status, $request );

		// Always acknowledge with 200 so Nimba SMS stops retrying (up to 3 attempts otherwise).
		return new WP_REST_Response(
			array(
				'status'  => 'OK',
				'updated' => (bool) $updated,
			),
			200
		);
	}
}
