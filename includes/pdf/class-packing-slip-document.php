<?php
/**
 * Packing slip document type.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Represents a PDF packing slip for a WooCommerce order.
 */
class Packing_Slip_Document extends Document {

	protected string $type  = 'packing_slip';
	protected string $label = '';

	public function __construct( \WC_Order $order ) {
		parent::__construct( $order );
		$this->label = __( 'Packing Slip', 'invoiceforge-for-woocommerce' );
	}

	public function get_default_template(): string {
		return 'default';
	}

	protected function build_template_data(): array {
		// Include only physical / shippable items.
		$shippable_items = array_filter(
			$this->order->get_items(),
			static function ( \WC_Order_Item_Product $item ) {
				$product = $item->get_product();
				return $product && ! $product->is_virtual();
			}
		);

		return array_merge(
			$this->get_common_data(),
			[
				'shippable_items'  => $shippable_items,
				'shipping_method'  => $this->order->get_shipping_method(),
				'tracking_number'  => $this->order->get_meta( '_tracking_number', true ),
			]
		);
	}
}
