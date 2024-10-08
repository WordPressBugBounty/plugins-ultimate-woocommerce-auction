<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly
class UWA_My_Auction_Setting_Endpoint {
	/**
	 * Custom endpoint name.
	 *
	 * @var string
	 */
	public static $endpoint = 'my-auction-setting';
	/**
	 * Plugin actions.
	 */
	public function __construct() {
		// Actions used to insert a new endpoint in the WordPress.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		// Change the My Accout page title.
		add_filter( 'the_title', array( $this, 'endpoint_title' ) );
		// Insering your new tab/page into the My Account page.
		add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
		add_action( 'woocommerce_account_' . self::$endpoint . '_endpoint', array( $this, 'endpoint_content' ) );

		add_action( 'template_redirect', array( $this, 'update_uwa_account_details' ) );
	}
	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
	}
	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::$endpoint;
		return $vars;
	}
	/**
	 * Set endpoint title.
	 *
	 * @param string $title
	 * @return string
	 */
	public function endpoint_title( $title ) {
		global $wp_query;
		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );
		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'Auctions Setting', 'ultimate-woocommerce-auction' );
			remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
		}
		return $title;
	}
	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param array $items
	 * @return array
	 */
	public function new_menu_items( $items ) {

		if ( get_template() == 'flatsome' ) {

				$logout = '';

			if ( isset( $items['customer-logout'] ) ) {
				$logout = $items['customer-logout'];
				unset( $items['customer-logout'] );
			}

				$items[ self::$endpoint ] = __( 'Auctions Setting', 'ultimate-woocommerce-auction' );

			if ( ! isset( $items['customer-logout'] ) && $logout ) {
				$items['customer-logout'] = $logout;
			}
		} else {
				// Remove the logout menu item.
			if ( isset( $items['customer-logout'] ) ) {
				$logout = $items['customer-logout'];
				unset( $items['customer-logout'] );
			}

				// Insert your custom endpoint.
				$items[ self::$endpoint ] = __( 'Auctions Setting', 'ultimate-woocommerce-auction' );

				// Insert back the logout item.
			if ( isset( $logout ) ) {
				$items['customer-logout'] = $logout;
			}
		}

		return $items;
	}

	/**
	 * Endpoint HTML content.
	 */
	public function endpoint_content() {
		wc_get_template( 'myaccount/uwa-myauction-setting.php' );
	}

	/**
	 * update_uwa_account_details.
	 */
	public function update_uwa_account_details() {

		if ( empty( $_POST['action'] ) || 'save_uwa_auctions_settings' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_uwa_auctions_settings' ) ) {
			return;
		}
		nocache_headers();
		$user_id = (int) get_current_user_id();
		if ( isset( $_POST['uwa_disable_display_user_name'] ) ) {
			update_user_meta( $user_id, 'uwa_disable_display_user_name', sanitize_key( $_POST['uwa_disable_display_user_name'] ) );
		} else {
			update_user_meta( $user_id, 'uwa_disable_display_user_name', '0' );
		}
	}

	/**
	 * Plugin install action.
	 * Flush rewrite rules to make our custom endpoint available.
	 */
	public static function install() {
		flush_rewrite_rules();
	}
}
new UWA_My_Auction_Setting_Endpoint();
// Flush rewrite rules on plugin activation.
register_activation_hook( __FILE__, array( 'UWA_My_Auction_Setting_Endpoint', 'install' ) );
