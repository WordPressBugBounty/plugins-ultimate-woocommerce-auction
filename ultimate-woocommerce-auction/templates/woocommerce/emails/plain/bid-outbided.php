<?php
/**
 * User placed a bid email notification (plain)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/* Exit if accessed directly */
global $woocommerce;

/* -------- live vs. preview wrapper ------------------------------------ */
if ( is_array( $email->object ) && isset( $email->object['product'] ) ) {

	/* LIVE EMAIL */
	$product           = $email->object['product'];                 // WC_Product
	$auction_url       = esc_url( $email->object['url_product'] );
	$user_name         = esc_attr( $email->object['user_name'] );
	$auction_title     = esc_attr( $product->get_title() );
	$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );

} else {

	/* PREVIEW MODE */
	$order   = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item    = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;

	$auction_url   = $product ? get_permalink( $product->get_id() ) : home_url();
	$user_name     = $order ? esc_html( $order->get_formatted_billing_full_name() ) : __( 'Auction Bidder', 'ultimate-woocommerce-auction' );
	$auction_title = $product ? esc_attr( $product->get_title() ) : __( 'Auction Product', 'ultimate-woocommerce-auction' );

	$bid_raw = $product ? get_post_meta( $product->get_id(), '_auction_current_bid', true ) : 0;
	if ( ! $bid_raw && $item ) { $bid_raw = $item->get_total(); }

	$auction_bid_value = wc_price( $bid_raw );
}

/* final sanitised output */
$auction_bid_value = wp_kses_post( $auction_bid_value );


echo esc_attr( $email_heading ) . '</br>';
// Translators: Placeholder %s represents the user_name text.
printf( esc_html__( 'Hi %s,', 'ultimate-woocommerce-auction' ), esc_attr( $user_name ) );
echo '</br>';
printf( wp_kses( "Auction has been outbid. Auction url <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) );
echo '</br>';
printf( wp_kses( 'Bid Value %s.', 'ultimate-woocommerce-auction' ), wp_kses_post( $auction_bid_value ) );
echo '</br>';
printf( wp_kses( "If you want to bid a new amount, click here <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) );
echo '</br>';
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
