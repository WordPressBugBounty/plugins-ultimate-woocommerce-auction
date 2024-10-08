<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	/**
	* Add Cron Init setting
	*
	* @package Ultimate Auction For WooCommerce
	* @author Nitesh Singh
	* @since 1.0
	*/
		add_action( 'plugins_loaded', 'woo_ua_cron_job' );
	/**
	* callback to change the auction status
	*
	* @package Ultimate Auction For WooCommerce
	* @author Nitesh Singh
	* @since 1.0
	*/
	add_action( 'scheduled_woo_ua_update_status', 'woo_ua_update_status_callback', 10 );

	/**
	* Delete all cron
	*
	* @package Ultimate Auction For WooCommerce
	* @author Nitesh Singh
	* @since 1.0
	*/
	add_action( 'scheduled_woo_ua_delete_completed_actions', 'woo_ua_delete_completed_actions_callback', 10 );

	/**
	 * Cron Init While plugin Load
	 *
	 * @package Ultimate Auction For WooCommerce
	 * @author Nitesh Singh
	 * @since 1.0
	 */
function woo_ua_cron_job() {

		$args             = array(
			'type'   => 'auction',
			'status' => 'publish',
		);
		$query            = new WC_Product_Query( $args );
		$auction_products = $query->get_products();
		$cron_check       = get_option( 'woo_ua_cron_initiated' );
		if ( ! empty( $auction_products ) ) {
			if ( ! $cron_check ) {
				woo_ua_cron_initiate( $configured = false );
			}
			if ( $cron_check ) {
				woo_ua_cron_initiate( $configured = true );
			}
		} elseif ( ! $cron_check ) {
			woo_ua_cron_initiate( $configured = false );
		}
}

	/**
	 * Cron Iinitiate
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
function woo_ua_cron_initiate( $configured ) {

		// configuration for cron 1
		$update_status_interval = get_option( 'woo_ua_cron_auction_status' );

	if ( isset( $update_status_interval ) && ! empty( $update_status_interval ) ) {

		$update_interval = $update_status_interval;

	} else {

		$update_interval = 2;
	}
		$cron_1_interval = woo_ua_minutes_converter( $update_interval );

		// configuration for cron 2
		$sent_email_interval = get_option( 'woo_ua_cron_auction_email' );

	if ( isset( $sent_email_interval ) && ! empty( $sent_email_interval ) ) {

		$sent_interval = $sent_email_interval;
	} else {
		$sent_interval = 4;
	}
		$cron_2_interval = woo_ua_minutes_converter( $sent_interval );
	if ( $configured ) {
		woo_ua_delete_prv_actions();
	} else {
		update_option( 'woo_ua_cron_initiated', 'yes' );
	}

		wc_schedule_recurring_action( strtotime( time() ), $cron_1_interval, 'scheduled_woo_ua_update_status' );
		wc_schedule_recurring_action( strtotime( time() ), '43200', 'scheduled_woo_ua_delete_completed_actions' );
}
	/**
	 * Delete Cron While pending
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
function woo_ua_delete_prv_actions() {
	// find sheduler posts
	$args1                           = array(
		'posts_per_page' => -1,
		'post_title'     => 'scheduled_woo_ua_update_status',
		'post_type'      => 'scheduled-action',
		'post_status'    => 'pending',
		'fields'         => 'ids',
	);
	$scheduled_update_status_actions = get_posts( $args1 );
	// echo '<pre>'.print_r($scheduled_update_status_actions,1).'</pre>';exit;
	if ( ! empty( $scheduled_update_status_actions ) ) {
		foreach ( $scheduled_update_status_actions as $auc_id ) {
			wp_delete_post( $auc_id );
		}
	}

	do_action( 'woo_ua_reset_other_scheduled_events' ); // delete other cron scheduled if added
}
	/**
	 * Cron checking And make close auction and send mail
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
function woo_ua_update_status_callback() {
	global $wpdb;
	set_time_limit( 0 );

	ignore_user_abort( 1 );

	global $woocommerce;

	$num_of_auctions = get_option( 'woo_ua_cron_auction_status_number', true );
	if ( isset( $num_of_auctions ) && ! empty( $num_of_auctions ) ) {
		$auc_no = $num_of_auctions;
	} else {
		$auc_no = 25;
	}
						$args = array(
							'post_type'            => 'product',
							'posts_per_page'       => $auc_no,
							'meta_query'           => array(
								'relation' => 'AND', // Optional, defaults to "AND"
								array(
									'key'     => 'woo_ua_auction_closed',
									'compare' => 'NOT EXISTS',
								),
								array(
									'key'     => 'woo_ua_auction_end_date',
									'compare' => 'EXISTS',
								),
							),
							'meta_key'             => 'woo_ua_auction_end_date',
							'orderby'              => 'meta_value',
							'order'                => 'ASC',
							'tax_query'            => array(
								array(
									'taxonomy' => 'product_type',
									'field'    => 'slug',
									'terms'    => 'auction',
								),
							),
							'auction_arhive'       => true,
							'show_past_auctions'   => true,
							'show_future_auctions' => true,
						);

						$the_query = new WP_Query( $args );
						$time      = microtime( 1 );
						if ( $the_query->have_posts() ) {
							while ( $the_query->have_posts() ) :
								$the_query->the_post();
								$product_data = wc_get_product( $the_query->post->ID );
								if ( method_exists( $product_data, 'get_type' ) && $product_data->get_type() == 'auction' ) {
									$product_data->is_woo_ua_closed();
								}
								endwhile;
						}
}
	/**
	 * Delete all Action while cron done
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
function woo_ua_delete_completed_actions_callback() {
	$status_args = array(
		'hook'     => 'scheduled_woo_ua_update_status',
		'per_page' => -1,
		'status'   => 'complete',
		'orderby'  => 'date',
		'order'    => 'ASC',
	);
	$status_arr  = wc_get_scheduled_actions( $status_args, 'ids' );
	if ( ! empty( $status_arr ) ) {
		foreach ( $status_arr as $pid ) {
				wp_delete_post( $pid );
		}
	}
		$complete_args = array(
			'hook'     => 'scheduled_woo_ua_delete_completed_actions',
			'per_page' => -1,
			'status'   => 'complete',
			'orderby'  => 'date',
			'order'    => 'ASC',
		);

		$complete_arr = wc_get_scheduled_actions( $complete_args, 'ids' );
		if ( ! empty( $complete_arr ) ) {
			foreach ( $complete_arr as $pid ) {
				wp_delete_post( $pid );
			}
		}
}

	/**
	 * Function For Converting min To sec
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
function woo_ua_minutes_converter( $interval ) {
	return $interval * 60; // coverting into sec
}
