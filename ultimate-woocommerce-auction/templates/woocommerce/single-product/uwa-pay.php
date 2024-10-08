<?php
/**
 * Auction Payment Button for winner
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce, $product, $post;
if ( ! ( method_exists( $product, 'get_type' ) && $product->get_type() == 'auction' ) ) {
	return;
}
$user_id      = get_current_user_id();
$nonce = wp_create_nonce( 'uwa_add_to_cart_nonce' );
//$checkout_url = esc_attr(add_query_arg("pay-uwa-auction",$product->get_id(), woo_ua_auction_get_checkout_url()));

$checkout_url = add_query_arg(
    array(
        'pay-uwa-auction' => $product->get_id(), // Product ID
        'nonce'            => $nonce // Nonce for security
    ),
    woo_ua_auction_get_checkout_url() // Base URL for checkout
);


if ( ( $user_id == $product->get_woo_ua_auction_current_bider() && $product->get_woo_ua_auction_closed() == '2' && ! $product->get_woo_ua_auction_payed() ) ) :
	?>
	<p><?php esc_html_e( 'Congratulations! You have won this auction.', 'ultimate-woocommerce-auction' ); ?></p>
	
	<p><a href="<?php echo esc_url($checkout_url); ?>" class="button alt">
	<?php echo esc_html( apply_filters( 'ultimate_woocommerce_auction_pay_now_button_text', __( 'Pay Now', 'ultimate-woocommerce-auction' ), $product ) ); ?>
	</a></p>   
<?php endif; ?>
