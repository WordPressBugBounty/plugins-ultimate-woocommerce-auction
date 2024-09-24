<?php
/**
 * Extra Functions file
 *
 * @package Ultimate Auction For WooCommerce
 * @author Nitesh Singh
 * @since 1.0
 * @return void
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Auction List By User Id
 *
 * @package Ultimate Auction For WooCommerce
 * @author Nitesh Singh
 * @since 1.0
 * @return void
 */
function get_uwa_wp_timezone() {
	$uwa_time_zone = wp_timezone();
	return $uwa_time_zone;
}

function get_uwa_now_date() {
	$uwa_now_date = wp_date( 'Y-m-d H:i:s', time(), get_uwa_wp_timezone() );
	return $uwa_now_date;
}

function get_woo_ua_auction_by_user( $user_id ) {

	global $wpdb, $woocommerce;
	global $product;

	$tbl_log = $wpdb->prefix . 'woo_ua_auction_log';
	// Prepare the cache key for the first query
	$cache_key = md5( 'auction_by_user_' . $user_id );
	$results   = wp_cache_get( $cache_key, 'woo_ua_auction_logs' );

	if ( false === $results ) {
		// Fetch auction data from the database
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT auction_id, MAX(bid) AS max_bid FROM {$tbl_log} WHERE userid = %d GROUP BY auction_id ORDER BY MAX(date) DESC",
			absint( $user_id )
		));

		// Cache the results if they exist
		if ( $results ) {
			wp_cache_set( $cache_key, $results, 'woo_ua_auction_logs' );
		}
	}

	// Ensure results are valid before looping
	if ( ! is_array( $results ) && ! is_object( $results ) ) {
		return []; // Return empty array if no results
	}

	$new_auctionlist = [];

	foreach ( $results as $var ) {
		// Prepare the cache key for individual auction bids
		$cache_key_2 = md5( 'auction_bid_' . $var->auction_id . '_' . $user_id );
		$max_bid     = wp_cache_get( $cache_key_2, 'woo_ua_auction_logs_2' );

		if ( false === $max_bid ) {
			// Fetch the highest bid for the specific auction and user
			$max_bid = $wpdb->get_var( $wpdb->prepare(
				"SELECT bid FROM {$tbl_log} WHERE auction_id = %d AND userid = %d ORDER BY CAST(bid AS decimal(50,5)) DESC, date ASC LIMIT 1",
				absint( $var->auction_id ), absint( $user_id )
			));

			// Cache the highest bid
			if ( $max_bid ) {
				wp_cache_set( $cache_key_2, $max_bid, 'woo_ua_auction_logs_2' );
			}
		}

		// Assign the max_bid value
		$var->max_bid = $max_bid;

		// Get the product object
		$product = wc_get_product( $var->auction_id );

		if ( is_object( $product ) && method_exists( $product, 'get_type' ) && $product->get_type() === 'auction' ) {
			$product_status = $product->get_status();

			if ( $product_status === 'publish' ) {
				$new_auctionlist[] = $var;
			}
		}

		// Optionally delete the cache for this individual auction bid
		wp_cache_delete( $cache_key_2, 'woo_ua_auction_logs_2' );
	}

	// Optionally delete the cache for the main query results
	wp_cache_delete( $cache_key, 'woo_ua_auction_logs' );

	return $new_auctionlist;
}

/**
 * Get Auction WatchList By User Id
 *
 * @package Ultimate WooCommerce Auction
 * @author Nitesh Singh
 * @since 1.0
 * @return void
 */
function get_woo_ua_auction_watchlist_by_user( $user_id ) {

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

	$results       = get_user_meta( $user_id, 'woo_ua_auction_watch' );
	$new_watchlist = array();
	if ( count( $results ) > 0 ) {
		foreach ( $results as $key => $value ) {
			$product = wc_get_product( $value );
			if ( is_object( $product ) && method_exists( $product, 'get_type' ) && $product->get_type() == 'auction' ) {
				$product_status = $product->get_status();
				if ( $product_status == 'publish' ) {
					$new_watchlist[] = $value;
				}
			}
		}
	}

	return $new_watchlist;
}
/**
 * Get Url For checkout
 *
 * @package Ultimate WooCommerce Auction
 * @author Nitesh Singh
 * @since 1.0
 * @return void
 */
function woo_ua_auction_get_checkout_url() {

	$checkout_page_id = wc_get_page_id( 'checkout' );

	$checkout_url = '';

	if ( $checkout_page_id ) {
		if ( is_ssl() || get_option( 'woocommerce_force_ssl_checkout' ) == 'yes' ) {

			$checkout_url = str_replace( 'http:', 'https:', get_permalink( $checkout_page_id ) );

		} else {
			$checkout_url = get_permalink( $checkout_page_id );
		}
	}
	return apply_filters( 'woocommerce_get_checkout_url', $checkout_url );
}
/**
 * Bid Placed Message
 *
 * @package Ultimate WooCommerce Auction
 * @author Nitesh Singh
 * @since 1.0
 * @return void
 */
