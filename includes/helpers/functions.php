<?php
/**
 * Global helper functions.
 *
 * @package InvoiceForge
 */

defined( 'ABSPATH' ) || exit;

/**
 * Writes content to an absolute file path using the WordPress Filesystem API.
 *
 * Use this instead of file_put_contents() for all plugin-owned file writes
 * so Plugin Check doesn't flag direct filesystem operations.
 *
 * @param string $absolute_path Absolute destination path.
 * @param string $content       Content to write.
 * @return bool True on success.
 */
function invoiceforge_fs_write( string $absolute_path, string $content ): bool {
	global $wp_filesystem;

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	WP_Filesystem();

	if ( ! $wp_filesystem ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- fallback when WP_Filesystem unavailable (e.g. Plugin Check activation sandbox).
		return false !== file_put_contents( $absolute_path, $content );
	}

	return (bool) $wp_filesystem->put_contents( $absolute_path, $content, FS_CHMOD_FILE );
}

/**
 * Deletes a file using the WordPress Filesystem API.
 *
 * Use this instead of unlink() for all plugin-owned file deletions.
 *
 * @param string $absolute_path Absolute path of the file to delete.
 * @return bool True on success or if file does not exist.
 */
function invoiceforge_fs_delete( string $absolute_path ): bool {
	global $wp_filesystem;

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	WP_Filesystem();

	if ( ! $wp_filesystem->exists( $absolute_path ) ) {
		return true;
	}

	return (bool) $wp_filesystem->delete( $absolute_path );
}

/**
 * Reads a file using the WordPress Filesystem API.
 *
 * Use this instead of file_get_contents() for plugin-owned file reads.
 *
 * @param string $absolute_path Absolute path of the file to read.
 * @return string|false File contents or false on failure.
 */
function invoiceforge_fs_read( string $absolute_path ) {
	global $wp_filesystem;

	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	WP_Filesystem();

	return $wp_filesystem->get_contents( $absolute_path );
}

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
