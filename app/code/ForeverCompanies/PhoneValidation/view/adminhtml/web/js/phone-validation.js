require(['jquery', 'jquery/ui', 'mage/validation/validation'], function($, ui, validation){ 
	
	$( document ).ready(function() {
	    $("#order-billing_address_telephone").addClass("validate-phoneStrict");
		$("#order-shipping_address_telephone").addClass("validate-phoneStrict");
	});
	
	$( document ).ajaxComplete(function( event,request, settings ) {
		$("#order-billing_address_telephone").addClass("validate-phoneStrict");
		$("#order-shipping_address_telephone").addClass("validate-phoneStrict");
	});

});
