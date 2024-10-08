<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 *
 * @class UWA_Email_Auction_Bid_Overbid
 * @author Nitesh Singh
 * @since 1.0
 */
if ( ! class_exists( 'UWA_Email_Auction_Bid_Overbid' ) ) {
	/**
	 * Class UWA_Email_Auction_Bid_Overbid
	 */
	class UWA_Email_Auction_Bid_Overbid extends WC_Email {
		/**
		 * Construct
		 *
		 * @since 1.0
		 */
		public function __construct() {

			$this->id             = 'woo_ua_email_auction_bid_overbidded';
			$this->title          = __( 'Ultimate Auction - User Outbid Notification', 'ultimate-woocommerce-auction' );
			$this->customer_email = true;
			$this->description    = __( 'Email ​Can be send to Bidder for Outbid their Bid', 'ultimate-woocommerce-auction' );
			$this->heading        = __( 'Auction has been outbid on', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->subject        = __( 'Auction has been outbid on', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->template_html  = 'emails/bid-outbided.php';
			$this->template_plain = 'emails/plain/bid-outbided.php';
			// Trigger on bid overbidded by other bidder
			add_action( 'uwa_outbid_bid_email', array( $this, 'trigger' ), 10, 2 );
			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
		}
		public function trigger( $outbiddeduser, $product ) {

			if ( ! $this->is_enabled() ) {
				return;
			}
			$user        = get_user_by( 'id', $outbiddeduser );
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
					'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'ultimate-woocommerce-auction' ),
					'type'        => 'text',
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->heading ),
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
return new UWA_Email_Auction_Bid_Overbid();
