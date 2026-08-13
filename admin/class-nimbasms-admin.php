<?php
/**
 * Admin UI: settings, manual send, log.
 *
 * @package NimbaSMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin pages.
 */
class NimbaSMS_Admin {

	/**
	 * Hook admin pieces.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_forms' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( NIMBASMS_PLUGIN_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * "Réglages" link on the plugins list.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'admin.php?page=nimbasms' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages', 'nimbasms' ) . '</a>' );
		return $links;
	}

	/**
	 * Register the menu page.
	 */
	public static function register_menu() {
		add_menu_page(
			__( 'Nimba SMS', 'nimbasms' ),
			__( 'Nimba SMS', 'nimbasms' ),
			'manage_options',
			'nimbasms',
			array( __CLASS__, 'render_page' ),
			'dashicons-email-alt2',
			58
		);
	}

	/**
	 * Handle settings + manual send form submissions.
	 */
	public static function handle_forms() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Save settings.
		if ( isset( $_POST['nimbasms_save_settings'] ) && check_admin_referer( 'nimbasms_settings', 'nimbasms_nonce' ) ) {
			$settings = get_option( 'nimbasms_settings', array() );

			$settings['service_id'] = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';

			// Keep the stored token when the field is left masked/empty.
			$posted_token = isset( $_POST['secret_token'] ) ? sanitize_text_field( wp_unslash( $_POST['secret_token'] ) ) : '';
			if ( '' !== $posted_token ) {
				$settings['secret_token'] = $posted_token;
			}

			$settings['sender_name'] = isset( $_POST['sender_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sender_name'] ) ) : '';
			$settings['admin_phone'] = isset( $_POST['admin_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_phone'] ) ) : '';

			$settings['notify_new_user']           = ! empty( $_POST['notify_new_user'] ) ? 1 : 0;
			$settings['notify_new_comment']        = ! empty( $_POST['notify_new_comment'] ) ? 1 : 0;
			$settings['wc_notify_admin_new_order'] = ! empty( $_POST['wc_notify_admin_new_order'] ) ? 1 : 0;
			$settings['wc_notify_customer_status'] = ! empty( $_POST['wc_notify_customer_status'] ) ? 1 : 0;

			$settings['wc_templates'] = array();
			if ( isset( $_POST['wc_templates'] ) && is_array( $_POST['wc_templates'] ) ) {
				foreach ( wp_unslash( $_POST['wc_templates'] ) as $status => $tpl ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$settings['wc_templates'][ sanitize_key( $status ) ] = sanitize_textarea_field( $tpl );
				}
			}

			update_option( 'nimbasms_settings', $settings, false );

			add_settings_error( 'nimbasms', 'saved', __( 'Réglages enregistrés.', 'nimbasms' ), 'success' );
		}

		// Manual send.
		if ( isset( $_POST['nimbasms_manual_send'] ) && check_admin_referer( 'nimbasms_manual_send', 'nimbasms_nonce' ) ) {
			$to      = isset( $_POST['sms_to'] ) ? sanitize_text_field( wp_unslash( $_POST['sms_to'] ) ) : '';
			$message = isset( $_POST['sms_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sms_message'] ) ) : '';

			$numbers = array_map( 'trim', explode( ',', $to ) );
			$result  = nimbasms_send( $numbers, $message );

			if ( is_wp_error( $result ) ) {
				add_settings_error( 'nimbasms', 'send_error', $result->get_error_message(), 'error' );
			} else {
				add_settings_error( 'nimbasms', 'sent', __( 'SMS envoyé avec succès.', 'nimbasms' ), 'success' );
			}
		}
	}

	/**
	 * Render the admin page with tabs.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tabs = array(
			'settings' => __( 'Réglages', 'nimbasms' ),
			'send'     => __( 'Envoyer un SMS', 'nimbasms' ),
			'logs'     => __( 'Journal', 'nimbasms' ),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Nimba SMS', 'nimbasms' ); ?></h1>
			<?php settings_errors( 'nimbasms' ); ?>
			<nav class="nav-tab-wrapper">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<a class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>"
						href="<?php echo esc_url( admin_url( 'admin.php?page=nimbasms&tab=' . $slug ) ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<?php
			switch ( $tab ) {
				case 'send':
					self::render_send_tab();
					break;
				case 'logs':
					self::render_logs_tab();
					break;
				default:
					self::render_settings_tab();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Settings tab.
	 */
	private static function render_settings_tab() {
		$settings = get_option( 'nimbasms_settings', array() );
		$get      = function ( $key, $default = '' ) use ( $settings ) {
			return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
		};

		$balance      = null;
		$sendernames  = array();
		if ( NimbaSMS_API::is_configured() ) {
			$account = NimbaSMS_API::get_account();
			if ( ! is_wp_error( $account ) && isset( $account['balance'] ) ) {
				$balance = $account['balance'];
			}
			$sn = NimbaSMS_API::get_sendernames();
			if ( ! is_wp_error( $sn ) && isset( $sn['results'] ) && is_array( $sn['results'] ) ) {
				$sendernames = $sn['results'];
			}
		}
		?>
		<?php if ( null !== $balance ) : ?>
			<p style="font-size:14px;">
				<strong><?php esc_html_e( 'Solde du compte :', 'nimbasms' ); ?></strong>
				<?php echo esc_html( number_format_i18n( (float) $balance ) ); ?> <?php esc_html_e( 'SMS', 'nimbasms' ); ?>
			</p>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'nimbasms_settings', 'nimbasms_nonce' ); ?>
			<h2><?php esc_html_e( 'Identifiants API', 'nimbasms' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: developers portal URL. */
					esc_html__( 'Récupérez vos identifiants sur %s (rubrique Développeurs de votre compte Nimba SMS).', 'nimbasms' ),
					'<a href="https://developers.nimbasms.com" target="_blank" rel="noopener">developers.nimbasms.com</a>'
				);
				?>
			</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="service_id"><?php esc_html_e( 'SERVICE ID', 'nimbasms' ); ?></label></th>
					<td><input name="service_id" id="service_id" type="text" class="regular-text" value="<?php echo esc_attr( $get( 'service_id' ) ); ?>" autocomplete="off"></td>
				</tr>
				<tr>
					<th scope="row"><label for="secret_token"><?php esc_html_e( 'SECRET TOKEN', 'nimbasms' ); ?></label></th>
					<td>
						<input name="secret_token" id="secret_token" type="password" class="regular-text" value="" autocomplete="new-password"
							placeholder="<?php echo '' !== $get( 'secret_token' ) ? esc_attr__( '•••••••• (enregistré — laissez vide pour conserver)', 'nimbasms' ) : ''; ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sender_name"><?php esc_html_e( 'Nom d’expéditeur', 'nimbasms' ); ?></label></th>
					<td>
						<?php if ( ! empty( $sendernames ) ) : ?>
							<select name="sender_name" id="sender_name">
								<?php foreach ( $sendernames as $sn_item ) : ?>
									<?php $name = isset( $sn_item['name'] ) ? $sn_item['name'] : ''; ?>
									<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $get( 'sender_name' ), $name ); ?>><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<input name="sender_name" id="sender_name" type="text" class="regular-text" value="<?php echo esc_attr( $get( 'sender_name' ) ); ?>">
							<p class="description"><?php esc_html_e( 'Enregistrez d’abord vos identifiants pour charger la liste de vos noms d’expéditeur approuvés.', 'nimbasms' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="admin_phone"><?php esc_html_e( 'Numéro de l’administrateur', 'nimbasms' ); ?></label></th>
					<td>
						<input name="admin_phone" id="admin_phone" type="text" class="regular-text" value="<?php echo esc_attr( $get( 'admin_phone' ) ); ?>" placeholder="6XXXXXXXX">
						<p class="description"><?php esc_html_e( 'Reçoit les notifications SMS (nouvelles commandes, inscriptions…).', 'nimbasms' ); ?></p>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Notifications WordPress', 'nimbasms' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Événements', 'nimbasms' ); ?></th>
					<td>
						<label><input type="checkbox" name="notify_new_user" value="1" <?php checked( $get( 'notify_new_user' ) ); ?>> <?php esc_html_e( 'Nouvel utilisateur inscrit', 'nimbasms' ); ?></label><br>
						<label><input type="checkbox" name="notify_new_comment" value="1" <?php checked( $get( 'notify_new_comment' ) ); ?>> <?php esc_html_e( 'Nouveau commentaire', 'nimbasms' ); ?></label>
					</td>
				</tr>
			</table>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<h2><?php esc_html_e( 'WooCommerce', 'nimbasms' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Notifications', 'nimbasms' ); ?></th>
						<td>
							<label><input type="checkbox" name="wc_notify_admin_new_order" value="1" <?php checked( $get( 'wc_notify_admin_new_order' ) ); ?>> <?php esc_html_e( 'SMS à l’administrateur à chaque nouvelle commande', 'nimbasms' ); ?></label><br>
							<label><input type="checkbox" name="wc_notify_customer_status" value="1" <?php checked( $get( 'wc_notify_customer_status' ) ); ?>> <?php esc_html_e( 'SMS au client aux changements de statut de commande', 'nimbasms' ); ?></label>
						</td>
					</tr>
					<?php
					$wc_templates = wp_parse_args( (array) $get( 'wc_templates', array() ), NimbaSMS_WooCommerce::default_templates() );
					foreach ( NimbaSMS_WooCommerce::default_templates() as $status => $default_tpl ) :
						?>
						<tr>
							<th scope="row">
								<label for="wc_tpl_<?php echo esc_attr( $status ); ?>">
									<?php
									printf(
										/* translators: %s: WooCommerce order status. */
										esc_html__( 'Modèle « %s »', 'nimbasms' ),
										esc_html( function_exists( 'wc_get_order_status_name' ) ? wc_get_order_status_name( $status ) : $status )
									);
									?>
								</label>
							</th>
							<td>
								<textarea name="wc_templates[<?php echo esc_attr( $status ); ?>]" id="wc_tpl_<?php echo esc_attr( $status ); ?>" class="large-text" rows="2"><?php echo esc_textarea( $wc_templates[ $status ] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Variables : {site} {order_id} {total} {first_name} {last_name} {status}', 'nimbasms' ); ?></p>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>

			<p class="submit">
				<button type="submit" name="nimbasms_save_settings" value="1" class="button button-primary"><?php esc_html_e( 'Enregistrer les réglages', 'nimbasms' ); ?></button>
			</p>
		</form>
		<?php
	}

	/**
	 * Manual send tab.
	 */
	private static function render_send_tab() {
		?>
		<form method="post" style="max-width:640px;">
			<?php wp_nonce_field( 'nimbasms_manual_send', 'nimbasms_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="sms_to"><?php esc_html_e( 'Destinataire(s)', 'nimbasms' ); ?></label></th>
					<td>
						<input name="sms_to" id="sms_to" type="text" class="large-text" placeholder="624000000, 625000000" required>
						<p class="description"><?php esc_html_e( 'Un ou plusieurs numéros séparés par des virgules.', 'nimbasms' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sms_message"><?php esc_html_e( 'Message', 'nimbasms' ); ?></label></th>
					<td><textarea name="sms_message" id="sms_message" class="large-text" rows="5" maxlength="459" required></textarea></td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" name="nimbasms_manual_send" value="1" class="button button-primary"><?php esc_html_e( 'Envoyer', 'nimbasms' ); ?></button>
			</p>
		</form>
		<?php
	}

	/**
	 * Logs tab.
	 */
	private static function render_logs_tab() {
		$rows = NimbaSMS_Logger::recent( 100 );
		?>
		<table class="widefat striped" style="margin-top:16px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'nimbasms' ); ?></th>
					<th><?php esc_html_e( 'Destinataires', 'nimbasms' ); ?></th>
					<th><?php esc_html_e( 'Message', 'nimbasms' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'nimbasms' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr><td colspan="4"><?php esc_html_e( 'Aucun envoi pour le moment.', 'nimbasms' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row->created_at ); ?></td>
							<td><?php echo esc_html( $row->recipients ); ?></td>
							<td><?php echo esc_html( wp_trim_words( $row->message, 20 ) ); ?></td>
							<td>
								<?php if ( 'error' === $row->status ) : ?>
									<span style="color:#b32d2e;"><?php echo esc_html( $row->error ); ?></span>
								<?php else : ?>
									<span style="color:#008a20;"><?php echo esc_html( $row->status ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
	}
}
