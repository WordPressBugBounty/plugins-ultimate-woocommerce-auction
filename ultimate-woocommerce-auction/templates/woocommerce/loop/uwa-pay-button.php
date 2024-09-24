<?php
/**
 * Loop Add to Cart
 *
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     1.6.4
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;
if ( method_exists( $product, 'get_type' ) && $product->get_type() == 'auction' ) :
	$user_id = get_current_user_id();

	$nonce = wp_create_nonce( 'uwa_add_to_cart_nonce' );

	$checkout_url = add_query_arg(
	    array(
	        'pay-uwa-auction' => absint( $product->get_id() ), // Product ID, cast to integer for extra safety
			'nonce'           => sanitize_text_field( $nonce ) // Sanitize the nonce value for security
	    ),
	    woo_ua_auction_get_checkout_url() // Base URL for checkout
	);

	if ( $user_id == $product->get_woo_ua_auction_current_bider() && $product->get_woo_ua_auction_closed() == '2' && ! $product->get_woo_ua_auction_payed() ) { ?>
		<a href="<?php echo esc_url($checkout_url); ?>" class="button"><?php esc_html_e( 'Pay Now', 'ultimate-woocommerce-auction' ); ?></a>
	<?php } ?>
<?php endif; ?>