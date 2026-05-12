<?php
/**
 * Uninstall routine — only runs when the plugin is deleted via the WP admin.
 *
 * @package InvoiceForge
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( 'yes' !== get_option( 'invoiceforge_remove_data_on_uninstall', 'no' ) ) {
	return;
}

// Remove all plugin options.
$options = [
	'invoiceforge_shop_name',
	'invoiceforge_shop_address',
	'invoiceforge_shop_vat_number',
	'invoiceforge_tax_label',
	'invoiceforge_footer_text',
	'invoiceforge_logo_url',
	'invoiceforge_invoice_prefix',
	'invoiceforge_invoice_suffix',
	'invoiceforge_generate_on_statuses',
	'invoiceforge_attach_to_emails',
	'invoiceforge_generate_packing_slip',
	'invoiceforge_remove_data_on_uninstall',
];
foreach ( $options as $option ) {
	delete_option( $option );
}

// Remove per-year sequence counters.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'invoiceforge_invoice_sequence_%'
	)
);

// Remove order meta.
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
		'_invoiceforge_%'
	)
);

// Remove PDF files via WP_Filesystem.
$upload_dir = wp_upload_dir();
$pdf_dir    = trailingslashit( $upload_dir['basedir'] ) . 'invoiceforge';

if ( is_dir( $pdf_dir ) ) {
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	global $wp_filesystem;
	WP_Filesystem();

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $pdf_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ( $files as $file ) {
		$real = $file->getRealPath();
		if ( $file->isDir() ) {
			$wp_filesystem->rmdir( $real );
		} else {
			$wp_filesystem->delete( $real );
		}
	}
	$wp_filesystem->rmdir( $pdf_dir );
}
