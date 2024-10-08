<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 *
 * @class UWA_Email_Place_Bid
 * @package Ultimate Auction For WooCommerce
 * @author Nitesh Singh
 * @since 1.0
 */
if ( ! class_exists( 'UWA_Email_Place_Bid' ) ) {
	/**
	 * Class UWA_Email_Place_Bid
	 *
	 * @author Nitesh Singh
	 */
	class UWA_Email_Place_Bid extends WC_Email {
		/**
		 * Construct
		 *
		 * @author Nitesh Singh
		 * @since 1.0
		 */
		public function __construct() {

			$this->id             = 'woo_ua_email_place_bid';
			$this->title          = __( '​Ultimate Auction - User Bid Notification', 'ultimate-woocommerce-auction' );
			$this->customer_email = true;
			$this->description    = __( '​Email Notification sent to Bidder when Bidder places a bid.', 'ultimate-woocommerce-auction' );
			$this->heading        = __( 'You recently placed a bid on', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->subject        = __( 'You recently placed a bid on', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->template_html  = 'emails/placed-bid.php';
			$this->template_plain = 'emails/plain/placed-bid.php';
			// Trigger when bid placed
			add_action( 'uwa_bid_place_email', array( $this, 'trigger' ), 10, 2 );
			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
		}
		public function trigger( $user_id, $product ) {

			// Check is email enable or not
			if ( ! $this->is_enabled() ) {
				return;
			}
			$user        = get_user_by( 'id', $user_id );
			$url_product = get_permalink( $product->get_id() );

			$this->object = array(
				'user_email'   => $user->data->user_email,
				'user_name'    => $user->data->user_login,
				'product_name' => $product->get_title(),
				'product'      => $product,
				'url_product'  => $url_product,
			);
			$this->send(
				$this->object['user_email'],
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				),
				'',
				WOO_UA_WC_TEMPLATE
			);
		}
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
				),
				'',
				WOO_UA_WC_TEMPLATE
			);
		}
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'ultimate-woocommerce-auction' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'ultimate-woocommerce-auction' ),
					'default' => 'yes',
				),

				'subject'    => array(
					'title'       => __( 'Subject', 'ultimate-woocommerce-auction' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter the subject of the email that is sent to the bidder after successfully placing a bid.Leave blank to use the default subject:- <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'ultimate-woocommerce-auction' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter the Heading of the email that is sent to the bidder after successfully placing a bid. Leave blank to use the default heading: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->heading ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'ultimate-woocommerce-auction' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'ultimate-woocommerce-auction' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}
	}
}
return new UWA_Email_Place_Bid();
