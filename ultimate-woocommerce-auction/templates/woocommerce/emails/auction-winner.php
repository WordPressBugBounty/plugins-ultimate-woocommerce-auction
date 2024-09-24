<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Send Email to bidder when the bidder won the auction. (HTML)
 */

do_action( 'woocommerce_email_header', $email_heading, $email );

$product_id        = $email->object['product_id'];
$product           = wc_get_product( $product_id );
$auction_url       = esc_url( $email->object['url_product'] );
$user_name         = esc_html( $email->object['user_name'] );
$auction_title     = esc_attr( $product->get_title() );
$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );
$thumb_image       = $product->get_image( 'thumbnail' );
$nonce = wp_create_nonce( 'uwa_add_to_cart_nonce' );
//$checkout_url  = add_query_arg( array( 'pay-uwa-auction' => $product_id  ), woo_ua_auction_get_checkout_url() );


$checkout_url = add_query_arg(
    array(
        'pay-uwa-auction' => absint( $product_id ), // Product ID, cast to integer for extra safety
		'nonce'           => sanitize_text_field( $nonce ) // Sanitize the nonce value for security
    ),
    woo_ua_auction_get_checkout_url() // Base URL for checkout
);


?>
<?php // Translators: Placeholder %s represents the outbid text. ?>
<p><?php printf( esc_html__( 'Hi %s,', 'ultimate-woocommerce-auction' ), esc_html( $user_name ) ); ?></p>
<?php // Translators: Placeholder %s represents the outbid text. ?>
<p><?php printf( wp_kses( "Congratulations! You are the winner! of the auction product <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) ); ?></p>
<p><?php printf( esc_html__( 'Here are the details : ', 'ultimate-woocommerce-auction' ) ); ?></p>
<table>
	<tr>    
		<td><?php esc_html_e( 'Image', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php esc_html_e( 'Product', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php esc_html_e( 'Winning bid', 'ultimate-woocommerce-auction' ); ?></td>	
	</tr>
	<tr>
		<td><?php echo wp_kses_post( $thumb_image ); ?></td>
		<td><a href="<?php echo esc_url( $auction_url ); ?>"><?php echo esc_attr( $auction_title ); ?></a></td>
		<td><?php echo wp_kses_post( $auction_bid_value ); ?></td>
	</tr>
</table>    
	<div>
		<p><?php esc_html_e( 'Please, proceed to checkout', 'ultimate-woocommerce-auction' ); ?></p>
		<p><a style="padding:6px 28px !important;font-size: 12px !important; background: #ccc !important; color: #333 !important; text-decoration: none!important; text-transform: uppercase!important; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif !important;font-weight: 800 !important; border-radius: 3px !important; display: inline-block !important;" href="<?php echo esc_url($checkout_url); ?>" class="button"><?php esc_html_e( 'Pay Now', 'ultimate-woocommerce-auction' ); ?></a>
		</p>
	   
	</div>
<?php do_action( 'woocommerce_email_footer', $email ); ?>