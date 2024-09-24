<?php
/**
 * User placed a bid email notification (plain)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/* Exit if accessed directly */
global $woocommerce;
$product           = $email->object['product'];
$auction_url       = esc_url( $email->object['url_product'] );
$auction_title     = esc_attr( $product->get_title() );
$user_name         = esc_attr( $email->object['user_name'] );
$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );
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
