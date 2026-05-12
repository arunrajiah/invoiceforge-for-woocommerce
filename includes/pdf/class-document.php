<?php
/**
 * Abstract base for all document types.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\PDF;

defined( 'ABSPATH' ) || exit;

/**
 * All PDF document types (invoice, packing slip, credit note, etc.) extend this class.
 */
abstract class Document {

	/** @var \WC_Order */
	protected \WC_Order $order;

	/** @var string Unique slug for this document type. */
	protected string $type = '';

	/** @var string Human-readable label. */
	protected string $label = '';

	/** @var string|null Assigned document number. */
	protected ?string $document_number = null;

	public function __construct( \WC_Order $order ) {
		$this->order = $order;
	}

	/** Returns the document type slug. */
	public function get_type(): string {
		return $this->type;
	}

	/** Returns the human-readable label. */
	public function get_label(): string {
		return $this->label;
	}

	/** Returns the WC_Order associated with this document. */
	public function get_order(): \WC_Order {
		return $this->order;
	}

	/** Returns the document number (may be null before generation). */
	public function get_document_number(): ?string {
		return $this->document_number;
	}

	/** Sets the document number. */
	public function set_document_number( string $number ): void {
		$this->document_number = $number;
	}

	/**
	 * Returns the data array that will be passed into the PDF template.
	 *
	 * @return array<string, mixed>
	 */
	public function get_template_data(): array {
		$data = $this->build_template_data();
		return apply_filters( 'invoiceforge_template_data', $data, $this->order, $this->type );
	}

	/**
	 * Build the raw template data (override per document type).
	 *
	 * @return array<string, mixed>
	 */
	abstract protected function build_template_data(): array;

	/**
	 * Returns the default template name for this document type.
	 */
	abstract public function get_default_template(): string;

	/**
	 * Returns the PDF filename (filterable).
	 */
	public function get_filename(): string {
		$filename = sprintf(
			'%s-%s.pdf',
			$this->type,
			$this->order->get_order_number()
		);
		return apply_filters( 'invoiceforge_pdf_filename', $filename, $this->order, $this->type );
	}

	/**
	 * Returns common data available to all document templates.
	 *
	 * @return array<string, mixed>
	 */
	protected function get_common_data(): array {
		$order        = $this->order;
		$billing      = $order->get_address( 'billing' );
		$shipping     = $order->get_address( 'shipping' );

		return [
			// Document meta.
			'document'         => $this,
			'document_type'    => $this->type,
			'document_label'   => $this->label,
			'document_number'  => $this->document_number,

			// Order.
			'order'            => $order,
			'order_number'     => $order->get_order_number(),
			'order_date'       => $order->get_date_created(),
			'order_items'      => $order->get_items(),
			'order_subtotal'   => $order->get_subtotal(),
			'order_shipping'   => $order->get_shipping_total(),
			'order_discount'   => $order->get_discount_total(),
			'order_tax'        => $order->get_total_tax(),
			'order_total'      => $order->get_total(),
			'payment_method'   => $order->get_payment_method_title(),
			'currency'         => get_woocommerce_currency_symbol( $order->get_currency() ),

			// Addresses.
			'billing_address'  => $billing,
			'shipping_address' => $shipping,

			// Shop.
			'shop_name'        => get_option( 'invoiceforge_shop_name', get_bloginfo( 'name' ) ),
			'shop_address'     => get_option( 'invoiceforge_shop_address', '' ),
			'shop_vat_number'  => get_option( 'invoiceforge_shop_vat_number', '' ),
			'shop_logo_url'    => get_option( 'invoiceforge_logo_url', '' ),
			'tax_label'        => get_option( 'invoiceforge_tax_label', __( 'VAT', 'invoiceforge-for-woocommerce' ) ),
			'footer_text'      => get_option( 'invoiceforge_footer_text', '' ),

			// Utils.
			'plugin_version'   => INVOICEFORGE_VERSION,
			'generated_at'     => current_time( 'mysql' ),
		];
	}
}
