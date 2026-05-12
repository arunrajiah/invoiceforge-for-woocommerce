<?php
/**
 * Generates PDF files from document objects using mPDF.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Handles PDF generation, storage, and serving.
 */
class PDF_Generator {

	/** @var Template_Loader */
	private Template_Loader $template_loader;

	public function __construct() {
		$this->template_loader = new Template_Loader();
	}

	/**
	 * Generates a PDF for the given document and saves it to disk.
	 *
	 * @param Document $document Document to render.
	 * @return string|null Absolute file path on success, null on failure.
	 */
	public function generate( Document $document ): ?string {
		$order = $document->get_order();

		// Assign a document number if the document doesn't have one yet.
		if ( $document instanceof Invoice_Document && ! $document->get_document_number() ) {
			$numbering = new \InvoiceForge\Numbering\Number_Generator();
			$number    = $numbering->get_or_create( $order );
			$document->set_document_number( $number );
		}

		do_action( 'invoiceforge_before_pdf_generated', $order, $document->get_type() );

		$html = $this->render_html( $document );
		if ( '' === $html ) {
			return null;
		}

		$file_path = $this->get_file_path( $document );
		$dir       = dirname( $file_path );

		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$mpdf = $this->create_mpdf_instance();
		if ( null === $mpdf ) {
			return null;
		}

		$mpdf->WriteHTML( $html );
		$mpdf->Output( $file_path, \Mpdf\Output\Destination::FILE );

		// Persist path in order meta.
		$meta_key = '_invoiceforge_' . $document->get_type() . '_path';
		$order->update_meta_data( $meta_key, $file_path );
		$order->save_meta_data();

		do_action( 'invoiceforge_pdf_generated', $order, $document->get_type(), $file_path );

		return $file_path;
	}

	/**
	 * Auto-generates invoice (and optionally packing slip) when an order status fires.
	 *
	 * Hooked to woocommerce_order_status_{status} actions.
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	public function auto_generate_on_status_change( int $order_id ): void {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$configured_statuses = get_option( 'invoiceforge_generate_on_statuses', [ 'completed' ] );
		$current_status      = 'wc-' . $order->get_status();

		if ( ! in_array( $order->get_status(), $configured_statuses, true ) ) {
			return;
		}

		$this->generate( new Invoice_Document( $order ) );

		if ( 'yes' === get_option( 'invoiceforge_generate_packing_slip', 'yes' ) ) {
			$this->generate( new Packing_Slip_Document( $order ) );
		}
	}

	/**
	 * Streams a PDF to the browser for download.
	 *
	 * @param Document $document Document to stream.
	 */
	public function stream( Document $document ): void {
		$file_path = $this->get_file_path( $document );

		// Regenerate if the file doesn't exist yet.
		if ( ! file_exists( $file_path ) ) {
			$file_path = $this->generate( $document );
		}

		if ( ! $file_path || ! file_exists( $file_path ) ) {
			wp_die( esc_html__( 'Invoice not found.', 'invoiceforge-for-woocommerce' ) );
		}

		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . esc_attr( $document->get_filename() ) . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		header( 'Cache-Control: private, max-age=0, must-revalidate' );
		readfile( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}

	/**
	 * Returns the absolute path where a document's PDF should be stored.
	 *
	 * @param Document $document Document instance.
	 * @return string
	 */
	public function get_file_path( Document $document ): string {
		$year      = gmdate( 'Y' );
		$order_id  = $document->get_order()->get_id();
		$base_path = invoiceforge_get_storage_dir() . "{$year}/{$order_id}/";

		$path = $base_path . $document->get_type() . '.pdf';
		return apply_filters( 'invoiceforge_pdf_storage_path', $path, $document->get_order(), $document->get_type() );
	}

	/**
	 * Renders the document HTML via the template loader.
	 *
	 * @param Document $document Document instance.
	 * @return string HTML string.
	 */
	private function render_html( Document $document ): string {
		$template_name = $document->get_default_template();
		$data          = $document->get_template_data();

		// Load the stylesheet and inline it into the data array.
		$style_path = $this->template_loader->locate_template( $document->get_type(), 'style' );
		if ( $style_path && str_ends_with( $style_path, '.php' ) ) {
			// Swap extension to css.
			$style_path = str_replace( '.php', '.css', $style_path );
		}
		$css = '';
		if ( $style_path && str_ends_with( $style_path, '.css' ) && file_exists( $style_path ) ) {
			$css = file_get_contents( $style_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		$data['inline_css'] = $css;

		return $this->template_loader->render( $document, $template_name, $data );
	}

	/**
	 * Creates and returns a configured mPDF instance.
	 *
	 * @return \Mpdf\Mpdf|null
	 */
	private function create_mpdf_instance(): ?\Mpdf\Mpdf {
		if ( ! class_exists( \Mpdf\Mpdf::class ) ) {
			return null;
		}

		$tmp_dir = get_temp_dir() . 'invoiceforge/';
		if ( ! file_exists( $tmp_dir ) ) {
			wp_mkdir_p( $tmp_dir );
		}

		return new \Mpdf\Mpdf(
			[
				'mode'              => 'utf-8',
				'format'            => 'A4',
				'margin_top'        => 15,
				'margin_right'      => 15,
				'margin_bottom'     => 15,
				'margin_left'       => 15,
				'margin_header'     => 9,
				'margin_footer'     => 9,
				'tempDir'           => $tmp_dir,
				'autoScriptToLang' => true,
				'autoLangToFont'   => true,
			]
		);
	}
}
