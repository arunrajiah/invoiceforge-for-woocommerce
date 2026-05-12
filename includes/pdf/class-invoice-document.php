<?php
/**
 * Invoice document type.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Represents a PDF invoice for a WooCommerce order.
 */
class Invoice_Document extends Document {

	protected string $type  = 'invoice';
	protected string $label = '';

	public function __construct( \WC_Order $order ) {
		parent::__construct( $order );
		$this->label = __( 'Invoice', 'invoiceforge-for-woocommerce' );

		// Load persisted number from order meta.
		$saved = $order->get_meta( '_invoiceforge_invoice_number', true );
		if ( $saved ) {
			$this->document_number = $saved;
		}
	}

	public function get_default_template(): string {
		return 'default';
	}

	protected function build_template_data(): array {
		return array_merge(
			$this->get_common_data(),
			[
				'invoice_number' => $this->document_number,
				'invoice_date'   => $this->order->get_date_created(),
				'due_date'       => null, // Pro B2B field.
			]
		);
	}
}
