<?php
/**
 * WooCommerce integration.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sends SMS on WooCommerce order events.
 */
class NimbaSMS_WooCommerce {

	/**
	 * Hook order events.
	 */
	public static function init() {
		$settings = get_option( 'nimbasms_settings', array() );

		if ( ! empty( $settings['wc_notify_admin_new_order'] ) ) {
			add_action( 'woocommerce_new_order', array( __CLASS__, 'notify_admin_new_order' ), 10, 1 );
		}

		if ( ! empty( $settings['wc_sms_enabled'] ) || ! empty( $settings['wc_wa_enabled'] ) ) {
			add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'notify_customer_status' ), 10, 4 );
		}
	}

	/**
	 * Default customer status templates.
	 *
	 * @return array<string,string>
	 */
	public static function default_templates() {
		return array(
			'processing' => __( '[{site}] Votre commande #{order_id} est confirmée. Montant : {total}. Merci !', 'nimbasms' ),
			'completed'  => __( '[{site}] Votre commande #{order_id} est terminée. Merci de votre confiance !', 'nimbasms' ),
			'cancelled'  => __( '[{site}] Votre commande #{order_id} a été annulée. Contactez-nous pour toute question.', 'nimbasms' ),
		);
	}

	/**
	 * Replace template placeholders with order data.
	 *
	 * @param string   $template Template with {placeholders}.
	 * @param WC_Order $order    Order object.
	 * @return string
	 */
	private static function render( $template, $order ) {
		$replacements = array(
			'{site}'       => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'{order_id}'   => $order->get_order_number(),
			'{total}'      => wp_strip_all_tags( html_entity_decode( wc_price( $order->get_total() ), ENT_QUOTES ) ),
			'{first_name}' => $order->get_billing_first_name(),
			'{last_name}'  => $order->get_billing_last_name(),
			'{status}'     => wc_get_order_status_name( $order->get_status() ),
		);

		/**
		 * Filter WooCommerce SMS template replacements.
		 *
		 * @param array    $replacements Placeholder map.
		 * @param WC_Order $order        Order.
		 */
		$replacements = apply_filters( 'nimbasms_wc_replacements', $replacements, $order );

		return strtr( $template, $replacements );
	}

	/**
	 * SMS the store admin on new order.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function notify_admin_new_order( $order_id ) {
		$settings = get_option( 'nimbasms_settings', array() );
		$phone    = isset( $settings['admin_phone'] ) ? nimbasms_normalize_number( $settings['admin_phone'] ) : '';

		if ( '' === $phone ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$message = self::render(
			__( '[{site}] Nouvelle commande #{order_id} de {first_name} {last_name} — {total}.', 'nimbasms' ),
			$order
		);

		nimbasms_send( $phone, $message );
	}

	/**
	 * SMS the customer on status change.
	 *
	 * @param int      $order_id   Order ID.
	 * @param string   $old_status Old status.
	 * @param string   $new_status New status.
	 * @param WC_Order $order      Order object.
	 */
	public static function notify_customer_status( $order_id, $old_status, $new_status, $order ) {
		$templates = self::default_templates();

		$settings = get_option( 'nimbasms_settings', array() );
		if ( ! empty( $settings['wc_templates'] ) && is_array( $settings['wc_templates'] ) ) {
			foreach ( $settings['wc_templates'] as $status => $tpl ) {
				if ( '' !== trim( (string) $tpl ) ) {
					$templates[ $status ] = (string) $tpl;
				}
			}
		}

		/**
		 * Filter which statuses trigger a customer SMS and their templates.
		 *
		 * @param array $templates Map of status => template.
		 */
		$templates = apply_filters( 'nimbasms_wc_templates', $templates );

		if ( ! isset( $templates[ $new_status ] ) ) {
			return;
		}

		$phone = nimbasms_normalize_number( $order->get_billing_phone() );
		if ( '' === $phone ) {
			return;
		}

		$settings = get_option( 'nimbasms_settings', array() );
		$sms_on   = ! empty( $settings['wc_sms_enabled'][ $new_status ] );
		$wa_on    = ! empty( $settings['wa_enabled'] ) && ! empty( $settings['wc_wa_enabled'][ $new_status ] );

		if ( ! $sms_on && ! $wa_on ) {
			return;
		}

		$message = self::render( $templates[ $new_status ], $order );

		$wa_sent = false;
		if ( $wa_on ) {
			$template_name = isset( $settings['wa_wc_templates'][ $new_status ] ) ? trim( (string) $settings['wa_wc_templates'][ $new_status ] ) : '';
			$vars_spec     = isset( $settings['wa_wc_variables'][ $new_status ] ) ? (string) $settings['wa_wc_variables'][ $new_status ] : '';

			if ( '' !== $template_name ) {
				$variables = array();
				foreach ( array_filter( array_map( 'trim', explode( '|', $vars_spec ) ) ) as $token ) {
					$variables[] = self::render( $token, $order );
				}

				$result  = nimbasms_send_whatsapp( $phone, $template_name, $variables );
				$wa_sent = ! is_wp_error( $result );
			}
		}

		// SMS: sent when enabled, or as fallback when WhatsApp was the only channel and failed.
		if ( $sms_on || ( $wa_on && ! $wa_sent ) ) {
			nimbasms_send( $phone, $message );
		}
	}
}
