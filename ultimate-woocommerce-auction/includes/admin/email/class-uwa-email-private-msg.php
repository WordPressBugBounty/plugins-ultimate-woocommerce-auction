<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 *
 * @class UWA_Email_Private_Msg
 * @author Nitesh Singh
 * @since 1.0
 */
if ( ! class_exists( 'UWA_Email_Private_Msg' ) ) {
	/**
	 * Class UWA_Email_Private_Msg
	 */
	class UWA_Email_Private_Msg extends WC_Email {
		/**
		 * Construct
		 *
		 * @since 1.0
		 */
		public function __construct() {

			$this->id            = 'woo_ua_email_auction_private_msg_admin';
			$this->title         = __( 'Ultimate Auction - Private Message', 'ultimate-woocommerce-auction' );
			$this->description   = __( 'Email can be send to admin when bidder send Private message.', 'ultimate-woocommerce-auction' );
			$this->heading       = __( 'You have a private message from', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->subject       = __( 'You have a private message from', 'ultimate-woocommerce-auction' ) . ' {site_title}';
			$this->template_html = 'emails/auction-private-msg.php';

			// Trigger on bid overbidded by other bidder
			add_action( 'uwa_private_msg_email_admin', array( $this, 'trigger' ), 10, 2 );
			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
			// Other settings
			$this->recipient = $this->get_option( 'recipient' );
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		public function trigger( $user_args ) {

			// Check is email enable or not
			if ( ! $this->is_enabled() ) {
				return;
			}

			$this->object = $user_args;

			$url_product = get_permalink( $this->object['product_id'] );

			$this->object = array(
				'product_id'   => $this->object['product_id'],
				'user_name'    => $this->object['user_name'],
				'user_email'   => $this->object['user_email'],
				'user_message' => $this->object['user_message'],
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
					'description' => sprintf( __( 'Enter the subject of the email that is sent to the admin when bidder send private message. Leave blank to use the default subject: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'ultimate-woocommerce-auction' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter the heading of the email that is sent to the admin when bidder send private message. Leave blank to use the default heading: <code>%s</code>.', 'ultimate-woocommerce-auction' ), $this->heading ),
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
						'html' => __( 'HTML', 'ultimate-woocommerce-auction' ),
					),
				),
			);
		}
	}
}
return new UWA_Email_Private_Msg();
