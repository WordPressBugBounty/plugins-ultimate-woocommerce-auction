<?php
/**
 * Admin notification when Bidder placed a bid (HTML)
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<?php
$product           = $email->object['product'];
$auction_url       = esc_url( $email->object['url_product'] );
$auction_title     = esc_attr( $product->get_title() );
$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );
$thumb_image       = $product->get_image( 'thumbnail' );
?>
<p><?php printf( esc_html__( 'Hi Admin,', 'ultimate-woocommerce-auction' ) ); ?></p>
<p><?php printf( wp_kses( "A bid was placed on <a href='%s'>%s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) ); ?></p>
<p><?php printf( esc_html__( 'Here are the details : ', 'ultimate-woocommerce-auction' ) ); ?></p>
<table>
	<tr>    
		<td><?php esc_html_e( 'Image', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php esc_html_e( 'Product', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php esc_html_e( 'Bid Value', 'ultimate-woocommerce-auction' ); ?></td>	
	</tr>
	<tr>
		<td><?php echo wp_kses_post( $thumb_image ); ?></td>
		<td><a href="<?php echo esc_url( $auction_url ); ?>"><?php echo esc_attr( $auction_title ); ?></a></td>
		<td><?php echo wp_kses_post( $auction_bid_value ); ?></td>
	</tr>
</table>
<?php do_action( 'woocommerce_email_footer', $email ); ?>