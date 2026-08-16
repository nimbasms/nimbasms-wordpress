<?php
/**
 * Contact Form 7 and WPForms integration.
 *
 * Sends an SMS to the administrator on each form submission, and optionally
 * a confirmation SMS to the visitor when the form contains a phone field.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form plugins integration.
 */
class NimbaSMS_Forms {

	/**
	 * Hook into supported form plugins when they are active.
	 */
	public static function init() {
		$settings = get_option( 'nimbasms_settings', array() );

		if ( empty( $settings['forms_admin_sms'] ) && empty( $settings['forms_visitor_sms'] ) ) {
			return;
		}

		// Contact Form 7: wpcf7_submit fires on every submission, even when the
		// notification email fails (common on servers without mail configured).
		if ( class_exists( 'WPCF7' ) ) {
			add_action( 'wpcf7_submit', array( __CLASS__, 'on_cf7_submission' ), 10, 2 );
		}

		// WPForms (Lite or Pro).
		if ( function_exists( 'wpforms' ) ) {
			add_action( 'wpforms_process_complete', array( __CLASS__, 'on_wpforms_submission' ), 10, 3 );
		}
	}

	/**
	 * Whether at least one supported form plugin is active.
	 *
	 * @return bool
	 */
	public static function has_form_plugin() {
		return class_exists( 'WPCF7' ) || function_exists( 'wpforms' );
	}

	/**
	 * Default message templates.
	 *
	 * @return array{admin:string,visitor:string}
	 */
	public static function default_templates() {
		return array(
			'admin'   => __( '[{site}] Nouvelle soumission du formulaire « {form} ».', 'nimbasms' ),
			'visitor' => __( '[{site}] Nous avons bien reçu votre message. Merci, nous revenons vers vous rapidement.', 'nimbasms' ),
		);
	}

	/**
	 * Render a template with common placeholders.
	 *
	 * @param string $template   Template with {placeholders}.
	 * @param string $form_title Form title.
	 * @return string
	 */
	private static function render( $template, $form_title ) {
		$replacements = array(
			'{site}' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'{form}' => $form_title,
		);

		/**
		 * Filter the placeholder replacements for form notification messages.
		 *
		 * @param array  $replacements Placeholder map.
		 * @param string $form_title   Form title.
		 */
		$replacements = apply_filters( 'nimbasms_forms_replacements', $replacements, $form_title );

		return strtr( $template, $replacements );
	}

	/**
	 * Send the configured notifications for one submission.
	 *
	 * @param string $form_title    Form title.
	 * @param string $visitor_phone Visitor phone number ('' when not found).
	 */
	private static function notify( $form_title, $visitor_phone ) {
		$settings  = get_option( 'nimbasms_settings', array() );
		$templates = self::default_templates();

		if ( ! empty( $settings['forms_admin_template'] ) ) {
			$templates['admin'] = (string) $settings['forms_admin_template'];
		}
		if ( ! empty( $settings['forms_visitor_template'] ) ) {
			$templates['visitor'] = (string) $settings['forms_visitor_template'];
		}

		// Admin notification.
		if ( ! empty( $settings['forms_admin_sms'] ) ) {
			$admin_phone = isset( $settings['admin_phone'] ) ? nimbasms_normalize_number( $settings['admin_phone'] ) : '';
			if ( '' !== $admin_phone ) {
				nimbasms_send( $admin_phone, self::render( $templates['admin'], $form_title ) );
			}
		}

		// Visitor confirmation.
		if ( ! empty( $settings['forms_visitor_sms'] ) && '' !== $visitor_phone ) {
			nimbasms_send( $visitor_phone, self::render( $templates['visitor'], $form_title ) );
		}
	}

	/**
	 * Find a phone number in submitted values.
	 *
	 * Priority: the configured field name, then any key containing 'phone' or 'tel'.
	 *
	 * @param array $values Field name => value map.
	 * @return string Normalized number, or '' when none found.
	 */
	private static function find_phone( $values ) {
		$settings   = get_option( 'nimbasms_settings', array() );
		$field_name = isset( $settings['forms_phone_field'] ) ? trim( (string) $settings['forms_phone_field'] ) : '';

		$candidates = array();

		if ( '' !== $field_name && isset( $values[ $field_name ] ) ) {
			$candidates[] = $values[ $field_name ];
		}

		foreach ( $values as $key => $value ) {
			if ( false !== stripos( (string) $key, 'phone' ) || false !== stripos( (string) $key, 'tel' ) ) {
				$candidates[] = $value;
			}
		}

		foreach ( $candidates as $candidate ) {
			if ( is_array( $candidate ) ) {
				$candidate = reset( $candidate );
			}
			$normalized = nimbasms_normalize_number( (string) $candidate );
			if ( '' !== $normalized ) {
				/**
				 * Filter the visitor phone number detected in a form submission.
				 *
				 * @param string $normalized Normalized number.
				 * @param array  $values     Submitted values.
				 */
				return apply_filters( 'nimbasms_forms_visitor_phone', $normalized, $values );
			}
		}

		return apply_filters( 'nimbasms_forms_visitor_phone', '', $values );
	}

	/**
	 * Contact Form 7 submission handler.
	 *
	 * @param WPCF7_ContactForm $contact_form Submitted form.
	 * @param array             $result       Submission result (status, message).
	 */
	public static function on_cf7_submission( $contact_form, $result = array() ) {
		// Only for accepted submissions: mail sent, or mail failed after a valid submission.
		$status = isset( $result['status'] ) ? $result['status'] : '';
		if ( ! in_array( $status, array( 'mail_sent', 'mail_failed' ), true ) ) {
			return;
		}

		$values     = array();
		$submission = class_exists( 'WPCF7_Submission' ) ? WPCF7_Submission::get_instance() : null;

		if ( $submission ) {
			$values = (array) $submission->get_posted_data();
		}

		self::notify( $contact_form->title(), self::find_phone( $values ) );
	}

	/**
	 * WPForms submission handler.
	 *
	 * @param array $fields    Submitted fields (id => {name, value, type}).
	 * @param array $entry     Raw entry.
	 * @param array $form_data Form settings and metadata.
	 */
	public static function on_wpforms_submission( $fields, $entry, $form_data ) {
		$values = array();
		$phone  = '';

		foreach ( (array) $fields as $field ) {
			$name  = isset( $field['name'] ) ? (string) $field['name'] : '';
			$value = isset( $field['value'] ) ? $field['value'] : '';

			if ( '' !== $name ) {
				$values[ $name ] = $value;
			}

			// WPForms has a dedicated phone field type: prefer it.
			if ( '' === $phone && isset( $field['type'] ) && 'phone' === $field['type'] ) {
				$phone = nimbasms_normalize_number( (string) $value );
			}
		}

		if ( '' === $phone ) {
			$phone = self::find_phone( $values );
		}

		$form_title = isset( $form_data['settings']['form_title'] ) ? (string) $form_data['settings']['form_title'] : __( 'Formulaire', 'nimbasms' );

		self::notify( $form_title, $phone );
	}
}
