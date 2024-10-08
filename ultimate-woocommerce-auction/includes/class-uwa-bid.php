<?php

/**
 * Notes  Bid Class
 *
 * @package Ultimate Auction For WooCommerce
 * @author Nitesh Singh
 * @since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class UWA_Bid {
	public $bid;
	/**
	 * Constructor for Loads options and hooks in the init method.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ), 5 );
	}
	/**
	 * Load bid data while WordPress init and hooks in method.
	 */
	public function init() {
	}
	/**
	 * Place bid On Auction Product
	 *
	 * @param string Product id and Bid Value
	 * @return bool
	 */
	public function uwa_bidplace( $product_id, $bid ) {

		global $product_data;
		global $sitepress;

		$history_bid_id = false;

		/* For WPML Support - start */
		if ( function_exists( 'icl_object_id' ) && is_object( $sitepress ) && method_exists( $sitepress, 'get_default_language' ) ) {

			$product_id = icl_object_id(
				$product_id,
				'product',
				false,
				$sitepress->get_default_language()
			);
		}
		/* For WPML Support - end */

		$product_data = wc_get_product( $product_id );
		$post_obj     = get_post( $product_id ); // The WP_Post object
		$post_author  = $post_obj->post_author; // <=== The post author ID

		if ( ! is_user_logged_in() ) {

			$my_account_page_url = get_permalink( wc_get_page_id( 'myaccount' ) );

			$uwa_login_register_msg = get_option( 'uwa_login_register_msg_enabled' );

			if ( $uwa_login_register_msg == 'no' ) {
					wp_redirect( $my_account_page_url );
					exit;
			} else {

				wc_add_notice(
					sprintf(
						__( 'Please Login/Register in to place your bid or buy the product. <a href="%s" target="_blank" class="button">Login/Register &rarr;</a>', 'ultimate-woocommerce-auction' ),
						$my_account_page_url
					),
					'error'
				);
				return false;
			}
		}

		$uwa_allow_admin_to_bid = get_option( 'uwa_allow_admin_to_bid', 'no' );
		$uwa_allow_owner_to_bid = get_option( 'uwa_allow_owner_to_bid', 'no' );

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			/* for administrator role only */
			if ( current_user_can( 'administrator' ) ) {
				if ( $uwa_allow_admin_to_bid == 'no' && $current_user->ID == $post_author ) {
					wc_add_notice( sprintf( __( 'Sorry, you can not bid on your own auction product.', 'ultimate-woocommerce-auction' ) ), 'error' );
					return false;
				}
			} elseif ( $uwa_allow_owner_to_bid == 'no' && $current_user->ID == $post_author ) { /* for seller/vendor/other role  */
					wc_add_notice( sprintf( __( 'Sorry, you can not bid on your own auction product.', 'ultimate-woocommerce-auction' ) ), 'error' );
					return false;
			}
		}

		if ( $bid <= 0 ) {
			wc_add_notice( sprintf( __( 'Please enter a value greater than 0!', 'ultimate-woocommerce-auction' ), get_permalink( wc_get_page_id( 'myaccount' ) ) ), 'error' );
			return false;
		}

		// Check if auction product expired
		if ( $product_data->is_woo_ua_closed() ) {
			wc_add_notice( sprintf( __( 'This auction &quot;%s&quot; has expired', 'ultimate-woocommerce-auction' ), $product_data->get_title() ), 'error' );
			return false;
		}
		// Check Stock
		if ( ! $product_data->is_in_stock() ) {
			wc_add_notice( sprintf( __( 'You cannot place a bid for &quot;%s&quot; because the product is out of stock.', 'ultimate-woocommerce-auction' ), $product_data->get_title() ), 'error' );
			return false;
		}
		$current_user = wp_get_current_user();
		$auction_type = $product_data->get_woo_ua_auction_type();

		if ( $auction_type == 'normal' ) {
			if ( $product_data->woo_ua_bid_value() <= ( $bid ) ) {

					$curent_bid = $product_data->get_woo_ua_current_bid();
					update_post_meta( $product_id, 'woo_ua_auction_current_bid', $bid );
					update_post_meta( $product_id, 'woo_ua_auction_current_bider', $current_user->ID );
					update_post_meta( $product_id, 'woo_ua_auction_bid_count', (int) $product_data->get_woo_ua_auction_bid_count() + 1 );
					$history_bid_id = $this->history_bid( $product_id, $bid, $current_user );

			} else {

				wc_add_notice( sprintf( __( 'Please enter a bid value for &quot;%1$s&quot; greater than the current bid. Your bid must be at least %2$s ', 'ultimate-woocommerce-auction' ), $product_data->get_title(), wc_price( $product_data->woo_ua_bid_value() ) ), 'error' );
				return false;
			}
		} else {
			wc_add_notice( sprintf( __( 'There was no bid Placed', 'ultimate-woocommerce-auction' ), $product_data->get_title() ), 'error' );
			return false;
		}
		do_action(
			'ultimate_woocommerce_auction_place_bid',
			array(
				'product_id' => $product_id,
				'log_id'     => $history_bid_id,
			)
		);
		return true;
	}
	/**
	 * Adding Bid Data To Log History
	 *
	 * @param string, int
	 * @return void
	 */
	public function history_bid( $product_id, $bid, $current_user, $proxy = 0 ) {

		global $wpdb;
		$history_bid_id = false;
		$history_bid    = $wpdb->insert(
			$wpdb->prefix . 'woo_ua_auction_log',
			array(
				'userid'     => $current_user->ID,
				'auction_id' => $product_id,
				'bid'        => $bid,
				'proxy'      => $proxy,
				'date'       => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%f', '%d', '%s' )
		);
		if ( $history_bid ) {
			$history_bid_id = $wpdb->insert_id;
		}

		return $history_bid_id;
	}
}
