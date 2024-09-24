<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 *
 * @class UWA_Email_Auction_Bid_Admin
 * @package Ultimate Auction For WooCommerce
 * @author Nitesh Singh
 * @since 1.0
 */
if ( ! class_exists( 'UWA_Email_Auction_Bid_Admin' ) ) {
	/**
	 * Class UWA_Email_Auction_Bid_Admin
	 *
	 * @author Nitesh Singh
	 */
	class UWA_Email_Auction_Bid_Admin extends WC_Email {
		/**
		 * Construct
		 *
		 * @author Nitesh Singh
		 * @since 1.0
		 */
		public function __construct() {
			$this->id             = 'woo_ua_email_action_bid_intimation_admin';
			$this->title          = __( 'Ultimate Auction - Admin Bid Notification', 'ultimate-woocommerce-auction' );
			$this->description    = __( '​Send bid email notification to admin when Bidder place a bid.', 'ultimate-woocommerce-auction' );
			$this->heading        = __( 'Bid item on', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->subject        = __( 'Bidder placed a bid on', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->template_html  = 'emails/placed-bid-admin.php';
			$this->template_plain = 'emails/plain/placed-bid-admin.php';
			// Trigger when bid placed
			add_action( 'uwa_bid_place_email_admin', array( $this, 'trigger' ), 10, 1 );
			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
			// Other settings
			$this->recipient = $this->get_option( 'recipient' );
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}
		public function trigger( $product ) {

			// Check is email enable or not
			if ( ! $this->is_enabled() ) {
				return;
			}
			$url_product = get_permalink( $product->get_id() );

			$this->object = array(
				'product_name' => $product->get_title(),
				'product'      => $product,
				'url_product'  => $url_product,
			);
			$this->send(
				$this->get_recipient(),
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
				'recipient'  => array(
					'title'       => __( 'Recipient(s)', 'ultimate-woocommerce-auction' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'ultimate-woocommerce-auction' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => '',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'ultimate-woocommerce-auction' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter the subject of the email that is sent to the admin after successfully placing a bid. Leave blank to use the default subject: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'ultimate-woocommerce-auction' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter the Heading of the email that is sent to the admin after successfully placing a bid. Leave blank to use the default heading: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->heading ),
					'placeholder' => '',
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'ultimate-woocommerce-auction' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'ultimate-woocommerce-auction' ),
					'default'     => 'html',
					'class'       => 'email_type',
					'options'     => array(
						'plain'     => __( 'Plain text', 'ultimate-woocommerce-auction' ),
						'html'      => __( 'HTML', 'ultimate-woocommerce-auction' ),
						'multipart' => __( 'Multipart', 'ultimate-woocommerce-auction' ),
					),
				),
			);
		}
	}
}
return new UWA_Email_Auction_Bid_Admin();
