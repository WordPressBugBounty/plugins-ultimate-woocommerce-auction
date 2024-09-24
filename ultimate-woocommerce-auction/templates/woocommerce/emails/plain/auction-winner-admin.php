<?php
/**
 * Admin notification when auction won by Bidder. (plain)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/* Exit if accessed directly */
global $woocommerce;
$product_id    = $email->object['product_id'];
$product       = wc_get_product( $product_id );
$auction_url   = esc_url( $email->object['url_product'] );
$bidder        = esc_attr( $email->object['user_name'] );
$auction_title = esc_attr( $product->get_title() );

$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );


$bidderlink = add_query_arg( 'user_id', $email->object['user_id'], admin_url( 'user-edit.php' ) );


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
