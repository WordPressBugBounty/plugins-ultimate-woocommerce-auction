<?php
/**
 * Bidder placed a bid email notification (plain)
 */
/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce;
$product_id        = $email->object['product_id'];
$product           = wc_get_product( $product_id );
$auction_url       = esc_url( $email->object['url_product'] );
$auction_title     = esc_attr( $product->get_title() );
$user_name         = esc_html( $email->object['user_name'] );
$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );
$nonce = wp_create_nonce( 'uwa_add_to_cart_nonce' );
//$checkout_url  = add_query_arg( array( 'pay-uwa-auction' => $product_id ), woo_ua_auction_get_checkout_url() );

$checkout_url = add_query_arg(
    array(
        'pay-uwa-auction' => absint( $product_id ), // Product ID, cast to integer for extra safety
		'nonce'           => sanitize_text_field( $nonce ) // Sanitize the nonce value for security
    ),
    woo_ua_auction_get_checkout_url() // Base URL for checkout
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
