<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Scripts Class
 *
 * Handles Scripts and Styles enqueues functionality.
 *
 * @package Ultimate Auction For WooCommerce
 * @author Nitesh Singh
 * @since 1.0
 */
class UWA_Scripts {

	private static $instance;
	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Singleton The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	public function __construct() {

		// Add admin side scripts
		add_action( 'admin_footer', array( $this, 'uwa_register_admin_scripts' ) );

		// Add admin side styles
		add_action( 'admin_enqueue_scripts', array( $this, 'uwa_register_admin_styles' ) );

		// front side scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'uwa_register_front_scripts' ) );

		// Add front side styles
		add_action( 'wp_enqueue_scripts', array( $this, 'uwa_register_front_styles' ) );
	}
	/**
	 * Manage admin side scripts
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
	public function uwa_register_admin_scripts( $hook_sufix ) {
		// Register globally scripts
		wp_register_script( 'uwa-admin', WOO_UA_ASSETS_URL . 'js/uwa-admin.js', array( 'jquery' ), WOO_UA_VERSION );

		wp_register_script( 'uwa-datepicker', WOO_UA_ASSETS_URL . 'js/date-picker.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), '1.0' );
		// localization script
		wp_localize_script( 'uwa-admin', 'WpUat', array( 'calendar_icon' => '<i class="dashicons-calendar-alt"></i>' ) );
		wp_localize_script(
			'uwa-admin',
			'WooUa',
			array(
				'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
				'ua_nonce'                 => wp_create_nonce( 'uwaajax-nonce' ),
				'calendar_image'           => WC()->plugin_url() . '/assets/images/calendar.png',
				'reguler_required_message' => __( 'Please enter Regular price.', 'ultimate-woocommerce-auction' ),
			)
		);
		wp_enqueue_script( 'uwa-admin' );

		wp_enqueue_script( 'uwa-datepicker' );
	}
	/**
	 * Manage admin side styles
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
	public function uwa_register_admin_styles( $hook_sufix ) {

		// Register styles
		wp_register_style( 'uwa-admin-css', WOO_UA_ASSETS_URL . 'css/uwa-admin.css', array(), WOO_UA_VERSION );

		// Enqueue styles
		wp_enqueue_style( 'uwa-admin-css' );
	}
	/**
	 * Manage front side scripts
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
	public function uwa_register_front_scripts( $hook_sufix ) {
		// Register globally scripts
		wp_register_script( 'uwa-front', WOO_UA_ASSETS_URL . 'js/uwa-front.js', array( 'jquery' ), WOO_UA_VERSION );

		wp_register_script( 'uwa-jquery-countdown', WOO_UA_ASSETS_URL . 'js/jquery.countdown.min.js', array( 'jquery' ), WOO_UA_VERSION, false );
		wp_enqueue_script( 'uwa-jquery-countdown' );

		wp_register_script( 'uwa-jquery-countdown-multi-lang', WOO_UA_ASSETS_URL . 'js/jquery.countdown-multi-lang.js', array( 'jquery', 'uwa-jquery-countdown' ), WOO_UA_VERSION, false );
			// localization custom data

			$multi_lang_data = array(
				'labels'        => array(
					'Years'   => __( 'Years', 'ultimate-woocommerce-auction' ),
					'Months'  => __( 'Months', 'ultimate-woocommerce-auction' ),
					'Weeks'   => __( 'Weeks', 'ultimate-woocommerce-auction' ),
					'Days'    => __( 'Days', 'ultimate-woocommerce-auction' ),
					'Hours'   => __( 'Hours', 'ultimate-woocommerce-auction' ),
					'Minutes' => __( 'Minutes', 'ultimate-woocommerce-auction' ),
					'Seconds' => __( 'Seconds', 'ultimate-woocommerce-auction' ),
				),
				'labels1'       => array(
					'Year'   => __( 'Year', 'ultimate-woocommerce-auction' ),
					'Month'  => __( 'Month', 'ultimate-woocommerce-auction' ),
					'Week'   => __( 'Week', 'ultimate-woocommerce-auction' ),
					'Day'    => __( 'Day', 'ultimate-woocommerce-auction' ),
					'Hour'   => __( 'Hour', 'ultimate-woocommerce-auction' ),
					'Minute' => __( 'Minute', 'ultimate-woocommerce-auction' ),
					'Second' => __( 'Second', 'ultimate-woocommerce-auction' ),
				),
				'compactLabels' => array(
					'y' => __( 'y', 'ultimate-woocommerce-auction' ),
					'm' => __( 'm', 'ultimate-woocommerce-auction' ),
					'w' => __( 'w', 'ultimate-woocommerce-auction' ),
					'd' => __( 'd', 'ultimate-woocommerce-auction' ),
				),
			);

			// localization custom data
			$uwa_custom_data = array(
				'expired'        => __( 'Auction has Expired!', 'ultimate-woocommerce-auction' ),
				'gtm_offset'     => get_option( 'gmt_offset' ),
				'started'        => __( 'Auction Started! Please refresh page.', 'ultimate-woocommerce-auction' ),
				'outbid_message' => wc_get_template_html(
					'notices/error.php',
					array(
						'notices' => array(
							array(
								'notice' => __( 'You have been overbidded!', 'ultimate-woocommerce-auction' ),
							),
						),
					)
				),
				'hide_compact'   => get_option( 'uwa_hide_compact_enable', 'no' ),
			);

			$bid_ajax_enable_check          = get_option( 'woo_ua_auctions_bid_ajax_enable' );
			$bid_ajax_enable_check_interval = get_option( 'woo_ua_auctions_bid_ajax_interval' );
			if ( $bid_ajax_enable_check == 'yes' ) {

				$uwa_custom_data['refresh_interval'] = isset( $bid_ajax_enable_check_interval ) && is_numeric( $bid_ajax_enable_check_interval ) ? $bid_ajax_enable_check_interval : '1';

			}

			// localization script

			wp_localize_script( 'uwa-jquery-countdown-multi-lang', 'multi_lang_data', $multi_lang_data );
			wp_enqueue_script( 'uwa-jquery-countdown-multi-lang' );

			wp_localize_script( 'uwa-front', 'uwa_data', $uwa_custom_data );
			wp_localize_script( 'uwa-front', 'WpUat', array( 'calendar_icon' => '<i class="dashicons-calendar-alt"></i>' ) );
			wp_localize_script(
				'uwa-front',
				'WooUa',
				array(
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'ua_nonce'       => wp_create_nonce( 'uwaajax-nonce' ),
					'last_timestamp' => get_option( 'woo_ua_auction_last_activity', '0' ),
					'calendar_image' => WC()->plugin_url() . '/assets/images/calendar.png',
				)
			);
			wp_localize_script( 'uwa-front', 'UWA_Ajax_Qry', array( 'ajaqry' => add_query_arg( 'uwa-ajax', '' ) ) );
			wp_enqueue_script( 'uwa-front' );
	}
	/**
	 * Manage Front side styles
	 *
	 * @package Ultimate WooCommerce Auction
	 * @author Nitesh Singh
	 * @since 1.0
	 */
	public function uwa_register_front_styles( $hook_sufix ) {

		// Register styles
		wp_register_style( 'uwa-front-css', WOO_UA_ASSETS_URL . 'css/uwa-front.css', array( 'dashicons' ), WOO_UA_VERSION, false );

		// Enqueue styles
		wp_enqueue_style( 'uwa-front-css' );
	}
}
UWA_Scripts::get_instance();
