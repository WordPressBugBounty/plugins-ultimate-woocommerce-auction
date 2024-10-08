<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$user_id     = get_current_user_id();
$my_auctions = get_woo_ua_auction_by_user( $user_id );

if ( count( $my_auctions ) > 0 ) { ?>

<table class="shop_table shop_table_responsive">
	<tr>
		<th class="toptable"><?php esc_html_e( 'Image', 'ultimate-woocommerce-auction' ); ?></td>
		<th class="toptable"><?php esc_html_e( 'Product', 'ultimate-woocommerce-auction' ); ?></td>
		<th class="toptable"><?php esc_html_e( 'Your bid', 'ultimate-woocommerce-auction' ); ?></td>
		<th class="toptable"><?php esc_html_e( 'Current bid', 'ultimate-woocommerce-auction' ); ?></td>
		<th class="toptable"><?php esc_html_e( 'Status', 'ultimate-woocommerce-auction' ); ?></td>
	</tr>
	<?php
	foreach ( $my_auctions as $my_auction ) {
		global $product;
		global $sitepress;
		$product_id = $my_auction->auction_id;
		/* For WPML Support - start */
		if ( function_exists( 'icl_object_id' ) && is_object( $sitepress ) && method_exists( $sitepress, 'get_current_language' ) ) {
			$product_id = icl_object_id( $product_id, 'product', false, $sitepress->get_current_language() );
		}
		/* For WPML Support - end */

		$product = wc_get_product( $product_id );

		if ( is_object( $product ) && method_exists( $product, 'get_type' ) && $product->get_type() == 'auction' ) {
			$product_name = get_the_title( $product_id );
			$product_url  = get_the_permalink( $product_id );
			$thumbnail    = $product->get_image( 'thumbnail' );
			$nonce = wp_create_nonce( 'uwa_add_to_cart_nonce' );

			$checkout_url = add_query_arg(
			    array(
			        'pay-uwa-auction' => absint( $product->get_id() ), // Product ID, cast to integer for extra safety
					'nonce'           => sanitize_text_field( $nonce ) // Sanitize the nonce value for security
			    ),
			    woo_ua_auction_get_checkout_url() // Base URL for checkout
			);
			?>
		<tr>            
			<td><?php echo wp_kses_post( $thumbnail ); ?></td>
			<td><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_attr( $product_name ); ?></a></td>
			<td><?php echo wp_kses_post( wc_price( $my_auction->max_bid ) ); ?></td>
			<td><?php echo wp_kses_post( $product->get_price_html() ); ?></td>
			<?php
			if ( ( $user_id == $product->get_woo_ua_auction_current_bider() && $product->get_woo_ua_auction_closed() == '2' && ! $product->get_woo_ua_auction_payed() ) ) {
				?>
				<td><a href="<?php echo esc_url( $checkout_url ); ?>" class="button alt"><?php esc_html_e( 'Pay Now', 'ultimate-woocommerce-auction' ); ?></a></td>
			<?php } elseif ( $product->is_woo_ua_closed() ) { ?> 
				<td><?php esc_html_e( 'Closed', 'ultimate-woocommerce-auction' ); ?></td>
				<?php } else { ?>
				<td><?php esc_html_e( 'Started', 'ultimate-woocommerce-auction' ); ?></td>
			<?php } ?>
		<tr>  
			<?php
		}
	}
} else {
		$shop_page_id  = wc_get_page_id( 'shop' );
		$shop_page_url = $shop_page_id ? get_permalink( $shop_page_id ) : '';
	?>
				<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">		
			<a class="woocommerce-Button button" href="<?php echo esc_url( $shop_page_url ); ?>"><?php esc_html_e( 'Go shop', 'ultimate-woocommerce-auction' ); ?></a> 
			<?php esc_html_e( 'No auctions available yet.', 'ultimate-woocommerce-auction' ); ?>
		</div>               
<?php } ?> 
</table>
<?php 
