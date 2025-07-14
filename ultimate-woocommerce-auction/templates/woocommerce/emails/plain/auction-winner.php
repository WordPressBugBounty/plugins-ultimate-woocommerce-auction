<?php
/**
 * Bidder placed a bid email notification (plain)
 */
/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce;

/* --- Wrapper to support both live & preview email contexts --- */
if ( is_array( $email->object ) && isset( $email->object['product_id'] ) ) {

    // LIVE EMAIL CONTEXT
    $product_id         = absint( $email->object['product_id'] );
    $product            = wc_get_product( $product_id );
    $auction_url        = esc_url( $email->object['url_product'] );
    $user_name          = esc_html( $email->object['user_name'] );
    $auction_title      = esc_attr( $product->get_title() );
    $auction_bid_value  = wc_price( $product->get_woo_ua_current_bid() );

} else {

    // PREVIEW MODE
    $order      = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
    $item       = $order ? current( $order->get_items() ) : null;
    $product    = $item ? $item->get_product() : null;
    $product_id = $product ? $product->get_id() : 0;

    $auction_url       = $product ? get_permalink( $product_id ) : home_url();
    $user_name         = $order ? esc_html( $order->get_formatted_billing_full_name() ) : __( 'Auction Winner', 'ultimate-woocommerce-auction' );
    $auction_title     = $product ? esc_attr( $product->get_title() ) : __( 'Auction Product', 'ultimate-woocommerce-auction' );

    // fallback bid value (meta or line item)
    $auction_bid_raw   = $product ? get_post_meta( $product_id, '_auction_current_bid', true ) : 0;
    if ( ! $auction_bid_raw && $item ) {
        $auction_bid_raw = $item->get_total();
    }
    $auction_bid_value = wc_price( $auction_bid_raw );
}

// Secure checkout link with nonce
$nonce         = wp_create_nonce( 'uwa_add_to_cart_nonce' );
$checkout_url  = add_query_arg(
    array(
        'pay-uwa-auction' => absint( $product_id ),
        'nonce'           => sanitize_text_field( $nonce ),
    ),
    woo_ua_auction_get_checkout_url()
);


$auction_url  = esc_url( $checkout_url );

$paynowbtn = esc_html__( 'Pay Now', 'ultimate-woocommerce-auction' );
echo esc_attr( $email_heading ) . '</br>';
// Translators: Placeholder %s represents the user_name text.
printf( esc_html__( 'Hi %s,', 'ultimate-woocommerce-auction' ), esc_html( $user_name ) );
echo '</br>';
printf( wp_kses( "Congratulations! You are the winner! of the auction product <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) );
echo '</br>';
printf( wp_kses( 'Winning bid %s.', 'ultimate-woocommerce-auction' ), wp_kses_post( $auction_bid_value ) );
echo '</br>';
// Translators: Placeholder %s represents the checkout text.
printf( wp_kses( "Please, proceed to checkout ,<a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $checkout_url ), esc_html( $paynowbtn ) );
echo '</br>';
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
