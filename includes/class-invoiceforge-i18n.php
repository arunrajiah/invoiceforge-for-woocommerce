<?php
/**
 * Internationalisation loader.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge;

defined( 'ABSPATH' ) || exit;

/**
 * Loads the plugin textdomain.
 */
class InvoiceForge_I18n {

	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'invoiceforge-for-woocommerce',
			false,
			dirname( INVOICEFORGE_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
