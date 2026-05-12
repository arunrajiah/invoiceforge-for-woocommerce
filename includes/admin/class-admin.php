<?php
/**
 * Admin bootstrap — enqueues assets and registers AJAX handlers.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles admin-side asset loading and AJAX download handling.
 */
class Admin {

	public function enqueue_styles( string $hook ): void {
		if ( ! $this->is_invoiceforge_page( $hook ) ) {
			return;
		}
		wp_enqueue_style(
			'invoiceforge-admin',
			INVOICEFORGE_PLUGIN_URL . 'assets/css/admin.css',
			[],
			INVOICEFORGE_VERSION
		);
	}

	public function enqueue_scripts( string $hook ): void {
		if ( ! $this->is_invoiceforge_page( $hook ) ) {
			return;
		}
		wp_enqueue_script(
			'invoiceforge-admin',
			INVOICEFORGE_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			INVOICEFORGE_VERSION,
			true
		);
		wp_localize_script(
			'invoiceforge-admin',
			'invoiceforgeAdmin',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'invoiceforge_admin' ),
			]
		);
	}

	private function is_invoiceforge_page( string $hook ): bool {
		$pages = [
			'edit.php',
			'post.php',
			'post-new.php',
			'woocommerce_page_wc-orders',
			'woocommerce_page_invoiceforge-settings',
		];
		return in_array( $hook, $pages, true ) || str_contains( $hook, 'invoiceforge' );
	}
}
