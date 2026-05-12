<?php
/**
 * Loads the correct PHP template for a document type.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves template paths with theme-override support.
 *
 * Override priority (highest to lowest):
 *   1. Active theme: /woocommerce/invoiceforge/{type}/{name}.php
 *   2. Plugin: templates/pdf/{type}/{name}.php
 */
class Template_Loader {

	/**
	 * Renders a document template and returns the HTML string.
	 *
	 * @param Document             $document     Document instance.
	 * @param string               $template_name Template filename without .php extension.
	 * @param array<string, mixed> $data         Variables to extract into the template scope.
	 * @return string Rendered HTML.
	 */
	public function render( Document $document, string $template_name, array $data ): string {
		$path = $this->locate_template( $document->get_type(), $template_name );

		if ( ! $path || ! file_exists( $path ) ) {
			return '';
		}

		ob_start();
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
		include $path;
		return (string) ob_get_clean();
	}

	/**
	 * Resolves the absolute path to a template file.
	 *
	 * @param string $document_type Document type slug.
	 * @param string $template_name Template name (without .php).
	 * @return string|null Absolute path or null if not found.
	 */
	public function locate_template( string $document_type, string $template_name ): ?string {
		$relative = "woocommerce/invoiceforge/{$document_type}/{$template_name}.php";

		// Allow Pro or themes to override the resolved path entirely.
		$path = apply_filters(
			'invoiceforge_template_path',
			'',
			$document_type,
			$template_name
		);

		if ( $path && file_exists( $path ) ) {
			return $path;
		}

		// Theme override check.
		$theme_path = locate_template( $relative );
		if ( $theme_path ) {
			return $theme_path;
		}

		// Default: plugin templates directory.
		$plugin_path = INVOICEFORGE_PLUGIN_DIR . "templates/pdf/{$document_type}/{$template_name}.php";
		return file_exists( $plugin_path ) ? $plugin_path : null;
	}
}
