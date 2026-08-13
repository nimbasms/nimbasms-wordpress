<?php
/**
 * Core WordPress event notifications.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sends admin SMS notifications on selected WordPress events.
 */
class NimbaSMS_Notifications {

	/**
	 * Hook everything.
	 */
	public static function init() {
		$settings = get_option( 'nimbasms_settings', array() );

		if ( ! empty( $settings['notify_new_user'] ) ) {
			add_action( 'user_register', array( __CLASS__, 'on_new_user' ), 10, 1 );
		}

		if ( ! empty( $settings['notify_new_comment'] ) ) {
			add_action( 'comment_post', array( __CLASS__, 'on_new_comment' ), 10, 2 );
		}
	}

	/**
	 * Admin phone number from settings.
	 *
	 * @return string
	 */
	private static function admin_phone() {
		$settings = get_option( 'nimbasms_settings', array() );
		return isset( $settings['admin_phone'] ) ? nimbasms_normalize_number( $settings['admin_phone'] ) : '';
	}

	/**
	 * New user registered.
	 *
	 * @param int $user_id User ID.
	 */
	public static function on_new_user( $user_id ) {
		$phone = self::admin_phone();
		if ( '' === $phone ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: site name, 2: user login. */
			__( '[%1$s] Nouvel utilisateur inscrit : %2$s', 'nimbasms' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			$user->user_login
		);

		nimbasms_send( $phone, $message );
	}

	/**
	 * New comment posted.
	 *
	 * @param int        $comment_id Comment ID.
	 * @param int|string $approved   Approval status.
	 */
	public static function on_new_comment( $comment_id, $approved ) {
		if ( 'spam' === $approved ) {
			return;
		}

		$phone = self::admin_phone();
		if ( '' === $phone ) {
			return;
		}

		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: site name, 2: comment author, 3: post title. */
			__( '[%1$s] Nouveau commentaire de %2$s sur « %3$s »', 'nimbasms' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			$comment->comment_author,
			get_the_title( (int) $comment->comment_post_ID )
		);

		nimbasms_send( $phone, $message );
	}
}
