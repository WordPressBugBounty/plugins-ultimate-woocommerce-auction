<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @class      UWA_Email_Auction_Winner
 * @package Ultimate Auction For WooCommerce
 * @since      Version 1.0.0
 * @author Nitesh Singh
 */
if ( ! class_exists( 'UWA_Email_Auction_Winner' ) ) {
	/**
	 * Class UWA_Email_Auction_Winner
	 *
	 * @author Nitesh Singh
	 */
	class UWA_Email_Auction_Winner extends WC_Email {

		/**
		 * Construct
		 *
		 * @author Nitesh Singh
		 * @since 1.0
		 */
		public function __construct() {
			$this->id             = 'woo_ua_email_auction_winner';
			$this->title          = __( 'Ultimate Auction - Auction Won', 'ultimate-woocommerce-auction' );
			$this->customer_email = true;
			$this->description    = __( '​Email ​Can be sent to the bidder after winning an auction', 'ultimate-woocommerce-auction' );
			$this->heading        = __( 'Congratulations! You have won an auction on', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->subject        = __( 'Congratulations! You have won an auction on', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->template_html  = 'emails/auction-winner.php';
			$this->template_plain = 'emails/plain/auction-winner.php';
			// Trigger on new paid orders
			add_action( 'uwa_won_email_bidder', array( $this, 'trigger' ), 10, 2 );
			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
		}
		public function trigger( $product_id, $winneruser ) {

			// Check is email enable or not
			if ( ! $this->is_enabled() ) {
				return;
			}

			$user        = get_user_by( 'id', $winneruser );
			$url_product = get_permalink( $product_id );

			$this->object = array(
				'user_email'  => $user->data->user_email,
				'user_name'   => $user->user_login,
				'product_id'  => $product_id,
				'url_product' => $url_product,
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
					'description' => sprintf( __( 'Enter the subject of the email that is sent to the bidder after winning an auction. Leave blank to use the default subject: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'ultimate-woocommerce-auction' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter the Heading of the email that is sent to the bidder after winning an auction. Leave blank to use the default heading: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->heading ),
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
return new UWA_Email_Auction_Winner();
