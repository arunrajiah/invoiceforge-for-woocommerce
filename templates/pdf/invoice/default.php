<?php
/**
 * Default invoice template — rendered by mPDF.
 *
 * Available variables (from Invoice_Document::build_template_data()):
 *
 * @var \InvoiceForge\PDF\Invoice_Document $document
 * @var \WC_Order                          $order
 * @var string                             $document_type
 * @var string                             $document_label
 * @var string|null                        $document_number
 * @var string|null                        $invoice_number
 * @var \WC_DateTime|null                  $order_date
 * @var \WC_Order_Item[]                   $order_items
 * @var string                             $order_subtotal
 * @var string                             $order_shipping
 * @var string                             $order_discount
 * @var string                             $order_tax
 * @var string                             $order_total
 * @var string                             $payment_method
 * @var string                             $currency
 * @var array<string, string>              $billing_address
 * @var array<string, string>              $shipping_address
 * @var string                             $shop_name
 * @var string                             $shop_address
 * @var string                             $shop_vat_number
 * @var string                             $shop_logo_url
 * @var string                             $tax_label
 * @var string                             $footer_text
 * @var string                             $inline_css
 *
 * @package InvoiceForge
 */

defined( 'ABSPATH' ) || exit;

$date_format = get_option( 'date_format', 'F j, Y' );
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_locale() ); ?>">
<head>
<meta charset="UTF-8">
<style><?php echo $inline_css; // phpcs:ignore WordPress.Security.EscapeOutput -- CSS from plugin files. ?></style>
</head>
<body>
<div class="invoice-wrapper">

	<!-- Header -->
	<table class="header-table" cellspacing="0" cellpadding="0">
		<tr>
			<td class="logo" style="width:50%; vertical-align:top;">
				<?php if ( $shop_logo_url ) : ?>
					<img src="<?php echo esc_url( $shop_logo_url ); ?>" alt="<?php echo esc_attr( $shop_name ); ?>">
				<?php else : ?>
					<span class="shop-name" style="font-size:16pt; font-weight:bold; color:#2563eb;"><?php echo esc_html( $shop_name ); ?></span>
				<?php endif; ?>
			</td>
			<td class="shop-details" style="width:50%; vertical-align:top;">
				<div class="shop-name"><?php echo esc_html( $shop_name ); ?></div>
				<?php if ( $shop_address ) : ?>
					<div style="white-space: pre-line;"><?php echo nl2br( esc_html( $shop_address ) ); ?></div>
				<?php endif; ?>
				<?php if ( $shop_vat_number ) : ?>
					<div><?php echo esc_html( $tax_label ); ?>: <?php echo esc_html( $shop_vat_number ); ?></div>
				<?php endif; ?>
			</td>
		</tr>
	</table>

	<!-- Title & Invoice Meta -->
	<div class="invoice-title-block">
		<span class="invoice-title"><?php echo esc_html( $document_label ); ?></span>
	</div>

	<table class="invoice-meta-table" cellspacing="0" cellpadding="0">
		<tr>
			<td class="label"><?php esc_html_e( 'Invoice Number', 'invoiceforge-for-woocommerce' ); ?></td>
			<td class="value"><?php echo esc_html( $invoice_number ?: $order->get_order_number() ); ?></td>
			<td style="width:40%;"></td>
		</tr>
		<tr>
			<td class="label"><?php esc_html_e( 'Invoice Date', 'invoiceforge-for-woocommerce' ); ?></td>
			<td class="value"><?php echo esc_html( $order_date ? $order_date->date_i18n( $date_format ) : '' ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td class="label"><?php esc_html_e( 'Order Number', 'invoiceforge-for-woocommerce' ); ?></td>
			<td class="value"><?php echo esc_html( $order->get_order_number() ); ?></td>
			<td></td>
		</tr>
	</table>

	<!-- Addresses -->
	<table class="addresses-table" cellspacing="0" cellpadding="0">
		<tr>
			<td class="address-block" style="padding-right:4%;">
				<h3><?php esc_html_e( 'Bill To', 'invoiceforge-for-woocommerce' ); ?></h3>
				<address>
					<?php
					$billing_parts = array_filter(
						[
							trim( ( $billing_address['first_name'] ?? '' ) . ' ' . ( $billing_address['last_name'] ?? '' ) ),
							$billing_address['company'] ?? '',
							$billing_address['address_1'] ?? '',
							$billing_address['address_2'] ?? '',
							trim( ( $billing_address['city'] ?? '' ) . ( isset( $billing_address['postcode'] ) ? ', ' . $billing_address['postcode'] : '' ) ),
							$billing_address['state'] ?? '',
							$billing_address['country'] ?? '',
						]
					);
					echo nl2br( esc_html( implode( "\n", $billing_parts ) ) );
					?>
					<?php if ( ! empty( $billing_address['email'] ) ) : ?>
						<br><a href="mailto:<?php echo esc_attr( $billing_address['email'] ); ?>"><?php echo esc_html( $billing_address['email'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $billing_address['phone'] ) ) : ?>
						<br><?php echo esc_html( $billing_address['phone'] ); ?>
					<?php endif; ?>
				</address>
			</td>
			<td class="address-block">
				<h3><?php esc_html_e( 'Ship To', 'invoiceforge-for-woocommerce' ); ?></h3>
				<address>
					<?php
					$shipping_parts = array_filter(
						[
							trim( ( $shipping_address['first_name'] ?? '' ) . ' ' . ( $shipping_address['last_name'] ?? '' ) ),
							$shipping_address['company'] ?? '',
							$shipping_address['address_1'] ?? '',
							$shipping_address['address_2'] ?? '',
							trim( ( $shipping_address['city'] ?? '' ) . ( isset( $shipping_address['postcode'] ) ? ', ' . $shipping_address['postcode'] : '' ) ),
							$shipping_address['state'] ?? '',
							$shipping_address['country'] ?? '',
						]
					);
					echo nl2br( esc_html( implode( "\n", $shipping_parts ) ) );
					?>
				</address>
			</td>
		</tr>
	</table>

	<!-- Line Items -->
	<table class="items-table" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th style="width:45%;"><?php esc_html_e( 'Product', 'invoiceforge-for-woocommerce' ); ?></th>
				<th class="text-center" style="width:10%;"><?php esc_html_e( 'Qty', 'invoiceforge-for-woocommerce' ); ?></th>
				<th class="text-right" style="width:15%;"><?php esc_html_e( 'Unit Price', 'invoiceforge-for-woocommerce' ); ?></th>
				<th class="text-right" style="width:15%;"><?php echo esc_html( $tax_label ); ?></th>
				<th class="text-right" style="width:15%;"><?php esc_html_e( 'Total', 'invoiceforge-for-woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $order_items as $item_id => $item ) : ?>
				<?php
				/** @var \WC_Order_Item_Product $item */
				$product    = $item->get_product();
				$unit_price = $order->get_item_subtotal( $item, false, true );
				$item_total = $order->get_item_total( $item, false, true );
				$item_tax   = $order->get_item_tax( $item );
				$meta_data  = $item->get_all_formatted_meta_data( '' );
				?>
				<tr>
					<td>
						<div class="item-name"><?php echo esc_html( $item->get_name() ); ?></div>
						<?php if ( $product && $product->get_sku() ) : ?>
							<div class="item-meta"><?php esc_html_e( 'SKU', 'invoiceforge-for-woocommerce' ); ?>: <?php echo esc_html( $product->get_sku() ); ?></div>
						<?php endif; ?>
						<?php foreach ( $meta_data as $meta ) : ?>
							<div class="item-meta"><?php echo wp_kses_post( $meta->display_key ); ?>: <?php echo wp_kses_post( $meta->display_value ); ?></div>
						<?php endforeach; ?>
					</td>
					<td class="text-center"><?php echo esc_html( $item->get_quantity() ); ?></td>
					<td class="text-right"><?php echo wc_price( $unit_price, [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
					<td class="text-right"><?php echo wc_price( $item_tax, [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
					<td class="text-right"><?php echo wc_price( $item->get_total(), [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<!-- Totals -->
	<table class="totals-table" cellspacing="0" cellpadding="0">
		<tr>
			<td class="label"><?php esc_html_e( 'Subtotal', 'invoiceforge-for-woocommerce' ); ?></td>
			<td class="value"><?php echo wc_price( $order_subtotal, [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
		</tr>
		<?php if ( (float) $order_shipping > 0 ) : ?>
			<tr>
				<td class="label"><?php esc_html_e( 'Shipping', 'invoiceforge-for-woocommerce' ); ?></td>
				<td class="value"><?php echo wc_price( $order_shipping, [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( (float) $order_discount > 0 ) : ?>
			<tr>
				<td class="label"><?php esc_html_e( 'Discount', 'invoiceforge-for-woocommerce' ); ?></td>
				<td class="value">-<?php echo wc_price( $order_discount, [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( (float) $order_tax > 0 ) : ?>
			<tr>
				<td class="label"><?php echo esc_html( $tax_label ); ?></td>
				<td class="value"><?php echo wc_price( $order_tax, [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
			</tr>
		<?php endif; ?>
		<tr class="total-row">
			<td class="label"><?php esc_html_e( 'Total', 'invoiceforge-for-woocommerce' ); ?></td>
			<td class="value"><?php echo wc_price( $order_total, [ 'currency' => $order->get_currency() ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
		</tr>
	</table>

	<!-- Payment Info -->
	<div class="payment-info">
		<strong><?php esc_html_e( 'Payment Method', 'invoiceforge-for-woocommerce' ); ?>:</strong> <?php echo esc_html( $payment_method ); ?>
	</div>

	<!-- Footer -->
	<?php if ( $footer_text ) : ?>
		<div class="footer"><?php echo wp_kses_post( $footer_text ); ?></div>
	<?php endif; ?>

</div>
</body>
</html>
