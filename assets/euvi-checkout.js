jQuery(document).ready(function($) {
	var $body = $('body');

	$body.on('blur', '#vieu_vat_number', function() {
		var $edd_cc_address = $('#edd_cc_address');
		var postData = {
			action: 'euvi_maybe_vat_exempt',
			vat_number: $edd_cc_address.find('#vieu_vat_number').val(),
			billing_country: $edd_cc_address.find('#billing_country').val()
		};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: edd_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (tax_response) {
				recalculate_taxes();
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});
	});

	function recalculate_taxes( state ) {
		if( '1' != edd_global_vars.taxes_enabled )
			return; // Taxes not enabled

		var $edd_cc_address = $('#edd_cc_address');

		if( ! state ) {
			state = $edd_cc_address.find('#card_state').val();
		}

		var postData = {
			action: 'edd_recalculate_taxes',
			billing_country: $edd_cc_address.find('#billing_country').val(),
			state: state
		};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: edd_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (tax_response) {
				$('#edd_checkout_cart').replaceWith(tax_response.html);
				$('.edd_cart_amount').html(tax_response.total);
				var tax_data = new Object();
				tax_data.postdata = postData;
				tax_data.response = tax_response;
				$('body').trigger('edd_taxes_recalculated', [ tax_data ]);
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});
	}
});