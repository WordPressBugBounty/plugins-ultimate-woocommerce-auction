<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 * Handling AJAX Event.
 *
 * @class  UWA_AJAX
 * @package Ultimate Auction For WooCommerce
 * @author Nitesh Singh
 * @since 1.0
 */
class UWA_AJAX {
	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_uwa_ajax' ), 0 );
		add_action( 'wp_loaded', array( __CLASS__, 'do_uwa_ajax' ), 10 );
	}
	/**
	 * Set AJAX constant and headers.
	 */
	public static function define_uwa_ajax() {
		$uwa_do_uwa_ajax_nonce = wp_create_nonce( 'uwa_do_uwa_ajax_nonce' );
		if ( ! isset( $uwa_do_uwa_ajax_nonce ) || ! wp_verify_nonce( $uwa_do_uwa_ajax_nonce, 'uwa_do_uwa_ajax_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed.' );
		}
		if ( ! empty( $_GET['uwa-ajax'] ) ) {
			wc_maybe_define_constant( 'UWA_DOING_AJAX', true );
			wc_maybe_define_constant( 'WC_DOING_AJAX', true );
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}
	/**
	 * Send headers for Ajax Requests.
	 */
	private static function wc_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}
	/**
	 * Check for  Ajax request and fire action.
	 */
	public static function do_uwa_ajax() {
		global $wp_query;
		$uwa_do_uwa_ajax_nonce = wp_create_nonce( 'uwa_do_uwa_ajax_nonce' );
		if ( ! isset( $uwa_do_uwa_ajax_nonce ) || ! wp_verify_nonce( $uwa_do_uwa_ajax_nonce, 'uwa_do_uwa_ajax_nonce' ) ) {
			wp_send_json_error( 'Nonce verification failed.' );
		}
		if ( ! empty( $_GET['uwa-ajax'] ) ) {
			self::wc_ajax_headers();
			do_action( 'uwa_ajax_' . sanitize_text_field( $_GET['uwa-ajax'] ) );
			wp_die();
		}
	}
}
UWA_AJAX::init();
