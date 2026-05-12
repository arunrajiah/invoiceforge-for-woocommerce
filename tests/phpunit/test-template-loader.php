<?php
/**
 * Tests for the template loader.
 *
 * @package InvoiceForge
 */

use InvoiceForge\PDF\Template_Loader;

/**
 * Template loader test case.
 */
class Test_Template_Loader extends WP_UnitTestCase {

	public function test_locates_plugin_default_template(): void {
		$loader = new Template_Loader();
		$path   = $loader->locate_template( 'invoice', 'default' );

		$this->assertNotNull( $path );
		$this->assertFileExists( $path );
		$this->assertStringContainsString( 'invoice', $path );
	}

	public function test_locate_returns_null_for_nonexistent_template(): void {
		$loader = new Template_Loader();
		$path   = $loader->locate_template( 'invoice', 'does-not-exist' );

		$this->assertNull( $path );
	}

	public function test_filter_can_override_template_path(): void {
		add_filter(
			'invoiceforge_template_path',
			static function ( $path, $document_type, $template_name ) {
				return '/tmp/custom-template.php';
			},
			10,
			3
		);

		$loader = new Template_Loader();
		$path   = $loader->locate_template( 'invoice', 'default' );

		remove_all_filters( 'invoiceforge_template_path' );

		// The filtered path is returned even if the file doesn't exist
		// (the generator checks file_exists separately).
		$this->assertEquals( '/tmp/custom-template.php', $path );
	}
}
