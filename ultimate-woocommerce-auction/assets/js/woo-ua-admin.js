jQuery( document ).ready(
	function ($) {
		// End Date
		jQuery( '.datetimepicker' ).datetimepicker(
			{   defaultDate: "",
				timeFormat: "HH:mm:ss",
				dateFormat: "yy-mm-dd",
				minDate: 0 ,
				numberOfMonths: 1,
				showButtonPanel: true,
				showOn: "button",
				buttonImage: WooUa.calendar_image,
				buttonText : 'Select a time',
				buttonImageOnly: true,
				showMillisec : 0,
			}
		);

		// Set regular price for other product type
		jQuery( '#general_product_data #_regular_price' ).on(
			'keyup',
			function () {
				jQuery( '#auction_options #_regular_price' ).val( jQuery( this ).val() );
			}
		);
		jQuery( "#woo_ua_auction_end_date" ).keypress(
			function (event) {
				event.preventDefault();}
		);
		// If Auction product Selected
		var productType = jQuery( '#product-type' ).val();
		if (productType == 'auction') {
			jQuery( '.show_if_simple' ).show();
			jQuery( '.inventory_options' ).show();
			jQuery( '.general_options' ).show();
			jQuery( '#inventory_product_data ._manage_stock_field' ).addClass( 'hide_if_auction' ).hide();
			jQuery( '#inventory_product_data ._sold_individually_field' ).parent().addClass( 'hide_if_auction' ).hide();
			jQuery( '#inventory_product_data ._sold_individually_field' ).addClass( 'hide_if_auction' ).hide();
			jQuery( '#inventory_product_data ._stock_field ' ).addClass( 'hide_if_auction' ).hide();
			jQuery( '#inventory_product_data ._backorders_field ' ).parent().addClass( 'hide_if_auction' ).hide();
			jQuery( '#inventory_product_data ._stock_status_field ' ).addClass( 'hide_if_auction' ).hide();
			jQuery( '.options_group.pricing ' ).addClass( 'hide_if_auction' ).hide();
		}

		// hide inventory_product_data
		jQuery( 'select#product-type' ).on(
			'change',
			function () {
				var value = jQuery( this ).val();
				if (value == 'auction') {
						jQuery( '.show_if_simple' ).show();
						jQuery( '.general_options' ).show();
						jQuery( '#inventory_product_data ._manage_stock_field' ).addClass( 'hide_if_auction' ).hide();
						jQuery( '#inventory_product_data ._sold_individually_field' ).parent().addClass( 'hide_if_auction' ).hide();
						jQuery( '#inventory_product_data ._sold_individually_field' ).addClass( 'hide_if_auction' ).hide();
						jQuery( '#inventory_product_data ._backorders_field ' ).parent().addClass( 'hide_if_auction' ).hide();
						jQuery( '.options_group.pricing ' ).addClass( 'hide_if_auction' ).hide();
				}
			}
		);
		// show virtual and downloadable option for auction product
		jQuery( 'label[for="_virtual"]' ).addClass( 'show_if_auction' );
		jQuery( 'label[for="_downloadable"]' ).addClass( 'show_if_auction' );

		// End Bid Now
		jQuery( '.woo_end_now' ).on(
			'click',
			function (event) {
				var postid = $( this ).data( 'postid' );
				jQuery.ajax(
					{
						type : "post",
						url : WooUa.ajaxurl,
						data : {action: "admin_end_auction_now",postid: postid, ua_nonce : WooUa.nonce },
						success: function (response) {
							var data = $.parseJSON( response );
							if ( data.status == 1 ) {
								alert( data.success_message );
								window.location.reload();
							} else {
								alert( data.error_message );
								window.location.reload();
							}
						}

					}
				);
				event.preventDefault();
			}
		);

		// Cancel Last bid
		jQuery( '.woo_ua-table .bid_action a:not(.disabled)' ).on(
			'click',
			function (event) {

				var logid  = $( this ).data( 'id' );
				var postid = $( this ).data( 'postid' );
				var curent = $( this );

				jQuery.ajax(
					{

						type : "post",
						url : WooUa.ajaxurl,
						data : {action: "admin_cancel_bid", logid : logid, postid: postid, ua_nonce : WooUa.nonce },
						success: function (response) {

							var data = $.parseJSON( response );
							if ( data.status == 1 ) {
								alert( data.success_message );
								window.location.reload();
							} else {
								alert( data.error_message );
								window.location.reload();

							}

						}

					}
				);

				event.preventDefault();

			}
		);

	}
);