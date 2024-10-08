jQuery(document).ready(
	function ($) {
		// validate regular price if product type - simple
		jQuery(document).on(
			'click',
			'#publish',
			function (event) {
				if (jQuery('#product-type').length > 0) {
					var pro_type = jQuery('#product-type').val();
					if (pro_type == 'simple') {
						var regular_price = jQuery('#_regular_price').val();
						if (!regular_price) {
							event.preventDefault();
							alert(WooUa.reguler_required_message);
						}
					}
				}
			}
		);
		// End Date
		jQuery('.datetimepicker').datetimepicker(
			{
				defaultDate: "",
				timeFormat: "HH:mm:ss",
				dateFormat: "yy-mm-dd",
				minDate: 0,
				numberOfMonths: 1,
				showButtonPanel: true,
				showOn: "button",
				buttonImage: WooUa.calendar_image,
				buttonText: 'Select a time',
				buttonImageOnly: true,
				showMillisec: 0,
			}
		);

		// Set regular price for other product type
		jQuery('#general_product_data #_regular_price').on(
			'keyup',
			function () {
				jQuery('#auction_options #_regular_price').val(jQuery(this).val());
			}
		);
		jQuery("#woo_ua_auction_end_date").keypress(
			function (event) {
				event.preventDefault();
			}
		);
		// If Auction product Selected
		var productType = jQuery('#product-type').val();
		if (productType == 'auction') {
			jQuery('.show_if_simple').show();
			jQuery('.inventory_options').show();
			jQuery('.general_options').show();
			jQuery('#inventory_product_data ._manage_stock_field').addClass('hide_if_auction').hide();
			jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('hide_if_auction').hide();
			jQuery('#inventory_product_data ._sold_individually_field').addClass('hide_if_auction').hide();
			jQuery('#inventory_product_data ._stock_field ').addClass('hide_if_auction').hide();
			jQuery('#inventory_product_data ._backorders_field ').parent().addClass('hide_if_auction').hide();
			jQuery('#inventory_product_data ._stock_status_field ').addClass('hide_if_auction').hide();
			jQuery('.options_group.pricing ').addClass('hide_if_auction').hide();

			var woo_ua_auction_form_type = jQuery("#woo_ua_auction_form_type").val();
			if (woo_ua_auction_form_type == "edit_product") {

				/* make fields disabled when auction is live/expired */
				var woo_ua_auction_status_type = jQuery("#woo_ua_auction_status_type").val();

				if (woo_ua_auction_status_type == "expired") {

					jQuery("#woo_ua_auction_end_date").attr("disabled", "disabled");

				}

			}
		}

		// hide inventory_product_data
		jQuery('select#product-type').on(
			'change',
			function () {
				var value = jQuery(this).val();
				if (value == 'auction') {
					jQuery('.show_if_simple').show();
					jQuery('.general_options').show();
					jQuery('#inventory_product_data ._manage_stock_field').addClass('hide_if_auction').hide();
					jQuery('#inventory_product_data ._sold_individually_field').parent().addClass('hide_if_auction').hide();
					jQuery('#inventory_product_data ._sold_individually_field').addClass('hide_if_auction').hide();
					jQuery('#inventory_product_data ._backorders_field ').parent().addClass('hide_if_auction').hide();
					jQuery('.options_group.pricing ').addClass('hide_if_auction').hide();
				}
			}
		);
		// show virtual and downloadable option for auction product
		jQuery('label[for="_virtual"]').addClass('show_if_auction');
		jQuery('label[for="_downloadable"]').addClass('show_if_auction');

		// Cancel Last bid
		jQuery('.uwa-admin-table .bid_action a:not(.disabled)').on(
			'click',
			function (event) {

				var logid = $(this).data('id');
				var postid = $(this).data('postid');
				var curent = $(this);

				jQuery.ajax(
					{

						type: "post",
						url: WooUa.ajaxurl,
						data: { action: "admin_cancel_bid", logid: logid, postid: postid, ua_nonce: WooUa.nonce },
						success: function (response) {

							var data = $.parseJSON(response);
							if (data.status == 1) {
								alert(data.success_message);
								window.location.reload();
							} else {
								alert(data.error_message);
								window.location.reload();

							}

						}

					}
				);

				event.preventDefault();

			}
		);

		jQuery(".uwa-see-more").click(
			function () {
				var current = jQuery(this);
				var auction_id = jQuery(this).attr('rel');

				jQuery(".uwa-bidder-list-" + auction_id).css('opacity', '0.4');

				var show_rows;
				var label_text;
				if (jQuery(this).hasClass('show-all')) {
					show_rows = -1;
					jQuery(this).removeClass('show-all');
					jQuery(this).addClass('show-one');
				} else {
					show_rows = 1;
					jQuery(this).removeClass('show-one');
					jQuery(this).addClass('show-all');
				}

				var data = {
					action: 'uwa_see_more_bids_ajax',
					auction_id: auction_id,
					show_rows: show_rows,
					ua_nonce: WooUa.ua_nonce
				};

				jQuery.post(
					ajaxurl,
					data,
					function (response) {

						var data = jQuery.parseJSON(response);

						jQuery(".uwa-bidder-list-" + auction_id).html(data.bids_list);

						current.text(data.uwa_label_text);

						jQuery(".uwa-bidder-list-" + auction_id).css('opacity', '1');
					}
				);
				return false;

				event.preventDefault();
			}
		);

	}
);