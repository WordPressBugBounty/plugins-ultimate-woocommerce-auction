<?php

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
== NOTICE ===================================================================
 * Please do not alter this file. Instead: make a copy of the entire plugin,
 * rename it, and work inside the copy. If you modify this plugin directly and
 * an update is released, your changes will be lost!
 * ========================================================================== */



/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */
$nonce = wp_create_nonce( 'uwa_plugin_nonce' );
if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'uwa_plugin_nonce' ) ) {
	wp_send_json_error( 'Nonce verification failed.' );
}
$auction_type = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';
class Woo_Ua_Logs_List_Table extends WP_List_Table {

	public $allData;
	public $auction_type;

	public function uwa_auction_get_data( $per_page, $page_number ) {
		global $sitepress;
		$uwa_uwa_auction_get_data_nonce = wp_create_nonce( 'uwa_uwa_auction_get_data_nonce' );
		if ( ! isset( $uwa_uwa_auction_get_data_nonce ) || ! wp_verify_nonce( $uwa_uwa_auction_get_data_nonce, 'uwa_uwa_auction_get_data_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed.' );
		}
		$pagination   = ( (int) $page_number - 1 ) * (int) $per_page;
		$search       = ( isset( $_POST['s'] ) ) ? sanitize_key( $_POST['s'] ) : '';
		$auction_type = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';
		$meta_query   = array(
			'relation' => 'AND',
			array(
				'key'     => 'woo_ua_auction_closed',
				'compare' => 'NOT EXISTS',
			),
		);

		if ( $auction_type == 'expired' ) {
			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'     => 'woo_ua_auction_closed',
					'value'   => array( '1', '2', '3', '4' ),
					'compare' => 'IN',
				),
			);
		}

		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $per_page,
			'offset'              => $pagination,
			's'                   => $search,
			'meta_key'            => 'woo_ua_auction_last_activity',
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
			'meta_query'          => array( $meta_query ),
			'tax_query'           => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'auction',
				),
			),
			'auction_arhive'      => true,
		);

		/* For WPML Support - start */
		$filter_id = ( isset( $_REQUEST['uwa_auction_id'] ) ) ? absint( $_REQUEST['uwa_auction_id'] ) : '';
		if ( $filter_id != '' ) {
			$args['p'] = $filter_id;
		}

		if ( function_exists( 'icl_object_id' ) && is_object( $sitepress ) && method_exists(
			$sitepress,
			'get_current_language'
		) ) {

			$args['suppress_filters'] = 0;
		}
		/* For WPML Support - end */

		$auction_item_array = get_posts( $args );
		$data_array         = array();
		foreach ( $auction_item_array as $single_auction ) {

			global $wpdb;
			$datetimeformat = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			$row            = array();
			$auction_ID     = intval( $single_auction->ID );
			/*$auction_title = $single_auction->post_title;*/
			$row['title'] = '<a href="' . get_permalink( $auction_ID ) . '">' . get_the_title( $auction_ID ) . '</a>';

			$create_date        = get_post_meta( $auction_ID, 'woo_ua_auction_start_date', true );
			$row['create_date'] = mysql2date( $datetimeformat, $create_date );

			$ending_date        = get_post_meta( $auction_ID, 'woo_ua_auction_end_date', true );
			$row['ending_date'] = mysql2date( $datetimeformat, $ending_date );

			$opening_price        = get_post_meta( $auction_ID, 'woo_ua_opening_price', true );
			$current_bid_price    = get_post_meta( $auction_ID, 'woo_ua_auction_current_bid', true );
			$row['opening_price'] = wc_price( $opening_price );
			if ( ! empty( $current_bid_price ) ) {
				$row['opening_price'] = wc_price( $opening_price ) . ' / ' . wc_price( $current_bid_price );
			}

			$row['bidders'] = '';
			$results        = array();
			$row_bidders    = '';

			/*$query_bidders = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$single_auction->ID.' ORDER BY id DESC LIMIT 2';*/

			$tbl_log = $wpdb->prefix . 'woo_ua_auction_log';
			/*
			$query_bidders = $wpdb->prepare("SELECT * FROM $tbl_log WHERE auction_id = %d ORDER BY id DESC LIMIT 2", $single_auction->ID);
			$results = $wpdb->get_results($query_bidders);*/

			$cache_key = 'bidders_' . $auction_ID;
			$results   = wp_cache_get( $cache_key, 'woo_ua_auction_logs' );

			if ( false === $results ) {
				$tbl_log = $wpdb->prefix . 'woo_ua_auction_log';
				// Query to fetch bidders from database
				
				// Execute query using $wpdb->get_results()
				$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}woo_ua_auction_log WHERE auction_id = %d ORDER BY id DESC LIMIT 2",absint($auction_ID)) );

				// Cache the results
				if ( $results ) {
					wp_cache_set( $cache_key, $results, 'woo_ua_auction_logs' );
				}
			}

			if ( ! empty( $results ) ) {

				foreach ( $results as $result ) {

					$userid = $result->userid;

					/* $userdata    = get_userdata( $userid );
					$bidder_name = $userdata->user_nicename;
					if ( $userdata ) {
						$bidder_name = "<a href='" . esc_url( get_edit_user_link( $userid ) ) . "' target='_blank'>" . $bidder_name . '</a>';

					} else {
						$bidder_name = 'User id:' . $userid;
					} */

					$obj_user = get_userdata($result->userid);
					$bidder_name = "";
					if ($obj_user) {					
						$bidder_name = $obj_user->display_name;	
					}	

	                if ($bidder_name) {
						$bidder_name = "<a href='".get_edit_user_link( $userid )."' target='_blank'>" .
							$bidder_name . "</a>";

					} else {
						// $bidder_name = 'User id:'.$result->userid;
						$bidder_name = "<a href='".get_edit_user_link( $userid )."' target='_blank'>" .
							$obj_user->user_login . "</a>";
	                } 

					$bid_amt      = wc_price( $result->bid );
					$bid_time     = mysql2date( $datetimeformat, $result->date );
					$row_bidders .= '<tr>';
					$row_bidders .= '<td>' . $bidder_name . ' </td>';
					$row_bidders .= '<td>' . $bid_amt . '</td>';
					$row_bidders .= '<td>' . $bid_time . '</td>';
					$row_bidders .= '</tr>';

				}
				// $row['bidders'] = "<div class='uwa-bidder-list-".$single_auction->ID.">";
				$row['bidders']  = "<table class='uwa-bidslist uwa-bidder-list-" . $auction_ID . "'>";
				$row['bidders'] .= $row_bidders;
				$row['bidders'] .= '</table>';

				/*$query_bidders_count = 'SELECT * FROM '.$wpdb->prefix.'woo_ua_auction_log WHERE auction_id ='.$single_auction->ID.' ORDER BY id DESC';*/

				$tbl_log             = $wpdb->prefix . 'woo_ua_auction_log';
				
				$results_count = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woo_ua_auction_log WHERE auction_id = %d ORDER BY id DESC", absint($single_auction->ID)));
				if ( count( $results_count ) > 2 ) {
						$row['bidders'] .= "
                            <a href='#' class='uwa-see-more show-all'  rel='" . $auction_ID . "' >" . __( 'See more', 'ultimate-woocommerce-auction' ) . '</a>';
				}
			} else {

				$row['bidders'] = __( 'No bids placed', 'ultimate-woocommerce-auction' );
			}

			$data_array[] = $row;
		} /* end of foreach */

		$this->allData = $data_array;
		return $data_array;
	}

	/**
	 * [REQUIRED] This method return columns to display in table
	 * you can skip columns that you do not want to show
	 * like content, or description
	 *
	 * @return array
	 */
	function get_columns() {
		$uwa_get_columns_nonce = wp_create_nonce( 'uwa_get_columns_nonce' );
		if ( ! isset( $uwa_get_columns_nonce ) || ! wp_verify_nonce( $uwa_get_columns_nonce, 'uwa_get_columns_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed.' );
		}

		$auction_type = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';
		$columns      = array(
			'title'         => __( 'Auction Title', 'ultimate-woocommerce-auction' ),
			'create_date'   => __( 'Creation Date', 'ultimate-woocommerce-auction' ),
			'ending_date'   => __( 'Ending Date', 'ultimate-woocommerce-auction' ),
			'opening_price' => __( 'Starting / Current Price', 'ultimate-woocommerce-auction' ),
			'bidders'       => __( 'Bidders Name / Bid / Time', 'ultimate-woocommerce-auction' ),

		);

		if ( $auction_type == 'expired' ) {
			$columns = array(
				'title'         => __( 'Auction Title', 'ultimate-woocommerce-auction' ),
				'create_date'   => __( 'Creation Date', 'ultimate-woocommerce-auction' ),
				'ending_date'   => __( 'End Date', 'ultimate-woocommerce-auction' ),
				'opening_price' => __( 'Starting / Final Price', 'ultimate-woocommerce-auction' ),
				'bidders'       => __( 'Bidders Name / Bid / Time', 'ultimate-woocommerce-auction' ),

			);
		}

		return $columns;
	}

	/**
	 * [OPTIONAL] This method return columns that may be used to sort table
	 * all strings in array - is column names
	 * notice that true on name column means that its default sort
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'title'         => array( 'title', true ),
			'create_date'   => array( 'create_date', true ),
			'ending_date'   => array( 'ending_date', true ),
			'opening_price' => array( 'opening_price', true ),
			'bidders'       => array( 'bidders', true ),

		);
		return $sortable_columns;
	}

	/**
	 * [REQUIRED] This is the most important method
	 *
	 * It will get rows from database and prepare them to be showed in table
	 */
	function prepare_items() {

		global $sitepress;

		$uwa_get_columns_nonce = wp_create_nonce( 'uwa_get_columns_nonce' );
		if ( ! isset( $uwa_get_columns_nonce ) || ! wp_verify_nonce( $uwa_get_columns_nonce, 'uwa_get_columns_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed.' );
		}


		$search                = ( isset( $_POST['s'] ) ) ? sanitize_key( $_POST['s'] ) : '';
		$this->auction_type    = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) :
		'live';
		$columns               = $this->get_columns();
		$hidden                = array();
		$per_page              = '';
		$current_page          = '';
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$orderby               = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'title';

		if ( $orderby === 'title' ) {
			$this->items = $this->uwa_auction_sort_array( $this->uwa_auction_get_data( $per_page, $current_page ) );
		} else {
			$this->items = $this->uwa_auction_get_data( $per_page, $current_page );
		}

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$auction_type = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';
		$meta_query   = array(
			'relation' => 'AND',
			array(
				'key'     => 'woo_ua_auction_closed',
				'compare' => 'NOT EXISTS',
			),
		);

		if ( $auction_type == 'expired' ) {
			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'     => 'woo_ua_auction_closed',
					'value'   => array( '1', '2', '3', '4' ),
					'compare' => 'IN',
				),
			);
		}

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			's'              => $search,
			'meta_key'       => 'woo_ua_auction_last_activity',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_query'     => array( $meta_query ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'auction',
				),
			),
			'auction_arhive' => true,
		);

		/* For WPML Support - start */
		$filter_id = ( isset( $_REQUEST['uwa_auction_id'] ) ) ? absint( $_REQUEST['uwa_auction_id'] ) : '';
		if ( $filter_id != '' ) {
			$args['p'] = $filter_id;
		}
		if ( function_exists( 'icl_object_id' ) && is_object( $sitepress ) && method_exists( $sitepress, 'get_current_language' ) ) {
			$args['suppress_filters'] = 0;
		}
		/* For WPML Support - end */

		$auctions    = get_posts( $args );
		$total_items = count( $auctions );
		// $this->found_data = array_slice($this->allData, (($current_page - 1) * $per_page), $per_page);

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = $this->uwa_auction_sort_array(
			$this->uwa_auction_get_data(
				$per_page,
				$current_page
			)
		);
	}


	public function get_result_e() {
		return $this->allData;
	}

	public function uwa_auction_sort_array( $args ) {
		$uwa_auction_sort_nonce = wp_create_nonce( 'uwa_auction_sort_nonce' );
		if ( ! isset( $uwa_auction_sort_nonce ) || ! wp_verify_nonce( $uwa_auction_sort_nonce, 'uwa_auction_sort_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed.' );
		}

		if ( ! empty( $args ) ) {

			$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'title';

			if ( $orderby === 'create_date' ) {

				$order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';
			} elseif ( $orderby === 'ending_date' ) {

				$order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';
			} else {
				$order = 'desc';
			}

			foreach ( $args as $array ) {
				$sort_key[] = $array[ $orderby ];
			}
			if ( $order == 'asc' ) {
				array_multisort( $sort_key, SORT_ASC, $args );
			} else {
				array_multisort( $sort_key, SORT_DESC, $args );
			}
		}

		return $args;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'create_date':
			case 'ending_date':
			case 'opening_price':
			case 'bidders':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}
} /* end of class */


	/**
	 * Auctions table
	 *
	 * @since 1.0.0
	 */
