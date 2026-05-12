<?php
/**
 * Minimal WooCommerce class stubs for the PHPUnit test environment.
 *
 * @package InvoiceForge
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	class WooCommerce {} // phpcs:ignore Generic.Files.OneClassPerFile.MultipleFound -- stubs only.
}

if ( ! class_exists( 'WC_Order' ) ) {
	class WC_Order { // phpcs:ignore Generic.Files.OneClassPerFile.MultipleFound -- stubs only.
		public function get_id(): int { return 0; }
		public function get_order_number(): string { return ''; }
		public function get_meta( string $key, bool $single = true ) { return ''; }
		public function update_meta_data( string $key, $value ): void {}
		public function save_meta_data(): void {}
		public function get_date_created(): ?\WC_DateTime { return null; }
		public function get_total(): string { return '0.00'; }
		public function get_total_tax(): string { return '0.00'; }
		public function get_currency(): string { return 'USD'; }
		public function get_customer_id(): int { return 0; }
		public function get_billing_email(): string { return ''; }
	}
}

if ( ! class_exists( 'WC_DateTime' ) ) {
	class WC_DateTime extends DateTime { // phpcs:ignore Generic.Files.OneClassPerFile.MultipleFound -- stubs only.
	}
}

if ( ! function_exists( 'wc_get_order' ) ) {
	function wc_get_order( $order ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- WC stub.
		return ( $order instanceof WC_Order ) ? $order : null;
	}
}

if ( ! function_exists( 'wc_price' ) ) {
	function wc_price( $price ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- WC stub.
		return number_format( (float) $price, 2 );
	}
}

if ( ! function_exists( 'wc_get_payment_gateway_by_order' ) ) {
	function wc_get_payment_gateway_by_order( $order ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- WC stub.
		return false;
	}
}
