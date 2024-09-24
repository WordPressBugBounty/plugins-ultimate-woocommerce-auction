<?php
/**
 * Bidder placed a bid email notification (plain)
 */
/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce;
$product           = $email->object['product'];
$auction_url       = esc_url( $email->object['url_product'] );
$user_name         = esc_html( $email->object['user_name'] );
$auction_title     = esc_attr( $product->get_title() );
$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );
$auction_bid_value = wp_kses_post( $auction_bid_value );
echo esc_attr( $email_heading ) . '</br>';
printf( wp_kses( 'Hi %s,', 'ultimate-woocommerce-auction' ), esc_html( $user_name ) );
echo '</br>';
printf( wp_kses( "You recently placed a bid on <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) );
echo '</br>';
printf( wp_kses( 'Bid Value %s.', 'ultimate-woocommerce-auction' ), wp_kses_post( $auction_bid_value ) );
echo '</br>';
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
