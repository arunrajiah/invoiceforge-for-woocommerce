<?php
/**
 * REST API endpoints.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\API;

defined( 'ABSPATH' ) || exit;

/**
 * Registers /wp-json/invoiceforge/v1/ endpoints.
 *
 * GET  /invoices/{order_id}           — Returns PDF (streams or returns metadata).
 * POST /invoices/{order_id}/regenerate — Regenerates the PDF (admin only).
 */
class REST_API {

	private const NAMESPACE = 'invoiceforge/v1';

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/invoices/(?P<order_id>[\d]+)',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_invoice' ],
				'permission_callback' => [ $this, 'can_view_invoice' ],
				'args'                => $this->order_id_args(),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/invoices/(?P<order_id>[\d]+)/regenerate',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'regenerate_invoice' ],
				'permission_callback' => [ $this, 'can_manage' ],
				'args'                => $this->order_id_args(),
			]
		);
	}

	/**
	 * GET /invoices/{order_id}
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_invoice( \WP_REST_Request $request ) {
		$order = $this->get_order( $request );
		if ( is_wp_error( $order ) ) {
			return $order;
		}

		$invoice_path = $order->get_meta( '_invoiceforge_invoice_path', true );

		if ( ! $invoice_path || ! file_exists( $invoice_path ) ) {
			return new \WP_Error(
				'invoiceforge_not_found',
				__( 'Invoice PDF not yet generated.', 'invoiceforge-for-woocommerce' ),
				[ 'status' => 404 ]
			);
		}

		return rest_ensure_response(
			[
				'order_id'       => $order->get_id(),
				'invoice_number' => invoiceforge_get_invoice_number( $order ),
				'file_size'      => filesize( $invoice_path ),
				'download_url'   => wp_nonce_url(
					add_query_arg(
						[
							'invoiceforge_action' => 'download_invoice',
							'order_id'            => $order->get_id(),
						],
						home_url( '/' )
					),
					'invoiceforge_download_' . $order->get_id()
				),
			]
		);
	}

	/**
	 * POST /invoices/{order_id}/regenerate
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function regenerate_invoice( \WP_REST_Request $request ) {
		$order = $this->get_order( $request );
		if ( is_wp_error( $order ) ) {
			return $order;
		}

		$generator = new \InvoiceForge\PDF\PDF_Generator();
		$file_path = $generator->generate( new \InvoiceForge\PDF\Invoice_Document( $order ) );

		if ( ! $file_path ) {
			return new \WP_Error(
				'invoiceforge_generation_failed',
				__( 'PDF generation failed.', 'invoiceforge-for-woocommerce' ),
				[ 'status' => 500 ]
			);
		}

		return rest_ensure_response(
			[
				'success'        => true,
				'order_id'       => $order->get_id(),
				'invoice_number' => invoiceforge_get_invoice_number( $order ),
			]
		);
	}

	public function can_view_invoice( \WP_REST_Request $request ): bool {
		$order_id = (int) $request->get_param( 'order_id' );
		return invoiceforge_current_user_can_view( $order_id );
	}

	public function can_manage(): bool {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * @return \WC_Order|\WP_Error
	 */
	private function get_order( \WP_REST_Request $request ) {
		$order_id = (int) $request->get_param( 'order_id' );
		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
			return new \WP_Error(
				'invoiceforge_order_not_found',
				__( 'Order not found.', 'invoiceforge-for-woocommerce' ),
				[ 'status' => 404 ]
			);
		}
		return $order;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	private function order_id_args(): array {
		return [
			'order_id' => [
				'description'       => __( 'WooCommerce order ID.', 'invoiceforge-for-woocommerce' ),
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}
}
