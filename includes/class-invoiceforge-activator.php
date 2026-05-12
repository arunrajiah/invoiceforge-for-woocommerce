<?php
/**
 * Plugin activation tasks.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge;

defined( 'ABSPATH' ) || exit;

/**
 * Runs on plugin activation.
 */
class InvoiceForge_Activator {

	public static function activate(): void {
		self::create_upload_directory();
		self::set_default_options();
		flush_rewrite_rules();
	}

	private static function create_upload_directory(): void {
		$upload_dir = wp_upload_dir();
		$base       = trailingslashit( $upload_dir['basedir'] ) . 'invoiceforge';

		if ( ! file_exists( $base ) ) {
			wp_mkdir_p( $base );
			// Protect the directory from direct access.
			file_put_contents( $base . '/.htaccess', 'deny from all' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			file_put_contents( $base . '/index.php', '<?php // Silence is golden.' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
	}

	private static function set_default_options(): void {
		$defaults = [
			'invoiceforge_generate_on_statuses' => [ 'completed' ],
			'invoiceforge_attach_to_emails'     => [ 'customer_completed_order' ],
			'invoiceforge_invoice_prefix'       => 'INV',
			'invoiceforge_invoice_suffix'       => '',
			'invoiceforge_generate_packing_slip' => 'yes',
			'invoiceforge_remove_data_on_uninstall' => 'no',
			'invoiceforge_shop_name'            => get_bloginfo( 'name' ),
			'invoiceforge_shop_address'         => '',
			'invoiceforge_shop_vat_number'      => '',
			'invoiceforge_tax_label'            => __( 'VAT', 'invoiceforge-for-woocommerce' ),
			'invoiceforge_footer_text'          => '',
		];

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				update_option( $key, $value );
			}
		}
	}
}
