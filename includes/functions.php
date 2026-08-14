<?php
/**
 * Public developer-facing functions.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send an SMS through Nimba SMS.
 *
 * Usage: nimbasms_send( '624000000', 'Hello from WordPress' );
 *
 * @param string|array $to          One phone number or a list of numbers.
 * @param string       $message     Message body.
 * @param string       $sender_name Optional sender name override.
 * @return array|WP_Error API response or error.
 */
function nimbasms_send( $to, $message, $sender_name = '' ) {
	return NimbaSMS_API::send( $to, $message, $sender_name );
}

/**
 * Send a WhatsApp template message through Nimba SMS.
 *
 * Usage: nimbasms_send_whatsapp( '624000000', 'order_confirmed', array( 'Fode', '45000 GNF' ) );
 *
 * @param string|array $to            One phone number or a list of numbers.
 * @param string       $template_name Approved WhatsApp template name.
 * @param array        $variables     Ordered values for the template body variables {{1}}, {{2}}, ...
 * @param string       $sender_name   Optional sender name override.
 * @return array|WP_Error API response or error.
 */
function nimbasms_send_whatsapp( $to, $template_name, $variables = array(), $sender_name = '' ) {
	return NimbaSMS_API::send_whatsapp( $to, $template_name, $variables, $sender_name );
}

/**
 * Normalize a phone number: keep digits and a leading '+'.
 *
 * @param string $number Raw input.
 * @return string Normalized number, or empty string when invalid.
 */
function nimbasms_normalize_number( $number ) {
	$number = trim( (string) $number );
	$plus   = 0 === strpos( $number, '+' ) ? '+' : '';
	$digits = preg_replace( '/\D+/', '', $number );

	if ( strlen( $digits ) < 8 ) {
		return '';
	}

	/**
	 * Filter a normalized phone number before it is used.
	 *
	 * @param string $normalized Normalized number.
	 * @param string $number     Raw input.
	 */
	return apply_filters( 'nimbasms_normalize_number', $plus . $digits, $number );
}

/**
 * Get the phone number attached to a WP user (meta keys: nimbasms_phone, billing_phone).
 *
 * @param int $user_id User ID.
 * @return string
 */
function nimbasms_get_user_phone( $user_id ) {
	$phone = get_user_meta( $user_id, 'nimbasms_phone', true );

	if ( '' === $phone ) {
		$phone = get_user_meta( $user_id, 'billing_phone', true );
	}

	/**
	 * Filter the phone number resolved for a user.
	 *
	 * @param string $phone   Phone number.
	 * @param int    $user_id User ID.
	 */
	return apply_filters( 'nimbasms_user_phone', (string) $phone, $user_id );
}
