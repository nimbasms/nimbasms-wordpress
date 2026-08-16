<?php
/**
 * Two-factor authentication by SMS on wp-login.
 *
 * Flow: after a valid username/password, a 6-digit code is sent by SMS to the
 * user's phone (profile field, billing_phone fallback). The user submits the
 * code in the extra login-form field to complete sign-in.
 *
 * Safety rails against lockouts:
 * - Users without a phone number sign in normally (fail-open, by design).
 * - define( 'NIMBASMS_DISABLE_2FA', true ) in wp-config.php disables the check.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMS two-factor authentication.
 */
class NimbaSMS_TwoFactor {

	/**
	 * Code lifetime in seconds.
	 */
	const CODE_TTL = 600;

	/**
	 * Minimum delay between two code sends, in seconds.
	 */
	const RESEND_DELAY = 60;

	/**
	 * Maximum wrong attempts per code.
	 */
	const MAX_ATTEMPTS = 5;

	/**
	 * Hook everything.
	 */
	public static function init() {
		// Profile phone field is always available: it is also used by other features.
		add_action( 'show_user_profile', array( __CLASS__, 'render_profile_field' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'render_profile_field' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_profile_field' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_profile_field' ) );

		$settings = get_option( 'nimbasms_settings', array() );

		if ( empty( $settings['tfa_enabled'] ) ) {
			return;
		}

		if ( defined( 'NIMBASMS_DISABLE_2FA' ) && NIMBASMS_DISABLE_2FA ) {
			return;
		}

		add_filter( 'wp_authenticate_user', array( __CLASS__, 'maybe_require_code' ), 30, 1 );
		add_action( 'login_form', array( __CLASS__, 'render_login_field' ) );
	}

	/**
	 * Roles that require a code.
	 *
	 * @return array
	 */
	public static function required_roles() {
		/**
		 * Filter the roles that require SMS two-factor authentication.
		 *
		 * @param array $roles Role slugs.
		 */
		return apply_filters( 'nimbasms_2fa_roles', array( 'administrator' ) );
	}

	/**
	 * Whether this user must pass 2FA.
	 *
	 * @param WP_User $user User.
	 * @return bool
	 */
	private static function needs_code( $user ) {
		return (bool) array_intersect( self::required_roles(), (array) $user->roles );
	}

	/**
	 * Main check, on the wp_authenticate_user filter (password already verified after this filter chain).
	 *
	 * @param WP_User|WP_Error $user User when credentials are valid so far.
	 * @return WP_User|WP_Error
	 */
	public static function maybe_require_code( $user ) {
		if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
			return $user;
		}

		if ( ! self::needs_code( $user ) ) {
			return $user;
		}

		$phone = nimbasms_get_user_phone( $user->ID );
		$phone = nimbasms_normalize_number( $phone );

		// No phone on file: sign in normally (fail-open to avoid lockouts). Set a phone in the user profile to enforce.
		if ( '' === $phone ) {
			return $user;
		}

		$key  = 'nimbasms_2fa_' . $user->ID;
		$data = get_transient( $key );
		// Nonce verification is not applicable on wp-login.php POST; the code itself is the credential.
		$code = isset( $_POST['nimbasms_2fa_code'] ) ? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_POST['nimbasms_2fa_code'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( '' !== $code ) {
			if ( ! is_array( $data ) ) {
				return self::send_code( $user, $phone, $key, __( 'Ce code a expiré. Un nouveau code vient de vous être envoyé par SMS.', 'nimbasms' ) );
			}

			$data['attempts'] = isset( $data['attempts'] ) ? (int) $data['attempts'] + 1 : 1;

			if ( $data['attempts'] > self::MAX_ATTEMPTS ) {
				delete_transient( $key );
				return new WP_Error( 'nimbasms_2fa_locked', __( 'Trop de tentatives. Reconnectez-vous pour recevoir un nouveau code.', 'nimbasms' ) );
			}

			set_transient( $key, $data, self::CODE_TTL );

			if ( isset( $data['hash'] ) && hash_equals( $data['hash'], wp_hash( $code ) ) ) {
				delete_transient( $key );
				return $user;
			}

			return new WP_Error( 'nimbasms_2fa_invalid', __( 'Code de vérification invalide.', 'nimbasms' ) );
		}

		// No code submitted yet: send one (rate-limited) and ask for it.
		if ( is_array( $data ) && isset( $data['sent'] ) && ( time() - (int) $data['sent'] ) < self::RESEND_DELAY ) {
			return new WP_Error( 'nimbasms_2fa_pending', __( 'Un code vous a déjà été envoyé par SMS. Saisissez-le dans le champ « Code de vérification SMS » ci-dessous.', 'nimbasms' ) );
		}

		return self::send_code( $user, $phone, $key, __( 'Un code de vérification vous a été envoyé par SMS. Saisissez-le ci-dessous avec vos identifiants.', 'nimbasms' ) );
	}

