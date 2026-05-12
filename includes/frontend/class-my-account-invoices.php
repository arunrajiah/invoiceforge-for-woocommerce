<?php
/**
 * My Account → Orders — Download Invoice button.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a "Download Invoice" action link to the My Account orders table.
 */
class My_Account_Invoices {

	public function enqueue_styles(): void {
		if ( ! is_account_page() ) {
			return;
		}
		wp_enqueue_style(
			'invoiceforge-frontend',
			INVOICEFORGE_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			INVOICEFORGE_VERSION
		);
	}

	/**
	 * Adds the "Download Invoice" action to My Account orders.
	 *
	 * @param array<string, array<string, string>> $actions Existing actions.
	 * @param \WC_Order                            $order   Current order.
	 * @return array<string, array<string, string>>
	 */
	public function add_download_invoice_action( array $actions, \WC_Order $order ): array {
		if ( ! invoiceforge_current_user_can_view( $order ) ) {
			return $actions;
		}

		$invoice_path = $order->get_meta( '_invoiceforge_invoice_path', true );

		if ( ! $invoice_path || ! file_exists( $invoice_path ) ) {
			return $actions;
		}

		$actions['invoiceforge_download_invoice'] = [
			'url'  => $this->get_download_url( $order ),
			'name' => __( 'Download Invoice', 'invoiceforge-for-woocommerce' ),
		];

		return apply_filters( 'invoiceforge_my_account_actions', $actions, $order );
	}

	/**
	 * Returns the nonce-protected URL for downloading an invoice.
	 *
	 * @param \WC_Order $order Order instance.
	 * @return string URL.
	 */
	private function get_download_url( \WC_Order $order ): string {
		return wp_nonce_url(
			add_query_arg(
				[
					'invoiceforge_action' => 'download_invoice',
					'order_id'            => $order->get_id(),
				],
				home_url( '/' )
			),
			'invoiceforge_download_' . $order->get_id()
		);
	}
}
