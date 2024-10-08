<?php

/**
 * Admin notification when auction won by user. (HTML)
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<?php
$product_id        = $email->object['product_id'];
$product           = wc_get_product( $product_id );
$auction_url       = esc_url( $email->object['url_product'] );
$user_name         = esc_html( $email->object['user_name'] );
$auction_title     = esc_attr( $product->get_title() );
$auction_bid_value = wc_price( $product->get_woo_ua_current_bid() );
$thumb_image       = $product->get_image( 'thumbnail' );
$userlink          = add_query_arg( 'user_id', $email->object['user_id'], admin_url( 'user-edit.php' ) );
?>
<p><?php printf( esc_html__( 'Hi Admin,', 'ultimate-woocommerce-auction' ) ); ?></p>
<?php // Translators: Placeholder %s represents the outbid text. ?>
<p><?php wp_kses( esc_html__( "The auction has expired and won by user. Auction url <a href='%1\$s'>%2\$s</a>.", 'ultimate-woocommerce-auction' ), esc_url( $auction_url ), esc_attr( $auction_title ) ); ?></p>
<p><?php printf( esc_html__( 'Here are the details : ', 'ultimate-woocommerce-auction' ) ); ?></p>
<table>
	<tr>    
		<td><?php esc_html_e( 'Image', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php esc_html_e( 'Product', 'ultimate-woocommerce-auction' ); ?></td>
		<td><?php esc_html_e( 'Winning bid', 'ultimate-woocommerce-auction' ); ?></td>	
		<td><?php esc_html_e( 'Winner', 'ultimate-woocommerce-auction' ); ?></td>	
	</tr>
	<tr>
		<td><?php echo wp_kses_post( $thumb_image ); ?></td>
		<td><a href="<?php echo esc_url( $auction_url ); ?>"><?php echo esc_attr( $auction_title ); ?></a></td>
		<td><?php echo wp_kses_post( $auction_bid_value ); ?></td>
		<td><a href="<?php echo esc_url( $userlink ); ?>"><?php echo esc_attr( $user_name ); ?></a></td>
	</tr>
</table>

<?php do_action( 'woocommerce_email_footer', $email ); ?>