function woo_ua_list_page_handler_display() {
		// menu list
		$uwa_auction_sort_nonce = wp_create_nonce( 'uwa_auction_sort_nonce' );
		if ( ! isset( $uwa_auction_sort_nonce ) || ! wp_verify_nonce( $uwa_auction_sort_nonce, 'uwa_auction_sort_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed.' );
		}
		global $wpdb;
		$table    = new Woo_Ua_Logs_List_Table();
		$search_s = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';

	if ( $search_s ) {
		$table->prepare_items( $search_s );
	} else {
		$table->prepare_items();
	}

	?>
				
		<div class="wrap" id="uwa_auction_setID">
			<div id='icon-tools' class='icon32'></br></div>
			
			<h2 class="uwa_main_h2"><?php esc_html_e( 'Ultimate Auction for WooCommerce', 'ultimate-woocommerce-auction' ); ?>
				<span class="uwa_version_text"><?php esc_html_e( 'Version :', 'ultimate-woocommerce-auction' ); ?> 
			<?php echo esc_html( WOO_UA_VERSION ); ?></span></h2>	 
			
			<div class="get_uwa_pro">

				<!-- <a rel="nofollow" href="https://auctionplugin.net?utm_source=woo plugin&utm_medium=horizontal banner&utm_campaign=learn-more-button" target="_blank"> <img src="<?php echo esc_url( WOO_UA_ASSETS_URL ); ?>/images/UWCA_row.jpg" alt="" /> </a>
						
				<div class="clear"></div> -->
			<?php
			global $current_user;
			$user_id = $current_user->ID;

			

			/* If user clicks to ignore the notice, add that to their user meta */
			if ( isset( $_GET['uwa_pro_add_plugin_notice_ignore'] ) && '0' == absint( $_GET['uwa_pro_add_plugin_notice_ignore'] ) ) {
				update_user_meta( $user_id, 'uwa_pro_add_plugin_notice_disable', 'true', true );
			}
			if ( current_user_can( 'manage_options' ) ) {
				$user_id          = $current_user->ID;
				$user_hide_notice = get_user_meta( $user_id, 'uwa_pro_add_plugin_notice_disable', true );
				if ( $user_hide_notice != 'true' ) {
					?>
					<div class="notice notice-info">
						<div class="get_uwa_pro" style="display:flex;justify-content: space-evenly;">
							
							<?php /*<a rel="nofollow" href="https://auctionplugin.net?utm_source=woo plugin&utm_medium=admin notice&utm_campaign=learn-more-button" target="_blank"> <img src="<?php echo esc_url( WOO_UA_ASSETS_URL ); ?>/images/UWCA_row.jpg" alt="" /> </a> */ ?>

							<a rel="nofollow" href="https://auctionplugin.net/pricing/?utm_source=woo plugin&utm_medium=admin notice&utm_campaign=festive-offer-button" target="_blank"> 
								<img src="<?php echo esc_url( WOO_UA_ASSETS_URL ); ?>/images/offer-banner.jpg" alt="" /> 
							</a>

							<p class="uwa_hide_free">
							<?php
							// printf(__('<a href="%s">Hide Notice</a>', 'ultimate-woocommerce-auction'),esc_attr(add_query_arg('uwa_pro_add_plugin_notice_ignore', '0')));
							?>
							</p>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'uwa_pro_add_plugin_notice_ignore', '0' ), 'ultimate-woocommerce-auction', '_ultimate-woocommerce-auction_nonce' ) ); ?>" class="woocommerce-message-close notice-dismiss" style="position:relative;float:right;padding:9px 0px 9px 9px;text-decoration:none;"></a>									
							<div class="clear"></div>
						</div>
					</div>
						<?php
				}
			}
			?>
							</div>
			<style>
				a.uwa-highlight-btn, a.uwa-highlight-btn:focus {
						text-decoration: none!important;
						border-radius: 2px;
						display: block;
						padding: 5px 10px;
						font-size: 1.1em;
						border: none;
						box-shadow: none;
					}
				a.highlight-btn-disabled {
					color: #337ab7;
					background-color: #fff;
				}
			</style>
			<div id="uwa-auction-banner-text">	
			<?php echo wp_kses( 'If you like <a href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post" target="_blank"> our plugin working </a> with WooCommerce, please leave us a <a href="https://wordpress.org/support/plugin/ultimate-woocommerce-auction/reviews?rate=5#new-post" target="_blank">★★★★★</a> rating. A huge thanks in advance!', 'ultimate-woocommerce-auction' ); ?>	 
			</div>
			<br class="clear">
			<div style="float:left;">
				<ul class="subsubsub">
					<li>
						<a style="border: 1px solid #2271b1;" class="uwa-highlight-btn highlight-btn-disabled uwa-toggle-addons"  href="#">Your Auctions</a>
					</li>
					<li>
						<a style="border: 1px solid #2271b1;" class="uwa-highlight-btn highlight-btn-disabled uwa-toggle-addons"  href="#">User Auctions</a>
					</li>
					
				</ul>
				
				
			</div>

			<div class="uwa-action-container" style="float:right;margin-right: 10px;">
				<div class="ex-csv-btn">
					<a style="border: 1px solid #2271b1;" href="#"  class="uwa-highlight-btn highlight-btn-disabled uwa-toggle-addons">Export Expired Auctions CSV</a>
					
				</div>
			</div>
			<br class="clear">
			
			<?php
			/* $manage_setting_tab  = isset( $_REQUEST['auction_type'] ) ? esc_attr( $_REQUEST['auction_type'] ) : 'live'; */
			$manage_setting_tab = isset( $_REQUEST['auction_type'] ) ? sanitize_text_field( $_REQUEST['auction_type'] ) : 'live';

			?>
						
			<div class="uwa-action-container" style="float:right;margin-right: 10px;">
					<form action="" method="POST">					
						<input type="text" name="s" value="<?php echo esc_attr( $search_s ); ?>" />
						<input type="submit" class="button-secondary" 
							name="wdm_auction_search_submit" 							
							value="<?php esc_html_e( 'Search', 'ultimate-woocommerce-auction' ); ?>" />
					</form>
			</div>

			<ul class="subsubsub">
				<li><a href="?page=uwa_manage_auctions&auction_type=live" class="<?php echo esc_attr( $manage_setting_tab ) == 'live' ? 'current' : ''; ?>">
																							<?php
																							esc_html_e(
																								'Live Auctions',
																								'ultimate-woocommerce-auction'
																							);
																							?>
																					</a>|</li>
				<li><a href="?page=uwa_manage_auctions&auction_type=expired" class="<?php echo esc_attr( $manage_setting_tab ) == 'expired' ? 'current' : ''; ?>"><?php esc_html_e( 'Expired Auctions', 'ultimate-woocommerce-auction' ); ?></a></li>
				
			</ul><br class="clear">
			<form id="persons-table" method="GET">
			<?php $page_s = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : ''; ?>				
				<input type="hidden" name="page" value="<?php echo esc_attr( $page_s ); ?>"/>	<?php $table->display(); ?>					
			</form>
		</div>			
		
		
		<?php
}

