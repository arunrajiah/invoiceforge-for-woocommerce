<?php
/**
 * Bulk actions on the WooCommerce orders list.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a "Download Invoices as ZIP" bulk action to the WooCommerce orders list.
 * Free tier: limited to the current page (max ~20 orders).
 * Pro tier: removes the limit via the invoiceforge_bulk_export_limit filter.
 */
class Bulk_Actions {

	/**
	 * Registers the bulk action.
	 *
	 * @param array<string, string> $bulk_actions Existing bulk actions.
	 * @return array<string, string>
	 */
	public function register_bulk_actions( array $bulk_actions ): array {
		$bulk_actions['invoiceforge_download_invoices'] = __( 'Download Invoices (ZIP)', 'invoiceforge-for-woocommerce' );
		return $bulk_actions;
	}

	/**
	 * Processes the bulk action.
	 *
	 * @param string                 $redirect_to Redirect URL.
	 * @param string                 $action      Action slug.
	 * @param array<int, int|string> $post_ids    Selected order IDs.
	 * @return string Redirect URL.
	 */
	public function handle_bulk_actions( string $redirect_to, string $action, array $post_ids ): string {
		if ( 'invoiceforge_download_invoices' !== $action ) {
			return $redirect_to;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $redirect_to;
		}

		check_admin_referer( 'bulk-orders' );

		$limit    = (int) apply_filters( 'invoiceforge_bulk_export_limit', 20 );
		$order_ids = array_slice( array_map( 'absint', $post_ids ), 0, $limit );

		if ( empty( $order_ids ) ) {
			return $redirect_to;
		}

		$zip_path = $this->build_zip( $order_ids );

		if ( $zip_path && file_exists( $zip_path ) ) {
			// Stream the ZIP and exit.
			$filename = 'invoices-' . gmdate( 'Y-m-d' ) . '.zip';
			header( 'Content-Type: application/zip' );
			header( 'Content-Disposition: attachment; filename="' . esc_attr( $filename ) . '"' );
			header( 'Content-Length: ' . filesize( $zip_path ) );
			readfile( $zip_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			unlink( $zip_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			exit;
		}

		return add_query_arg( 'invoiceforge_bulk_error', '1', $redirect_to );
	}

	/**
	 * Creates a temporary ZIP file containing the invoices for the given order IDs.
	 *
	 * @param int[] $order_ids Order IDs.
	 * @return string|null Absolute path to the ZIP file, or null on failure.
	 */
	private function build_zip( array $order_ids ): ?string {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return null;
		}

		$generator = new \InvoiceForge\PDF\PDF_Generator();
		$zip_path  = get_temp_dir() . 'invoiceforge-bulk-' . uniqid() . '.zip';

		$zip = new \ZipArchive();
		if ( true !== $zip->open( $zip_path, \ZipArchive::CREATE ) ) {
			return null;
		}

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}

			$document  = new \InvoiceForge\PDF\Invoice_Document( $order );
			$file_path = $generator->get_file_path( $document );

			if ( ! file_exists( $file_path ) ) {
				$file_path = $generator->generate( $document );
			}

			if ( $file_path && file_exists( $file_path ) ) {
				$zip->addFile( $file_path, 'invoice-' . $order->get_order_number() . '.pdf' );
			}
		}

		$zip->close();

		return file_exists( $zip_path ) ? $zip_path : null;
	}
}
