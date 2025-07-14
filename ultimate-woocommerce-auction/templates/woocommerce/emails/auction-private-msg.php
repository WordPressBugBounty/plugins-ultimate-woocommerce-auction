<?php
/**
 * Admin - Private Message By User. (HTML)
 */
/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email ); 

if ( is_array( $email->object ) && isset( $email->object['product_id'] ) ) {

	// LIVE EMAIL DATA
	$user_name      = esc_html( $email->object['user_name'] );
	$user_email_id  = sanitize_email( $email->object['user_email'] );
	$user_message   = esc_html( $email->object['user_message'] );
	$auction_url    = esc_url( $email->object['url_product'] );
	$product_id     = absint( $email->object['product_id'] );
	$product        = wc_get_product( $product_id );
	$auction_title  = esc_attr( $product->get_title() );

} else {

	// PREVIEW MODE
	$order      = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item       = $order ? current( $order->get_items() ) : null;
	$product    = $item ? $item->get_product() : null;
	$product_id = $product ? $product->get_id() : 0;

	$user_name      = $order ? esc_html( $order->get_formatted_billing_full_name() ) : __( 'Bidder Name', 'ultimate-woocommerce-auction' );
	$user_email_id  = $order ? $order->get_billing_email() : 'user@example.com';
	$user_message   = __( 'This is a preview of the message from the user.', 'ultimate-woocommerce-auction' );
	$auction_url    = $product ? get_permalink( $product_id ) : home_url();
	$auction_title  = $product ? esc_attr( $product->get_title() ) : __( 'Auction Product', 'ultimate-woocommerce-auction' );
}


?>
<p><?php printf( esc_html__( 'Hi Admin,', 'ultimate-woocommerce-auction' ) ); ?></p>
<?php // Translators: Placeholder %s represents the outbid text. ?>
<p><?php printf( wp_kses( "Bidder Sent Private Message for Auction <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) ); ?></p>
<p><?php printf( esc_html__( 'Here are the details : ', 'ultimate-woocommerce-auction' ) ); ?></p>
<table>
	<tr>    
		<td><?php esc_html_e( 'Name:', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php echo esc_attr( $user_name ); ?></td>	 
	</tr>
	<tr>    
		<td><?php esc_html_e( 'Email:', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php echo esc_attr( $user_email_id ); ?></td>	 
	</tr>
	<tr>    
		<td><?php esc_html_e( 'Message:', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php echo esc_attr( $user_message ); ?></td>	 
	</tr>    
</table>
<?php do_action( 'woocommerce_email_footer', $email ); 
