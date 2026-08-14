<?php
/**
 * Nimba SMS API client built on the WordPress HTTP API.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin client for https://api.nimbasms.com/v1
 */
class NimbaSMS_API {

	/**
	 * Get stored credentials.
	 *
	 * @return array{service_id:string,secret_token:string}
	 */
	public static function credentials() {
		$settings = get_option( 'nimbasms_settings', array() );
		return array(
			'service_id'   => isset( $settings['service_id'] ) ? (string) $settings['service_id'] : '',
			'secret_token' => isset( $settings['secret_token'] ) ? (string) $settings['secret_token'] : '',
		);
	}

	/**
	 * Whether the plugin is configured.
	 *
	 * @return bool
	 */
	public static function is_configured() {
		$creds = self::credentials();
		return '' !== $creds['service_id'] && '' !== $creds['secret_token'];
	}

	/**
	 * Perform an authenticated request against the API.
	 *
	 * @param string $method HTTP method.
	 * @param string $path   API path beginning with '/'.
	 * @param array  $body   Optional JSON body.
	 * @return array|WP_Error Decoded JSON array on success.
	 */
	public static function request( $method, $path, $body = null ) {
		if ( ! self::is_configured() ) {
			return new WP_Error( 'nimbasms_not_configured', __( 'Nimba SMS n’est pas configuré. Renseignez votre SERVICE ID et votre SECRET TOKEN.', 'nimbasms' ) );
		}

		$creds = self::credentials();

		global $wp_version;

		$args = array(
			'method'     => $method,
			'timeout'    => 20,
			'user-agent' => 'NimbaSMS-WordPress/' . NIMBASMS_VERSION . ' (WordPress/' . $wp_version . '; PHP/' . PHP_VERSION . '; +https://github.com/nimbasms/nimbasms-wordpress)',
			'headers'    => array(
				'Authorization'         => 'Basic ' . base64_encode( $creds['service_id'] . ':' . $creds['secret_token'] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'Accept'                => 'application/json',
				'Content-Type'          => 'application/json',
				'X-Nimba-Client'        => 'wordpress-plugin',
				'X-Nimba-Client-Version' => NIMBASMS_VERSION,
			),
		);

		if ( null !== $body ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( NIMBASMS_API_BASE . $path, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 400 ) {
			$message = '';
			if ( is_array( $data ) ) {
				$message = isset( $data['detail'] ) ? $data['detail'] : wp_json_encode( $data );
			}
			return new WP_Error(
				'nimbasms_api_error',
				sprintf(
					/* translators: 1: HTTP status code, 2: API error message. */
					__( 'Erreur API Nimba SMS (HTTP %1$d) : %2$s', 'nimbasms' ),
					$code,
					$message
				)
			);
		}

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Get account details (balance).
	 *
	 * @return array|WP_Error
	 */
	public static function get_account() {
		return self::request( 'GET', '/accounts' );
	}

	/**
	 * Get approved sender names.
	 *
	 * @return array|WP_Error
	 */
	public static function get_sendernames() {
		return self::request( 'GET', '/sendernames' );
	}

	/**
	 * Send an SMS.
	 *
	 * @param string|array $to          One number or a list of numbers.
	 * @param string       $message     Message body.
	 * @param string       $sender_name Optional sender name; defaults to the configured one.
	 * @return array|WP_Error
	 */
	public static function send( $to, $message, $sender_name = '' ) {
		$settings = get_option( 'nimbasms_settings', array() );

		if ( '' === $sender_name ) {
			$sender_name = isset( $settings['sender_name'] ) ? (string) $settings['sender_name'] : '';
		}

		$numbers = array_values( array_filter( array_map( 'nimbasms_normalize_number', (array) $to ) ) );

		if ( empty( $numbers ) ) {
			return new WP_Error( 'nimbasms_invalid_number', __( 'Aucun numéro de téléphone valide.', 'nimbasms' ) );
		}

		$payload = array(
			'to'          => $numbers,
			'message'     => $message,
			'sender_name' => $sender_name,
		);

		/**
		 * Filter the payload sent to the Nimba SMS API.
		 *
		 * @param array $payload Request payload.
		 */
		$payload = apply_filters( 'nimbasms_send_payload', $payload );

		$result = self::request( 'POST', '/messages', $payload );

		NimbaSMS_Logger::log( $numbers, $message, $result );

		/**
		 * Fires after an SMS send attempt.
		 *
		 * @param array          $numbers Recipients.
		 * @param string         $message Message body.
		 * @param array|WP_Error $result  API response or error.
		 */
		do_action( 'nimbasms_after_send', $numbers, $message, $result );

		return $result;
	}
}