	/**
	 * Generate, store and send a code, then return the "code required" error.
	 *
	 * @param WP_User $user    User.
	 * @param string  $phone   Normalized phone.
	 * @param string  $key     Transient key.
	 * @param string  $message Message shown on the login form.
	 * @return WP_Error
	 */
	private static function send_code( $user, $phone, $key, $message ) {
		$code = (string) wp_rand( 100000, 999999 );

		set_transient(
			$key,
			array(
				'hash'     => wp_hash( $code ),
				'sent'     => time(),
				'attempts' => 0,
			),
			self::CODE_TTL
		);

		$sms = sprintf(
			/* translators: 1: verification code, 2: site name. */
			__( '%1$s est votre code de connexion pour %2$s. Valable 10 minutes.', 'nimbasms' ),
			$code,
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		);

		nimbasms_send( $phone, $sms );

		/**
		 * Fires after a 2FA code was sent.
		 *
		 * @param int    $user_id User ID.
		 * @param string $phone   Phone number.
		 */
		do_action( 'nimbasms_2fa_code_sent', $user->ID, $phone );

		return new WP_Error( 'nimbasms_2fa_required', $message );
	}

	/**
	 * Extra field on the login form.
	 */
	public static function render_login_field() {
		?>
		<p>
			<label for="nimbasms_2fa_code"><?php esc_html_e( 'Code de vérification SMS', 'nimbasms' ); ?></label>
			<input type="text" name="nimbasms_2fa_code" id="nimbasms_2fa_code" class="input" value="" size="20"
				autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]*" />
		</p>
		<p class="description" style="margin:-10px 0 12px;font-size:12px;">
			<?php esc_html_e( 'Laissez vide lors de la première étape : le code vous est envoyé après vérification de vos identifiants.', 'nimbasms' ); ?>
		</p>
		<?php
	}

	/**
	 * Phone field on the user profile.
	 *
	 * @param WP_User $user Profile being edited.
	 */
	public static function render_profile_field( $user ) {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}
		?>
		<h2><?php esc_html_e( 'Nimba SMS', 'nimbasms' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="nimbasms_phone"><?php esc_html_e( 'Numéro de téléphone', 'nimbasms' ); ?></label></th>
				<td>
					<input type="text" name="nimbasms_phone" id="nimbasms_phone" class="regular-text"
						value="<?php echo esc_attr( get_user_meta( $user->ID, 'nimbasms_phone', true ) ); ?>" placeholder="6XXXXXXXX" />
					<p class="description"><?php esc_html_e( 'Utilisé pour la double authentification par SMS à la connexion (si activée dans les réglages Nimba SMS).', 'nimbasms' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
		wp_nonce_field( 'nimbasms_profile_phone', 'nimbasms_profile_nonce' );
	}

	/**
	 * Save the profile phone field.
	 *
	 * @param int $user_id User being saved.
	 */
	public static function save_profile_field( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		if ( ! isset( $_POST['nimbasms_profile_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nimbasms_profile_nonce'] ) ), 'nimbasms_profile_phone' ) ) {
			return;
		}

		if ( isset( $_POST['nimbasms_phone'] ) ) {
			update_user_meta( $user_id, 'nimbasms_phone', sanitize_text_field( wp_unslash( $_POST['nimbasms_phone'] ) ) );
		}
	}
}
