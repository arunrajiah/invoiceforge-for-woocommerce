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
		// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- kept for backwards-compat with pre-4.6 installs; harmless on 4.6+.
		load_plugin_textdomain(
			'invoiceforge-for-woocommerce',
			false,
			dirname( INVOICEFORGE_PLUGIN_BASENAME ) . '/languages/'
		);
	}
}
