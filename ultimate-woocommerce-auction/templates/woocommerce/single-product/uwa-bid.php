<?php
/**
 * Auction Product Bid Area
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce, $product, $post;
if ( ! ( method_exists( $product, 'get_type' ) && $product->get_type() == 'auction' ) ) {
	return;
}
$curent_bid                   = $product->get_woo_ua_auction_current_bid();
$current_user                 = wp_get_current_user();
$product_id                   = $product->get_id();
$user_max_bid                 = $product->get_woo_ua_user_max_bid( $product_id, $current_user->ID );
$gmt_offset                   = get_option( 'gmt_offset' ) > 0 ? '+' . get_option( 'gmt_offset' ) : get_option( 'gmt_offset' );
$timezone_string              = get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : __( 'UTC ', 'ultimate-woocommerce-auction' ) . $gmt_offset;
$uwa_enable_bid_place_warning = get_option( 'uwa_enable_bid_place_warning' );
$uwa_product_condition        = $product->get_woo_ua_condition();

?>
<p class="uwa_auction_condition">
	<strong>
		<?php esc_html_e( 'Item condition:', 'ultimate-woocommerce-auction' ); ?>
	</strong>

	<span class="uwa_auction_current_condition">
		<?php echo esc_attr( $uwa_product_condition ); ?>
	</span>
</p>
	
<?php if ( ( $product->is_woo_ua_closed() === false ) and ( $product->is_woo_ua_started() === true ) ) : ?>

	<div class="uwa_auction_time" id="uwa_auction_countdown">
			<strong>
				<?php esc_html_e( 'Time Left:', 'ultimate-woocommerce-auction' ); ?>
			</strong>
			<div class="uwa-main-auction-product uwa_auction_product_countdown" data-time="<?php echo esc_attr( $product->get_woo_ua_remaining_seconds() ); ?>" data-auction-id="<?php echo esc_attr( $product_id ); ?>" 
			data-format="<?php echo esc_attr( get_option( 'woo_ua_auctions_countdown_format' ) ); ?>"></div>
	</div>	

	<div class='uwa_auction_product_ajax_change' >
	
		<p class="uwa_auction_end_time">
			<strong><?php esc_html_e( 'Ending On:', 'ultimate-woocommerce-auction' ); ?></strong>
			<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $product->get_woo_ua_auctions_end_time() ) ) ); ?>  
			<?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $product->get_woo_ua_auctions_end_time() ) ) ); ?>
			
		</p>
		
		<p class="uwa_auction_product_timezone">
			<strong><?php esc_html_e( 'Timezone:', 'ultimate-woocommerce-auction' ); ?></strong>
			<?php echo esc_attr( $timezone_string ); ?>
		</p>
			<div class="checkreserve">
		<?php if ( ( $product->is_woo_ua_reserved() === true ) && ( $product->is_woo_ua_reserve_met() === false ) ) { ?>
			<?php $reserve_text = __( 'price has not been met.', 'ultimate-woocommerce-auction' ); ?>
				<p class="uwa_auction_reserve_not_met">
					<?php $reserve_text_escaped = esc_html( $reserve_text ); ?>
					<?php // Translators: Placeholder %s represents the reserve text. ?>
					<strong><?php printf( esc_html__( 'Reserve %s', 'ultimate-woocommerce-auction' ), esc_html( $reserve_text_escaped ) ); ?></strong>
				</p>	
		<?php } ?>
	
	<?php if ( ( $product->is_woo_ua_reserved() === true ) && ( $product->is_woo_ua_reserve_met() === true ) ) { ?>
			<?php $reserve_text = __( 'price has been met.', 'ultimate-woocommerce-auction' ); ?>
			<p class="uwa_auction_reserve_met">
				<?php $reserve_text_escaped = esc_html( $reserve_text ); ?>
				<?php // Translators: Placeholder %s represents the reserve text. ?>
				<strong><?php printf( esc_html__( 'Reserve %s', 'ultimate-woocommerce-auction' ), esc_html( $reserve_text_escaped ) ); ?></strong>
			</p>
	<?php } ?>
	</div>
	
	<?php do_action( 'ultimate_woocommerce_auction_before_bid_form' ); ?>
	
	<form class="uwa_auction_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo esc_attr( $product_id ); ?>">
		<?php do_action( 'ultimate_woocommerce_auction_before_bid_button' ); ?>
		<input type="hidden" name="bid" value="<?php echo esc_attr( $product_id ); ?>" />
			<div class="quantity buttons_added">
				<!-- <label for="uwa_your_bid"><?php esc_html_e( 'Bid Value', 'ultimate-woocommerce-auction' ); ?>:</label>-->
				<span class="uwa_currency"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
				<input type="number" name="uwa_bid_value" id="uwa_bid_value" data-auction-id="<?php echo esc_attr( $product_id ); ?>"
				value=""min="<?php echo esc_attr( $product->woo_ua_bid_value() ); ?>"  
				step="any" size="<?php echo intval( strlen( $product->get_woo_ua_current_bid() ) + 2 ); ?>" title="bid"  class="input-text qty  bid text left">
			</div>	
		<button type="submit" class="bid_button button alt" id="placebidbutton">
		<?php echo esc_html( apply_filters( 'ultimate_woocommerce_auction_bid_button_text', __( 'Place Bid', 'ultimate-woocommerce-auction' ), $product ) ); ?>	
		</button>
		<div class="uwa_inc_price_hint" >		
		<small class="uwa_inc_price">(<?php esc_html_e( 'Enter more than or equal to', 'ultimate-woocommerce-auction' ); ?> : </small>
		<small class="uwa_inc_latest_price uwa_inc_price_ajax_<?php echo esc_attr( $product_id ); ?>">
		<?php echo wp_kses_post( wc_price( $product->woo_ua_bid_value() ) ); ?> )</small>		
		</div>		
		
		<input type="hidden" name="uwa-place-bid" value="<?php echo esc_attr( $product_id ); ?>" />
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>" />
		<?php if ( is_user_logged_in() ) { ?>
			<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>" />
		<?php } ?> 
		<?php 
		/* If user clicks to ignore the notice, add that to their user meta */
		$uwa_uwa_place_bid_nonce = wp_create_nonce( 'uwa_uwa_place_bid' );
		
		?>
		<input type="hidden" name="uwa_uwa_place_bid_nonce" value="<?php echo esc_attr( $uwa_uwa_place_bid_nonce ); ?>" />

		
	<?php do_action( 'ultimate_woocommerce_auction_after_bid_button' ); ?>
		
	</form>
	
	<?php do_action( 'ultimate_woocommerce_auction_after_bid_form' ); ?>
	
	</div>
