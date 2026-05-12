<?php
/**
 * Plugin deactivation tasks.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge;

defined( 'ABSPATH' ) || exit;

/**
 * Runs on plugin deactivation.
 */
class InvoiceForge_Deactivator {

	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'invoiceforge_daily_cleanup' );
		flush_rewrite_rules();
	}
}
