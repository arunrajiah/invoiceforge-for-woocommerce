<?php
/**
 * PHPUnit bootstrap for InvoiceForge Free.
 *
 * @package InvoiceForge
 */

// Composer autoloader (includes mPDF).
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: sys_get_temp_dir() . '/wordpress-tests-lib';

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php. Run bin/install-wp-tests.sh first.\n"; // phpcs:ignore
	exit( 1 );
}

// Give access to tests_add_filter().
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin.
 */
function _manually_load_plugin(): void {
	// Stub WooCommerce as it won't be present in the test environment.
	if ( ! class_exists( 'WooCommerce' ) ) {
		require_once __DIR__ . '/stubs/class-woocommerce-stub.php';
	}
	require_once dirname( __DIR__ ) . '/invoiceforge-for-woocommerce.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';