?>
<div class="popup">
	<label class="close" for="example1">
		<img src="<?php echo esc_url( WOO_UA_ASSETS_URL ); ?>images/popup-close.png">
	</label>
	<div class="modual-popup-content">
		<img src="<?php echo esc_url( WOO_UA_ASSETS_URL ); ?>images/484964199.png">
		<h4><a href="<?php echo esc_url( $GLOBALS['uwa_update_link']['pro_link'] ); ?>" target="_blank">Unlock</a></h4>
		<h3>7+ Addon</h3>
		<a class="prem-plan-btn"href="">With Ultimate Woo Auction Pro Plans</a>
		<p class="upgrade-text">We’re sorry <br> Auction Addon are not available on <strong>"Ultimate Auction for WooCommerce"</strong>. Please upgrade to a PRO plan to unlock the Addon of your choice.</p>
		<a class="Upgrade-pro-btn" href="<?php echo esc_url( $GLOBALS['uwa_update_link']['pro_link'] ); ?>" target="_blank">Upgrade to Pro</a>
	  
	</div>
</div>

<script>
	jQuery(document).ready(function(){
	 
	jQuery(".uwa-toggle-addons").click(function(){
		jQuery(".popup").toggleClass("show");
		jQuery(".popup").show();
	});
	});
	jQuery("label.close").click(function(){
	jQuery(".popup").removeClass("show");
	jQuery(".popup").hide();
});
</script>
<style>
	.popup label.close img {
	width: 16px;
}
.modual-popup-content h4 a {
	color: #ff5722;
	font-size: 20px;
	text-decoration: none;
}
.modual-popup-content {
	padding: 40px;
	background-color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
}
a.Alredy-Upgraded-link {
	color: #7867ff;
	font-weight: bold;
	text-decoration: none;
	font-size: 14px;
	margin-top: 10px;
}
.modual-popup-content p {
	text-align: center;
	margin-bottom: 30px;
}
a.Upgrade-pro-btn {
	background-color: #ff5722;
	color: #fff;
	text-decoration: none;
	padding: 12px 15px;
	font-weight: 600;
	letter-spacing: 0.5px;
	font-size: 15px;
	margin-bottom: 20px;
}
a.prem-plan-btn {
	background-color: #e9f3ff;
	padding: 10px 25px;
	border-radius: 20px;
	text-decoration: none;
	color: #1c81fb;
	font-weight: bold;
	margin-bottom: 10px;
}
.popup.show {
	opacity: 1;
	visibility: visible;
	position: fixed;
	z-index: 9;
	left: 0;
	right: 0;
	display: block;
}

