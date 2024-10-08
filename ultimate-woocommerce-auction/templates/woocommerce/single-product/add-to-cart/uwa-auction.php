<?php
/**
 * Auction product add to cart
 */
/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce, $product, $post;
if ( ! $product->is_purchasable() ) {
	return;
}
if ( $product->is_woo_ua_closed() ) {
	return;
}
if ( ! $product->is_sold_individually() ) {
	return;
}
if ( $product->is_in_stock() ) : ?>
	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>
<form class="buy-now cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo esc_attr( $post->ID ); ?>">
	<?php
		do_action( 'woocommerce_before_add_to_cart_button' );
	if ( ! $product->is_sold_individually() ) {
					woocommerce_quantity_input(
						array(
							'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
							'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product ),
						)
					);
	}
	?>
	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
	<button type="submit" class="single_add_to_cart_button button alt">
		<?php // Translators: Placeholder %s represents the Buy Now text. ?>
		<?php echo wp_kses_post( apply_filters( 'single_add_to_cart_text', sprintf( __( 'Buy Now %s', 'ultimate-woocommerce-auction' ), wc_price( $product->get_regular_price() ) ), $product ) ); ?>
	</button>
	<div>
		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $post->ID ); ?>" />
	</div>
	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>
	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
<?php endif; 
