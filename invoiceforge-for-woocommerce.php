<?php
/**
 * Plugin Name:       InvoiceForge for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/invoiceforge-for-woocommerce/
 * Description:       Beautiful PDF invoices and packing slips for WooCommerce. Auto-generate, attach to emails, and let customers download from My Account.
 * Version:           0.1.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Arun Rajiah
 * Author URI:        https://arunrajiah.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       invoiceforge-for-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   9.9
 *
 * @package InvoiceForge
 */

defined( 'ABSPATH' ) || exit;

define( 'INVOICEFORGE_VERSION', '0.1.0' );
define( 'INVOICEFORGE_PLUGIN_FILE', __FILE__ );
define( 'INVOICEFORGE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'INVOICEFORGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'INVOICEFORGE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare HPOS (High-Performance Order Storage) compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Check that WooCommerce is active before loading the plugin.
 */
function invoiceforge_check_dependencies(): bool {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'invoiceforge_missing_wc_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function invoiceforge_missing_wc_notice(): void {
	echo '<div class="notice notice-error"><p>' .
		esc_html__( 'InvoiceForge for WooCommerce requires WooCommerce to be installed and active.', 'invoiceforge-for-woocommerce' ) .
		'</p></div>';
}

/**
 * Load the Composer autoloader and initialise the plugin.
 */
function invoiceforge_init(): void {
	if ( ! invoiceforge_check_dependencies() ) {
		return;
	}

	$autoload = INVOICEFORGE_PLUGIN_DIR . 'vendor/autoload.php';
	if ( file_exists( $autoload ) ) {
		require_once $autoload;
	}

	require_once INVOICEFORGE_PLUGIN_DIR . 'includes/class-invoiceforge.php';

	$plugin = InvoiceForge\InvoiceForge::instance();
	$plugin->run();
}
add_action( 'plugins_loaded', 'invoiceforge_init', 10 );

/**
 * Activation hook.
 */
function invoiceforge_activate(): void {
	require_once INVOICEFORGE_PLUGIN_DIR . 'includes/helpers/functions.php';
	require_once INVOICEFORGE_PLUGIN_DIR . 'includes/class-invoiceforge-activator.php';
	InvoiceForge\InvoiceForge_Activator::activate();
}
register_activation_hook( __FILE__, 'invoiceforge_activate' );

/**
 * Deactivation hook.
 */
function invoiceforge_deactivate(): void {
	require_once INVOICEFORGE_PLUGIN_DIR . 'includes/class-invoiceforge-deactivator.php';
	InvoiceForge\InvoiceForge_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'invoiceforge_deactivate' );
