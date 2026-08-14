<?php
/**
 * Plugin Name:       Nimba SMS
 * Plugin URI:        https://github.com/nimbasms/nimbasms-wordpress
 * Description:       Connectez WordPress à Nimba SMS, la plateforme de communication professionnelle des entreprises (SMS, WhatsApp, e-mail) : notifications WooCommerce, envois SMS et WhatsApp (templates Meta), alertes et journal.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Nimba SMS
 * Author URI:        https://www.nimbasms.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nimbasms
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NIMBASMS_VERSION', '1.0.0' );
define( 'NIMBASMS_PLUGIN_FILE', __FILE__ );
define( 'NIMBASMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NIMBASMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NIMBASMS_API_BASE', 'https://api.nimbasms.com/v1' );

require_once NIMBASMS_PLUGIN_DIR . 'includes/class-nimbasms-api.php';
require_once NIMBASMS_PLUGIN_DIR . 'includes/class-nimbasms-logger.php';
require_once NIMBASMS_PLUGIN_DIR . 'includes/class-nimbasms-notifications.php';
require_once NIMBASMS_PLUGIN_DIR . 'includes/functions.php';

if ( is_admin() ) {
	require_once NIMBASMS_PLUGIN_DIR . 'admin/class-nimbasms-admin.php';
}

/**
 * Main plugin bootstrap.
 */
final class NimbaSMS_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var NimbaSMS_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return NimbaSMS_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'maybe_load_woocommerce' ), 20 );

		NimbaSMS_Notifications::init();

		if ( is_admin() ) {
			NimbaSMS_Admin::init();
		}
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'nimbasms', false, dirname( plugin_basename( NIMBASMS_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Load WooCommerce integration when WooCommerce is active.
	 */
	public function maybe_load_woocommerce() {
		if ( class_exists( 'WooCommerce' ) ) {
			require_once NIMBASMS_PLUGIN_DIR . 'includes/class-nimbasms-woocommerce.php';
			NimbaSMS_WooCommerce::init();
		}
	}
}

/**
 * Activation: create the logs table.
 */
function nimbasms_activate() {
	NimbaSMS_Logger::create_table();
}
register_activation_hook( __FILE__, 'nimbasms_activate' );

NimbaSMS_Plugin::instance();
