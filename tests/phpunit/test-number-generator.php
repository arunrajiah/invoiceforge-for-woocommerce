<?php
/**
 * Tests for the invoice number generator.
 *
 * @package InvoiceForge
 */

use InvoiceForge\Numbering\Number_Generator;
use InvoiceForge\Numbering\Number_Store;

/**
 * Number generator test case.
 */
class Test_Number_Generator extends WP_UnitTestCase {

	public function test_generates_sequential_numbers(): void {
		$store = new Number_Store();
		$year  = (int) gmdate( 'Y' );
		$store->reset( $year, 0 );

		$generator = new Number_Generator( $store );

		$order1 = $this->make_mock_order( 1001 );
		$order2 = $this->make_mock_order( 1002 );

		$number1 = $generator->generate( $order1 );
		$number2 = $generator->generate( $order2 );

		$this->assertNotEquals( $number1, $number2 );
		$this->assertStringContainsString( (string) $year, $number1 );
		$this->assertStringContainsString( '00001', $number1 );
		$this->assertStringContainsString( '00002', $number2 );
	}

	public function test_applies_prefix_from_options(): void {
		update_option( 'invoiceforge_invoice_prefix', 'TEST' );
		update_option( 'invoiceforge_invoice_suffix', '' );

		$store = new Number_Store();
		$store->reset( (int) gmdate( 'Y' ), 99 );

		$generator = new Number_Generator( $store );
		$number    = $generator->generate( $this->make_mock_order( 2000 ) );

		$this->assertStringStartsWith( 'TEST-', $number );
		delete_option( 'invoiceforge_invoice_prefix' );
	}

	public function test_get_or_create_returns_same_number_on_second_call(): void {
		$store = new Number_Store();
		$store->reset( (int) gmdate( 'Y' ), 0 );

		$generator = new Number_Generator( $store );
		$order     = $this->make_mock_order( 3000 );

		$first  = $generator->get_or_create( $order );
		$second = $generator->get_or_create( $order );

		$this->assertEquals( $first, $second );
	}

	/**
	 * Creates a WC_Order stub with sufficient functionality for tests.
	 *
	 * @param int $order_id Order ID.
	 * @return \WC_Order|\PHPUnit\Framework\MockObject\MockObject
	 */
	private function make_mock_order( int $order_id ) {
		$order = $this->createMock( \WC_Order::class );
		$meta  = [];

		$order->method( 'get_id' )->willReturn( $order_id );
		$order->method( 'get_order_number' )->willReturn( (string) $order_id );
		$order->method( 'get_meta' )->willReturnCallback( static fn( $key ) => $meta[ $key ] ?? '' );
		$order->method( 'update_meta_data' )->willReturnCallback( static function ( $key, $value ) use ( &$meta ) {
			$meta[ $key ] = $value;
		} );
		$order->method( 'save_meta_data' )->willReturn( null );

		return $order;
	}
}
