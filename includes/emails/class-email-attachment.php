<?php
/**
 * Attaches PDF invoices to WooCommerce transactional emails.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\Emails;

defined( 'ABSPATH' ) || exit;

/**
 * Hooks into WooCommerce email generation to attach invoices.
 */
class Email_Attachment {

	/**
	 * Attaches the invoice PDF to the configured email types.
	 *
	 * @param string[]               $attachments Existing attachments.
	 * @param string                 $email_id    WC email class ID.
	 * @param \WC_Order|mixed        $order       Order object (may not always be an order).
	 * @return string[]
	 */
	public function attach_invoice( array $attachments, string $email_id, $order ): array {
		if ( ! $order instanceof \WC_Order ) {
			return $attachments;
		}

		$configured = (array) get_option( 'invoiceforge_attach_to_emails', [ 'customer_completed_order' ] );
		$configured = apply_filters( 'invoiceforge_email_attachments', $configured, $email_id, $order );

		if ( ! in_array( $email_id, $configured, true ) ) {
			return $attachments;
		}

		$invoice_path = $order->get_meta( '_invoiceforge_invoice_path', true );

		// Generate if not yet created.
		if ( ! $invoice_path || ! file_exists( $invoice_path ) ) {
			$generator    = new \InvoiceForge\PDF\PDF_Generator();
			$invoice_path = $generator->generate( new \InvoiceForge\PDF\Invoice_Document( $order ) );
		}

		if ( $invoice_path && file_exists( $invoice_path ) ) {
			$attachments[] = $invoice_path;
		}

		return $attachments;
	}
}
