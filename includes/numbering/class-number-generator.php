<?php
/**
 * Generates formatted invoice numbers.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\Numbering;

defined( 'ABSPATH' ) || exit;

/**
 * Creates sequential invoice numbers in the format:
 *   {PREFIX}-{YEAR}-{SEQUENCE}{SUFFIX}
 * e.g. INV-2026-00042
 *
 * All parts are configurable via settings. The formatted number is also
 * passed through the invoiceforge_number_format filter so Pro can override
 * the entire number without touching this class.
 */
class Number_Generator {

	/** @var Number_Store */
	private Number_Store $store;

	public function __construct( ?Number_Store $store = null ) {
		$this->store = $store ?? new Number_Store();
	}

	/**
	 * Returns the existing invoice number for an order, or generates a new one.
	 *
	 * @param \WC_Order $order WooCommerce order.
	 * @return string Formatted invoice number.
	 */
	public function get_or_create( \WC_Order $order ): string {
		$existing = $order->get_meta( '_invoiceforge_invoice_number', true );
		if ( $existing ) {
			return $existing;
		}

		$number = $this->generate( $order );
		$order->update_meta_data( '_invoiceforge_invoice_number', $number );
		$order->update_meta_data( '_invoiceforge_invoice_number_raw', $this->store->peek( (int) gmdate( 'Y' ) ) );
		$order->save_meta_data();

		return $number;
	}

	/**
	 * Generates a fresh formatted number (does NOT save to order meta).
	 *
	 * @param \WC_Order $order WooCommerce order.
	 * @return string
	 */
	public function generate( \WC_Order $order ): string {
		$year     = (int) gmdate( 'Y' );
		$sequence = $this->store->get_next( $year );

		$prefix   = get_option( 'invoiceforge_invoice_prefix', 'INV' );
		$suffix   = get_option( 'invoiceforge_invoice_suffix', '' );
		$padding  = (int) apply_filters( 'invoiceforge_sequence_padding', 5 );

		$parts = array_filter( [ $prefix, $year, str_pad( (string) $sequence, $padding, '0', STR_PAD_LEFT ) ] );
		$base  = implode( '-', $parts ) . $suffix;

		return apply_filters( 'invoiceforge_number_format', $base, 'invoice', $order );
	}
}
