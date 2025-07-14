<?php
/**
 * Admin notification when auction won by Bidder. (plain)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/* Exit if accessed directly */
global $woocommerce;

if ( is_array( $email->object ) && isset( $email->object['product_id'] ) ) {

	// LIVE EMAIL DATA
	$product_id         = absint( $email->object['product_id'] );
	$product            = wc_get_product( $product_id );
	$auction_url        = esc_url( $email->object['url_product'] );
	$bidder             = esc_attr( $email->object['user_name'] );
	$auction_title      = esc_attr( $product->get_title() );
	$auction_bid_value  = wc_price( $product->get_woo_ua_current_bid() );
	$user_id            = absint( $email->object['user_id'] );

} else {

	// PREVIEW MODE
	$order      = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item       = $order ? current( $order->get_items() ) : null;
	$product    = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;

	$auction_url       = $product ? get_permalink( $product_id ) : home_url();
	$bidder            = $order ? esc_html( $order->get_formatted_billing_full_name() ) : __( 'Auction Bidder', 'ultimate-woocommerce-auction' );
	$auction_title     = $product ? esc_attr( $product->get_title() ) : __( 'Auction Product', 'ultimate-woocommerce-auction' );

	// fallback bid value
	$auction_bid_raw   = $product ? get_post_meta( $product_id, '_auction_current_bid', true ) : 0;
	if ( ! $auction_bid_raw && $item ) {
		$auction_bid_raw = $item->get_total();
	}
	$auction_bid_value = wc_price( $auction_bid_raw );

	$user_id = $order ? $order->get_user_id() : 0;
}

// Admin user profile link
$bidderlink = add_query_arg( 'user_id', absint( $user_id ), admin_url( 'user-edit.php' ) );


echo esc_attr( $email_heading ) . '</br>';
printf( esc_html__( 'Hi Admin,', 'ultimate-woocommerce-auction' ) );
echo '</br>';
printf( wp_kses( "The auction has expired and won by user. Auction url <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) );
echo '</br>';
// Translators: Placeholder %s represents the user_name text.
printf( wp_kses( "<a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $bidderlink ), esc_attr( $bidder ) );
echo '</br>';
echo '</br>';
// Translators: Placeholder %s represents the user_name text.
printf( esc_html__( 'Winning bid %s.', 'ultimate-woocommerce-auction' ), wp_kses_post( $auction_bid_value ) );
echo '</br>';
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
