<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Send Email to bidder when the bidder won the auction. (HTML)
 */

do_action( 'woocommerce_email_header', $email_heading, $email );

if ( is_array( $email->object ) && isset( $email->object['product_id'] ) ) {

	// LIVE EMAIL
	$product_id        = absint( $email->object['product_id'] );
	$product           = wc_get_product( $product_id );
	$auction_url       = esc_url( $email->object['url_product'] );
	$user_name         = esc_html( $email->object['user_name'] );
	$auction_title     = esc_attr( $product->get_title() );
	$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );
	$thumb_image       = $product->get_image( 'thumbnail' );

} else {

	// PREVIEW MODE
	$order   = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item    = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;

	$auction_url   = $product ? get_permalink( $product_id ) : home_url();
	$user_name     = $order ? esc_html( $order->get_formatted_billing_full_name() ) : '';
	$auction_title = $product ? esc_attr( $product->get_title() ) : __( 'Auction Product', 'ultimate-woocommerce-auction' );

	$auction_bid_raw = $product ? get_post_meta( $product_id, '_auction_current_bid', true ) : 0;
	if ( ! $auction_bid_raw && $item ) {
		$auction_bid_raw = $item->get_total();
	}

	$auction_bid_value = wc_price( $auction_bid_raw );
	$thumb_image       = $product ? $product->get_image( 'thumbnail' ) : '';

}

// Nonce and checkout URL
$nonce = wp_create_nonce( 'uwa_add_to_cart_nonce' );

$checkout_url = add_query_arg(
	array(
		'pay-uwa-auction' => absint( $product_id ),
		'nonce'           => sanitize_text_field( $nonce ),
	),
	woo_ua_auction_get_checkout_url()
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