function uwa_bid_place_message( $product_id ) {

	global $woocommerce;
	$product_data = wc_get_product( $product_id );
	$current_user = wp_get_current_user();
	if ( $current_user->ID == $product_data->get_woo_ua_auction_current_bider() ) {

		if ( ! $product_data->is_woo_ua_reserve_met() ) {
			/* Translators: This string dispaly Bid messgae.*/
			$message = sprintf( __( 'Your Bid Placed Successfully for &quot;%s&quot;.', 'ultimate-woocommerce-auction' ), $product_data->get_title() );

		} elseif ( $product_data->get_woo_ua_auction_max_bid() ) {
				/* Translators: This string dispaly Bid messgae.*/
				$message = sprintf( __( 'Your Bid has been placed successfully for &quot;%1$s&quot;! Your max bid is %2$s.', 'ultimate-woocommerce-auction' ), $product_data->get_title(), wc_price( $product_data->get_woo_ua_auction_max_bid() ) );

		} else {
			/* Translators: This string dispaly Bid messgae.*/
			$message = sprintf( __( 'Your Bid has been placed successfully for &quot;%s&quot;!', 'ultimate-woocommerce-auction' ), $product_data->get_title() );
		}
	} else {
		/* Translators: This string dispaly Bid messgae.*/
		$message = sprintf( __( 'Your bid has been placed successfully but you have been outbid for &quot;%s&quot;!', 'ultimate-woocommerce-auction' ), $product_data->get_title() );
	}
	wc_add_notice( apply_filters( 'ultimate_woocommerce_auction_bid_place_message', $message, $product_id ) );
}
if ( ! function_exists( 'wc_get_price_decimals' ) ) {
	function wc_get_price_decimals() {
		return absint( get_option( 'wc_price_num_decimals', 2 ) );
	}
}

// user display name
function uwa_user_display_name( $user_id ) {

	/*
	$uwa_bid_display_username = get_option('uwa_bid_display_username');
	$uwa_disable_display_user_name = get_user_meta($user_id, 'uwa_disable_display_user_name', true);
	$c_user_id = get_current_user_id();
	if (current_user_can('administrator') or current_user_can('shop_manager')) {
		$user_name = get_userdata($user_id)->display_name;
	}
	elseif($uwa_disable_display_user_name ==0  && $user_id != $c_user_id){
		$no_user_name = get_userdata($user_id)->display_name;
		$user_name = str_repeat("*", strlen($no_user_name));
	}
	else {
		$user_name = get_userdata($user_id)->display_name;
	}*/

	$user_name = get_userdata( $user_id )->display_name;
	return $user_name;
}

// list bidders Ajax callback - 'See More' link on 'Your Auctions/User Auctions' pages
function uwa_see_more_bids_ajax_callback() {
	global $wpdb;

	$tbl_log        = $wpdb->prefix . 'woo_ua_auction_log';
	$datetimeformat = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

	if ( ! isset( $_POST['ua_nonce'] ) || ! wp_verify_nonce( $_POST['ua_nonce'], 'uwaajax-nonce' ) ) {
		wp_send_json_error( 'Nonce verification failed.' );
	}

	if ( sanitize_key( $_POST['show_rows'] == -1 ) ) {
		/*$query_bidders = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$_POST['auction_id'].' ORDER BY date DESC';*/

		$query_bidders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_ua_auction_log WHERE auction_id = %d ORDER BY date DESC", absint( $_POST['auction_id'] ) ) );

		$response['uwa_label_text'] = __( 'See less.', 'ultimate-woocommerce-auction' );

	} else {
		/*$query_bidders = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$_POST['auction_id'].' ORDER BY date DESC LIMIT 2';*/

		$query_bidders              = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_ua_auction_log WHERE auction_id = %d ORDER BY date DESC LIMIT 2", absint( $_POST['auction_id'] )) );
		$response['uwa_label_text'] = __( 'See more', 'ultimate-woocommerce-auction' );
	}

	$cache_key_1 = md5( 'number-gen' ); // Generate a unique cache key based on the query

	$results = wp_cache_get( $cache_key_1, 'woo_ua_auction_logs_query_bidders' );

	if ( false === $results ) {
		// Execute query to fetch results using $wpdb->get_results()
		$results =$query_bidders;

		// Cache the results
		if ( $results ) {
			wp_cache_set( $cache_key_1, $results, 'woo_ua_auction_logs_query_bidders' );
		}
	}

	$results     = $query_bidders;
	$row_bidders = '';
	if ( ! empty( $results ) ) {

		foreach ( $results as $result ) {

				$userid      = $result->userid;
				$userdata    = get_userdata( $userid );
				$bidder_name = $userdata->user_nicename;
			if ( $userdata ) {

				$bidder_name = "<a href='" . esc_url( get_edit_user_link( $userid ) ) . "' target='_blank'>" . $bidder_name . '</a>';

			} else {
				$bidder_name = 'User id:' . $userid;
			}

				$bid_amt      = wc_price( $result->bid );
				$bid_time     = mysql2date( $datetimeformat, $result->date );
				$row_bidders .= '<tr>';
				$row_bidders .= '<td>' . $bidder_name . ' </td>';
				$row_bidders .= '<td>' . $bid_amt . '</td>';
				$row_bidders .= '<td>' . $bid_time . '</td>';
				$row_bidders .= '</tr>';

		}

			$row_bidders_final     = $row_bidders;
			$response['bids_list'] = $row_bidders_final;

	}

		/*echo json_encode( $response );*/
		echo wp_json_encode( $response );
		exit;
}
add_action( 'wp_ajax_uwa_see_more_bids_ajax', 'uwa_see_more_bids_ajax_callback' );
add_action( 'wp_ajax_nopriv_uwa_see_more_bids_ajax', 'uwa_see_more_bids_ajax_callback' );