<?php endif; ?>

	<?php if ( $product->get_woo_ua_auction_fail_reason() == '1' ) { ?>
		
	<p class="expired">	<?php esc_html_e( 'Auction Expired because there were no bids', 'ultimate-woocommerce-auction' ); ?>  </p>
		 
	<?php } elseif ( $product->get_woo_ua_auction_fail_reason() == '2' ) { ?>
		
	<p class="reserve_not_met"> <?php esc_html_e( 'Auction expired without reaching reserve price', 'ultimate-woocommerce-auction' ); ?> </p>
		
	<?php } ?>
</p>

<script type="text/javascript">

	jQuery("document").ready(function($){

		$("#placebidbutton").on('click', function(event){
			
			var formname = "custombid";
			retval = bid_check(formname);
			
			if(retval == true || retval == false){				
				return retval;
			}
		});

		function bid_check(formname){			

			var id_Bid;

			id_Bid = "#uwa_bid_value";
		

				var bidval = parseFloat($(id_Bid).val());

				if(bidval){

					var minval = parseFloat($(id_Bid).attr("min"));
				var maxval = parseFloat($(id_Bid).attr("max"));

				if(minval <= bidval){

					<?php

					$uwa_enable_bid_place_warning = esc_attr( get_option( 'uwa_enable_bid_place_warning' ) );

					if ( $uwa_enable_bid_place_warning == 'yes' ) {
						?>

							confirm_bid(formname, id_Bid);

						<?php
					}

					?>
				}
			}

		}

		/* Extra confirmation message on place bid */
		function  confirm_bid(formname, id_Bid) {

			/* Get bid value, format value and then add to confirm message */
			var bidval = jQuery(id_Bid).val();
			var bidval = parseFloat(bidval);

			if (bidval > 0){
				
				var floatbidval = bidval.toFixed(2); 
			
				var currencyval = "<?php echo esc_js( html_entity_decode( get_woocommerce_currency_symbol(), ENT_COMPAT | ENT_HTML401, 'UTF-8' ) ); ?>";

				var finalval = currencyval + floatbidval;

				var confirm1 = '<?php echo esc_html__( 'Do you really want to bid', 'ultimate-woocommerce-auction' ); ?>';

				var confirm_message = confirm1 + ' ' + finalval + ' ?';

				var result_conf = confirm(confirm_message);

				if(result_conf == false){
					event.preventDefault(); /* don't use return it reloads page */
				}
				else{
					return true;
				}
			}
			
		} /* end of function - confirm_bid() */

	}); /* end of document ready */

</script>