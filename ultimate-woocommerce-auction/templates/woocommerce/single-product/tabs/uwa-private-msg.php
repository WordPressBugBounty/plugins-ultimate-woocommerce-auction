<?php
/**
 * Private message tab
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $woocommerce, $post, $product;
$user_id    = get_current_user_id();
$user       = get_user_by( 'id', $user_id );
$user_email = isset( $user->data->user_email ) ? $user->data->user_email : '';
$user_login = isset( $user->data->user_login ) ? $user->data->user_login : '';
?>
<div class="private_msg_main">
	<h2><?php esc_html_e( 'Send Private Message', 'ultimate-woocommerce-auction' ); ?></h2>
	<form id="uwa_private_msg_form" method="post" action="">
	<!-- hidden variables -->
	<input type="hidden" name="uwa_pri_product_id" class="uwa_pri_product_id" value="<?php echo esc_attr( $product->get_id() ); ?>" />
	<div id="uwa_private_msg_success"></div>
		<img class="uwa_private_msg_ajax_loader" src="<?php echo esc_url( WOO_UA_ASSETS_URL ); ?>/images/ajax_loader.gif" alt="Loading..." />
		<table id="auction-privatemsg-table-<?php echo esc_attr( $product->get_id() ); ?>" class="auction-privatemsg-table">
			<tbody>
			<?php if ( ! is_user_logged_in() ) { ?>
			<tr>
				<td><?php esc_html_e( 'Name:', 'ultimate-woocommerce-auction' ); ?></td>
				<td class="name">
				<input type="text" placeholder="<?php esc_html_e( 'Your Name', 'ultimate-woocommerce-auction' ); ?>" class="uwa_pri_name" id="uwa_pri_name" name="uwa_pri_name" value="<?php echo esc_attr( $user_login ); ?>" ></br>
				<div id="error_fname" class="error_forms"></div>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Email:', 'ultimate-woocommerce-auction' ); ?></td>
				<td class="name">
				<input type="email" placeholder="you@example.com" class="uwa_pri_email" id="uwa_pri_email"  value="<?php echo esc_attr( $user_email ); ?>" name="uwa_pri_email" ></br>
				<span id="error_email" class="error_forms"></span>
				</td>
			</tr>
			<?php } else { ?>
				<input type="hidden" placeholder="<?php esc_html_e( 'Your Name', 'ultimate-woocommerce-auction' ); ?>" class="uwa_pri_name" id="uwa_pri_name" name="uwa_pri_name" value="<?php echo esc_attr( $user_login ); ?>" >
				<input type="hidden" placeholder="you@example.com" class="uwa_pri_email" id="uwa_pri_email"  value="<?php echo esc_attr( $user_email ); ?>" name="uwa_pri_email" >
			<?php } ?>
			<tr>
				<td><?php esc_html_e( 'Message:', 'ultimate-woocommerce-auction' ); ?></td>
				<td class="name">
					<textarea id="uwa_pri_message" placeholder="<?php esc_html_e( 'Message Detail', 'ultimate-woocommerce-auction' ); ?>" class="uwa_pri_message"></textarea>
					</br>
					<span id="error_message" class="error_forms"></span>
				</td>
			</tr>
			<tr>
				<td>
				<button  id="uwa_private_send" class="button alt uwa_private_send">
					<?php esc_html_e( 'Send', 'ultimate-woocommerce-auction' ); ?>
				</button>
				</td>
				<td></td>
			</tr>
			
			</tbody>
		<tr class="start"></tr>
		</table>
	</form>
</div> 
<!--- Private Message tab end-->
