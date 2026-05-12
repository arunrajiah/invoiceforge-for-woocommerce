<?php
/**
 * Documents every public hook provided by InvoiceForge.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\Extensibility;

defined( 'ABSPATH' ) || exit;

/**
 * Hook registry — acts as living documentation for all extensibility points.
 *
 * Pro plugin and third-party code should only interact with InvoiceForge
 * through the hooks listed here.
 *
 * -------------------------------------------------------------------------
 * ACTIONS
 * -------------------------------------------------------------------------
 *
 * invoiceforge_loaded
 *   Fires after the plugin has fully initialised.
 *   No parameters.
 *
 * invoiceforge_before_pdf_generated( WC_Order $order, string $document_type )
 *   Fires just before the PDF file is written to disk.
 *   $document_type: 'invoice' | 'packing_slip' | 'credit_note' | 'proforma' | 'delivery_note'
 *
 * invoiceforge_pdf_generated( WC_Order $order, string $document_type, string $file_path )
 *   Fires after the PDF has been successfully written.
 *
 * invoiceforge_pro_loaded
 *   Fires after the Pro plugin has fully initialised (defined by Pro plugin).
 *
 * -------------------------------------------------------------------------
 * FILTERS
 * -------------------------------------------------------------------------
 *
 * invoiceforge_pdf_filename( string $filename, WC_Order $order, string $document_type ) : string
 *   Override the filename of the generated PDF.
 *
 * invoiceforge_pdf_storage_path( string $path, WC_Order $order, string $document_type ) : string
 *   Override the directory where PDFs are stored.
 *
 * invoiceforge_document_types( array $types ) : array
 *   Extend the list of document types. Pro adds credit_note, proforma, delivery_note.
 *   $types: associative array of slug => label.
 *
 * invoiceforge_template_path( string $path, string $document_type, string $template_name ) : string
 *   Override which PHP template file is loaded for rendering.
 *
 * invoiceforge_template_data( array $data, WC_Order $order, string $document_type ) : array
 *   Add or modify the variables passed to the PDF template.
 *
 * invoiceforge_number_format( string $number, string $document_type, WC_Order $order ) : string
 *   Override the formatted invoice/document number.
 *
 * invoiceforge_email_attachments( array $attachments, string $email_id, WC_Order $order ) : array
 *   Add or remove PDF attachments from WooCommerce transactional emails.
 *
 * invoiceforge_admin_order_actions( array $actions, WC_Order $order ) : array
 *   Add or remove actions shown on the admin order list row.
 *
 * invoiceforge_my_account_actions( array $actions, WC_Order $order ) : array
 *   Add or remove actions shown on the My Account → Orders table.
 *
 * invoiceforge_bulk_export_limit( int $limit ) : int
 *   Maximum number of orders included in a bulk ZIP export.
 *   Free: 20 (one page). Pro filters to PHP_INT_MAX.
 *
 * -------------------------------------------------------------------------
 */
class Hook_Registry {

	/**
	 * Returns all registered hook names grouped by type.
	 *
	 * @return array{actions: string[], filters: string[]}
	 */
	public static function get_all(): array {
		return [
			'actions' => [
				'invoiceforge_loaded',
				'invoiceforge_before_pdf_generated',
				'invoiceforge_pdf_generated',
				'invoiceforge_pro_loaded',
			],
			'filters' => [
				'invoiceforge_pdf_filename',
				'invoiceforge_pdf_storage_path',
				'invoiceforge_document_types',
				'invoiceforge_template_path',
				'invoiceforge_template_data',
				'invoiceforge_number_format',
				'invoiceforge_email_attachments',
				'invoiceforge_admin_order_actions',
				'invoiceforge_my_account_actions',
				'invoiceforge_bulk_export_limit',
			],
		];
	}
}
