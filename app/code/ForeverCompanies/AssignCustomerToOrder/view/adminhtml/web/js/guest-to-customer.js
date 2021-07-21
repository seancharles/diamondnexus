define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm'
], function ($, alert, confirm) {


$(document).ready(function() {
	
    window.guestToCustomerButtonClick = function(url, orderId, msg){
		postData(url, orderId);
    };

    var displayMsg = function(url, orderId, msg){
        confirm({
            content: msg,
            actions: {
                confirm: function () {
                    alert(url);
                    postData(url, orderId);
                }
            }
        });
    };


    var postData = function(url, orderId){
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
                form_key: FORM_KEY,
                order_id: orderId
            },
            showLoader: true
        }).done(function(response) {

            if (typeof response === 'object') {
                if (response.error) {
                    alert({ title: 'Error', content: response.message });
                } else if (response.ajaxExpired && response.ajaxRedirect) {
                    window.location.href = response.ajaxRedirect;
                }
                else{
                    location.reload();
                }
            }


        });
    }
});


});
