<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<?php
if ( is_array( $email->object ) && isset( $email->object['product'] ) ) {

	// LIVE EMAIL PATH
	$product           = $email->object['product'];
	$auction_url       = esc_url( $email->object['url_product'] );
	$user_name         = esc_html( $email->object['user_name'] );
	$auction_title     = esc_attr( $product->get_title() );
	$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );
	$thumb_image       = $product->get_image( 'thumbnail' );

} else {

	// PREVIEW MODE PATH
	$order = is_a( $email->object, 'WC_Order' ) ? $email->object : null;
	$item  = $order ? current( $order->get_items() ) : null;
	$product = $item ? $item->get_product() : null;

	$auction_url       = $product ? get_permalink( $product->get_id() ) : home_url();
	$user_name         = $order ? esc_html( $order->get_formatted_billing_full_name() ) : '';
	$auction_title     = $product ? esc_attr( $product->get_title() ) : __( 'Auction Product', 'ultimate-woocommerce-auction' );

	$auction_bid_raw   = $product ? get_post_meta( $product->get_id(), '_auction_current_bid', true ) : 0;
	if ( ! $auction_bid_raw && $item ) {
		$auction_bid_raw = $item->get_total();
	}

	$auction_bid_value = wc_price( $auction_bid_raw );
	$thumb_image       = $product ? $product->get_image( 'thumbnail' ) : '';

}
?>
<?php // Translators: Placeholder %s represents the outbid text. ?>
<p><?php printf( esc_html__( 'Hi %s,', 'ultimate-woocommerce-auction' ), esc_html( $user_name ) ); ?></p>
<?php // Translators: Placeholder %s represents the outbid text. ?>
<p><?php printf( wp_kses( "Auction has been outbid. Auction url <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) ); ?></p>
<p><?php printf( esc_html__( 'Here are the details : ', 'ultimate-woocommerce-auction' ) ); ?></p>
<table>
	<tr>    
		<td><?php esc_html_e( 'Image', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php esc_html_e( 'Product', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php esc_html_e( 'Current bid', 'ultimate-woocommerce-auction' ); ?></td>	
	</tr>
	<tr>
		<td><?php echo wp_kses_post( $thumb_image ); ?></td>
		<td><a href="<?php echo esc_url( $auction_url ); ?>"><?php echo esc_attr( $auction_title ); ?></a></td>
		<td><?php echo wp_kses_post( $auction_bid_value ); ?></td>
	</tr>
</table>
	<div>
		<p><?php esc_html_e( 'If you want to bid a new amount, click here', 'ultimate-woocommerce-auction' ); ?> 
		<a href="<?php echo esc_url( $auction_url ); ?>"><?php echo esc_attr( $auction_title ); ?></a> </p>
	</div>
<?php do_action( 'woocommerce_email_footer', $email );
