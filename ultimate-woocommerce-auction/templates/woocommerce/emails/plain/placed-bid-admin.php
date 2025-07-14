<?php
/**
 * Admin notification when Bidder placed a bid (plain)
 */
/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce;

/* -------- Live + Preview Mode Compatible -------- */
if ( is_array( $email->object ) && isset( $email->object['product'] ) ) {

	// LIVE EMAIL context
	$product           = $email->object['product'];
	$auction_url       = esc_url( $email->object['url_product'] );
	$auction_title     = esc_attr( $product->get_title() );
	$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );

} else {

	// PREVIEW EMAIL context
	$order   = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item    = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;

	$auction_url   = $product ? get_permalink( $product->get_id() ) : home_url();
	$auction_title = $product ? esc_attr( $product->get_title() ) : __( 'Auction Product', 'ultimate-woocommerce-auction' );

	$bid_raw = $product ? get_post_meta( $product->get_id(), '_auction_current_bid', true ) : 0;
	if ( ! $bid_raw && $item ) { $bid_raw = $item->get_total(); }

	$auction_bid_value = wc_price( $bid_raw );
}

echo esc_attr( $email_heading ) . '</br>';
printf( wp_kses( 'Hi Admin,', 'ultimate-woocommerce-auction' ) );
echo '</br>';
printf( wp_kses( "A bid was placed on <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) );
echo '</br>';
printf( wp_kses( 'Bid Value %s.', 'ultimate-woocommerce-auction' ), wp_kses_post( $auction_bid_value ) );
echo '</br>';
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
