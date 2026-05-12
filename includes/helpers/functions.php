<?php
/**
 * Global helper functions.
 *
 * @package InvoiceForge
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the absolute file path for an order's invoice PDF, or null if not yet generated.
 *
 * @param int|\WC_Order $order Order ID or object.
 * @return string|null
 */
function invoiceforge_get_invoice_path( $order ): ?string {
	$order = wc_get_order( $order );
	if ( ! $order ) {
		return null;
	}
	return $order->get_meta( '_invoiceforge_invoice_path', true ) ?: null;
}

/**
 * Returns the absolute file path for an order's packing-slip PDF, or null if not yet generated.
 *
 * @param int|\WC_Order $order Order ID or object.
 * @return string|null
 */
function invoiceforge_get_packing_slip_path( $order ): ?string {
	$order = wc_get_order( $order );
	if ( ! $order ) {
		return null;
	}
	return $order->get_meta( '_invoiceforge_packing_slip_path', true ) ?: null;
}

/**
 * Returns the invoice number for an order, or null if not yet assigned.
 *
 * @param int|\WC_Order $order Order ID or object.
 * @return string|null
 */
function invoiceforge_get_invoice_number( $order ): ?string {
	$order = wc_get_order( $order );
	if ( ! $order ) {
		return null;
	}
	return $order->get_meta( '_invoiceforge_invoice_number', true ) ?: null;
}

/**
 * Returns the storage base directory for InvoiceForge PDFs.
 *
 * @return string Absolute path with trailing slash.
 */
function invoiceforge_get_storage_dir(): string {
	$upload_dir = wp_upload_dir();
	return trailingslashit( $upload_dir['basedir'] ) . 'invoiceforge/';
}

/**
 * Returns the URL base for InvoiceForge PDFs.
 *
 * @return string URL with trailing slash.
 */
function invoiceforge_get_storage_url(): string {
	$upload_dir = wp_upload_dir();
	return trailingslashit( $upload_dir['baseurl'] ) . 'invoiceforge/';
}

/**
 * Checks whether the current user can view or download an invoice for a given order.
 *
 * @param int|\WC_Order $order Order ID or object.
 * @return bool
 */
function invoiceforge_current_user_can_view( $order ): bool {
	$order = wc_get_order( $order );
	if ( ! $order ) {
		return false;
	}
	if ( current_user_can( 'manage_woocommerce' ) ) {
		return true;
	}
	return (int) get_current_user_id() === (int) $order->get_customer_id();
}

/**
 * Returns the list of document types available (filterable by Pro).
 *
 * @return array<string, string> Slug => label.
 */
function invoiceforge_get_document_types(): array {
	$types = [
		'invoice'      => __( 'Invoice', 'invoiceforge-for-woocommerce' ),
		'packing_slip' => __( 'Packing Slip', 'invoiceforge-for-woocommerce' ),
	];
	return apply_filters( 'invoiceforge_document_types', $types );
}
