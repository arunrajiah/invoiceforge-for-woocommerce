<?php
/**
 * Default packing slip template — rendered by mPDF.
 *
 * @var \InvoiceForge\PDF\Packing_Slip_Document $document
 * @var \WC_Order                               $order
 * @var \WC_Order_Item[]                         $shippable_items
 * @var array<string, string>                    $billing_address
 * @var array<string, string>                    $shipping_address
 * @var string                                   $shipping_method
 * @var string                                   $tracking_number
 * @var string                                   $shop_name
 * @var string                                   $shop_logo_url
 * @var string                                   $footer_text
 * @var string                                   $inline_css
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
<style><?php echo $inline_css; // phpcs:ignore WordPress.Security.EscapeOutput ?></style>
</head>
<body>
<div class="slip-wrapper">

	<!-- Header -->
	<table class="header-table" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width:50%; vertical-align:top;">
				<?php if ( $shop_logo_url ) : ?>
					<img src="<?php echo esc_url( $shop_logo_url ); ?>" alt="<?php echo esc_attr( $shop_name ); ?>">
				<?php else : ?>
					<span class="shop-name"><?php echo esc_html( $shop_name ); ?></span>
				<?php endif; ?>
			</td>
			<td style="width:50%; text-align:right; vertical-align:top; font-size:10pt; color:#555;">
				<div class="shop-name"><?php echo esc_html( $shop_name ); ?></div>
			</td>
		</tr>
	</table>

	<div class="slip-title"><?php esc_html_e( 'Packing Slip', 'invoiceforge-for-woocommerce' ); ?></div>

	<!-- Meta -->
	<table class="meta-table" cellspacing="0" cellpadding="0">
		<tr>
			<td class="label"><?php esc_html_e( 'Order Number', 'invoiceforge-for-woocommerce' ); ?></td>
			<td class="value"><?php echo esc_html( $order->get_order_number() ); ?></td>
		</tr>
		<tr>
			<td class="label"><?php esc_html_e( 'Order Date', 'invoiceforge-for-woocommerce' ); ?></td>
			<td class="value"><?php echo esc_html( $order_date ? $order_date->date_i18n( $date_format ) : '' ); ?></td>
		</tr>
		<?php if ( $shipping_method ) : ?>
			<tr>
				<td class="label"><?php esc_html_e( 'Shipping Method', 'invoiceforge-for-woocommerce' ); ?></td>
				<td class="value"><?php echo esc_html( $shipping_method ); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( $tracking_number ) : ?>
			<tr>
				<td class="label"><?php esc_html_e( 'Tracking #', 'invoiceforge-for-woocommerce' ); ?></td>
				<td class="value"><?php echo esc_html( $tracking_number ); ?></td>
			</tr>
		<?php endif; ?>
	</table>

	<!-- Addresses -->
	<table class="addresses-table" cellspacing="0" cellpadding="0">
		<tr>
			<td class="address-block" style="padding-right:4%;">
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
							$shipping_address['country'] ?? '',
						]
					);
					echo nl2br( esc_html( implode( "\n", $shipping_parts ) ) );
					?>
					<?php if ( ! empty( $billing_address['phone'] ) ) : ?>
						<br><?php echo esc_html( $billing_address['phone'] ); ?>
					<?php endif; ?>
				</address>
			</td>
			<td class="address-block">
				<h3><?php esc_html_e( 'Bill To', 'invoiceforge-for-woocommerce' ); ?></h3>
				<address>
					<?php
					$billing_parts = array_filter(
						[
							trim( ( $billing_address['first_name'] ?? '' ) . ' ' . ( $billing_address['last_name'] ?? '' ) ),
							$billing_address['company'] ?? '',
							$billing_address['address_1'] ?? '',
							$billing_address['city'] ?? '',
						]
					);
					echo nl2br( esc_html( implode( "\n", $billing_parts ) ) );
					?>
				</address>
			</td>
		</tr>
	</table>

	<!-- Items -->
	<table class="items-table" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th style="width:60%;"><?php esc_html_e( 'Product', 'invoiceforge-for-woocommerce' ); ?></th>
				<th class="text-center" style="width:15%;"><?php esc_html_e( 'SKU', 'invoiceforge-for-woocommerce' ); ?></th>
				<th class="text-center" style="width:10%;"><?php esc_html_e( 'Qty', 'invoiceforge-for-woocommerce' ); ?></th>
				<th class="text-right" style="width:15%;"><?php esc_html_e( 'Weight', 'invoiceforge-for-woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $shippable_items as $item ) : ?>
				<?php
				/** @var \WC_Order_Item_Product $item */
				$product    = $item->get_product();
				$sku        = $product ? $product->get_sku() : '';
				$weight     = $product ? $product->get_weight() : '';
				$meta_data  = $item->get_all_formatted_meta_data( '' );
				?>
				<tr>
					<td>
						<div class="item-name"><?php echo esc_html( $item->get_name() ); ?></div>
						<?php foreach ( $meta_data as $meta ) : ?>
							<div class="item-meta"><?php echo wp_kses_post( $meta->display_key ); ?>: <?php echo wp_kses_post( $meta->display_value ); ?></div>
						<?php endforeach; ?>
					</td>
					<td class="text-center"><?php echo esc_html( $sku ); ?></td>
					<td class="text-center"><?php echo esc_html( $item->get_quantity() ); ?></td>
					<td class="text-right">
						<?php if ( $weight ) : ?>
							<?php echo esc_html( $weight ); ?> <?php echo esc_html( get_option( 'woocommerce_weight_unit', 'kg' ) ); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $footer_text ) : ?>
		<div class="footer"><?php echo wp_kses_post( $footer_text ); ?></div>
	<?php endif; ?>

</div>
</body>
</html>