.black-bg {
	opacity: 0;
	position: fixed;
	background: radial-gradient(rgba(0, 0, 0, 0.8) 75%, rgba(0, 0, 0, 0.6));
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
}

.popup {
	opacity: 0;
	overflow: hidden;
	background: #fcfcfc;
	width: 400px;
	padding: 0;
	margin: 0 auto;
	box-shadow: #000 0 1px 20px;
	transition: all .1s ease-out;
	display: none;
}

.input.uwa-toggle-addons:checked ~ .black-bg {
	opacity: 1;
	transition: opacity .15s ease-in;
}

.input.uwa-toggle-addons:checked ~ .popup {
	opacity: 1;
	transition: opacity .15s ease-in;
}

.input.uwa-toggle-addons:checked + label::before {
	content: "Close ";
}

.close {
	position: absolute;
	top: 0px;
	right: 10px;
	font-size: 3em;
	color: #ccc;
}

.close:hover {
	color: #d00;
}

.uws-addons-content .plugin-card .plugin-action-buttons .uwa-switch input:checked+.uwa_slider {

background-color: #0068a0

}



.uwa_addon_setting_title {

padding: 6px 0 6px 12px;

padding: 10px 0 12px 12px;

margin: 0 0 10px 0;

font-weight: 600!important;

font-size: 1.3em!important;

background-color: #f9f9f9;

color: #23282d!important

}



.uwa-switch {

position: relative;

display: inline-block;

width: 40px;

height: 18px

}



.uwa-switch input {

display: none

}



.uwa-switch .uwa_slider {

position: absolute;

cursor: pointer;

top: 0;

left: 0;

right: 0;

bottom: 0;

background-color: #ccc;

-webkit-transition: .4s;

transition: .4s

}



.uwa-switch .uwa_slider.round {

border-radius: 34px

}



.uwa-switch .uwa_slider.round:before {

border-radius: 50%

}



.uwa-switch .uwa_slider.round:before {

position: absolute;

content: "";

height: 14px;

width: 14px;

left: 2px;

bottom: 2px;

background-color: #fff;

-webkit-transition: .4s;

transition: .4s

}



.uwa-switch input:checked+.uwa_slider:before {

-webkit-transform: translateX(22px);

-ms-transform: translateX(22px);

transform: translateX(22px)

}



</style>