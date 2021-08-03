define(['jquery'], function ($) {
    'use strict';
    return function multipayadminaddpayment()
    {

        $('.admin__control-radio').click(function (event) {
            /*
                if ($('#multipay_method_cc').is(':checked')) {
                    $('.payment-method-options:nth-child(2)').css(
                        'display',
                        'block'
                    )
                    $('.payment-method-options:nth-child(3)').css(
                        'display',
                        'block'
                    )
                    $('.payment-method-options:nth-child(4)').css('display', 'none')
                    $('.payment-method-options:nth-child(5)').css(
                        'display',
                        'block'
                    )
                    $('.payment-method-options:nth-child(6)').css('display', 'none')
                }
            */

            if ($('#multipay_method_credit_offline').is(':checked')) {
                $('.payment-method-options:nth-child(2)').css(
                    'display',
                    'block'
                )
                $('.payment-method-options:nth-child(3)').css(
                    'display',
                    'block'
                )
                $('.payment-method-options:nth-child(4)').css('display', 'none')
                $('.payment-method-options:nth-child(5)').css('display', 'none')
                $('.payment-method-options:nth-child(6)').css('display', 'none')
            } else if ($('#multipay_method_cash').is(':checked')) {
                $('.payment-method-options:nth-child(2)').css(
                    'display',
                    'block'
                )
                $('.payment-method-options:nth-child(3)').css(
                    'display',
                    'block'
                )
                $('.payment-method-options:nth-child(4)').css('display', 'none')
                $('.payment-method-options:nth-child(5)').css('display', 'none')
                $('.payment-method-options:nth-child(6)').css(
                    'display',
                    'block'
                )
            } else if ($('#multipay_method_quote').is(':checked')) {
                $('.payment-method-options:nth-child(2)').css('display', 'none')
                $('.payment-method-options:nth-child(3)').css('display', 'none')
                $('.payment-method-options:nth-child(4)').css('display', 'none')
                $('.payment-method-options:nth-child(5)').css('display', 'none')
                $('.payment-method-options:nth-child(6)').css('display', 'none')
            } else if ($('#multipay_method_paypal_offline').is(':checked')) {
                $('.payment-method-options:nth-child(2)').css(
                    'display',
                    'block'
                )
                $('.payment-method-options:nth-child(3)').css(
                    'display',
                    'block'
                )
                $('.payment-method-options:nth-child(4)').css('display', 'none')
                $('.payment-method-options:nth-child(5)').css('display', 'none')
                $('.payment-method-options:nth-child(6)').css('display', 'none')
            } else if ($('#multipay_method_pl').is(':checked')) {
                $('.payment-method-options:nth-child(2)').css(
                    'display',
                    'block'
                )
                $('.payment-method-options:nth-child(3)').css(
                    'display',
                    'block'
                )
                $('.payment-method-options:nth-child(4)').css('display', 'none')
                $('.payment-method-options:nth-child(5)').css('display', 'none')
                $('.payment-method-options:nth-child(6)').css('display', 'none')
            } else if ($('#multipay_method_store_credit').is(':checked')) {
                $('#multipay_method_partial_balance').prop('checked',true)
                $('#multipay_option_partial').val($(this).data('amount'))

                var amount_to_pay = $('#multipay_option_partial').val()
                var multipay_amount_due = $('#multipay_amount_due').val()
                var balance_due = multipay_amount_due - amount_to_pay
                if (balance_due > 0) {
                    $('#multipay_new_balance').val(balance_due.toFixed(2))
                } else {
                    $('#multipay_new_balance').val('0.00')
                }

                $('.payment-method-options:nth-child(2)').css('display', 'none')
                $('.payment-method-options:nth-child(3)').css('display', 'none')
                $('.payment-method-options:nth-child(4)').css('display', 'none')
                $('.payment-method-options:nth-child(5)').css('display', 'none')
                $('.payment-method-options:nth-child(6)').css('display', 'none')
            }
            if ($('#multipay_method_partial_balance').is(':checked')) {
                $('.payment-method-options:nth-child(4)').css(
                    'display',
                    'block'
                )
            } else {
                $('.payment-method-options:nth-child(4)').css('display', 'none')
            }
            if (
                $('#multipay_method_quote').is(':checked') &&
                $('#multipay_method_partial_balance').is(':checked')
            ) {
                $('.payment-method-options:nth-child(4)').css('display', 'none')
            }
        })

        var amount_due = parseFloat($('#multipay_amount_due').val())
        var amount_due_rounded = amount_due.toFixed(2)
        $('#multipay_amount_due').val(amount_due_rounded)

        var balance_due = parseFloat($('#multipay_new_balance').val())
        var balance_due_rounded = balance_due.toFixed(2)
        $('#multipay_new_balance').val(balance_due_rounded)

        $('#multipay_change_due').val('0.00')

        $('#multipay_option_partial').bind('keyup mouseup', function () {
            var amount_to_pay = $('#multipay_option_partial').val()
            var multipay_amount_due = $('#multipay_amount_due').val()
            var balance_due = multipay_amount_due - amount_to_pay
            if (balance_due > 0) {
                $('#multipay_new_balance').val(balance_due.toFixed(2))
            } else {
                $('#multipay_new_balance').val('0.00')
            }
        })

        $('#multipay_cash_tendered').bind('keyup mouseup', function () {
            var multipay_amount_due = $('#multipay_amount_due').val()
            var multipay_cash_tendered = $('#multipay_cash_tendered').val()
            var change_due = multipay_cash_tendered - multipay_amount_due
            if (change_due > 0) {
                $('#multipay_change_due').val(change_due.toFixed(2))
            } else {
                $('#multipay_change_due').val('0.00')
            }
        })

        $('#multipay_option_partial').bind('blur', function () {
            let amount_to_pay = $('#multipay_option_partial').val()
            let multipay_amount_due = $('#multipay_amount_due').val()
            let balance_due = multipay_amount_due - amount_to_pay

            if (amount_to_pay > multipay_amount_due) {
                alert('Amount to pay must not be for more than is due.')
                // set the amount to pay to the amount due
                // $('#multipay_option_partial').val(Number.parseFloat(multipay_amount_due).toFixed(2))
                // this doesn't work and needs to force the order to update server-side
            } else {
                $('#multipay_option_partial').val(Number.parseFloat(amount_to_pay).toFixed(2))
            }

            if (balance_due > 0) {
                $('#multipay_new_balance').val(Number.parseFloat(balance_due).toFixed(2))
            } else {
                $('#multipay_new_balance').val('0.00')
            }
        })

        $('#multipay_cash_tendered').bind('blur', function () {
            let cash_tendered = $('#multipay_cash_tendered').val()
            let multipay_amount_due = $('#multipay_amount_due').val()
            let change_due = cash_tendered - multipay_amount_due

            $('#multipay_cash_tendered').val(Number.parseFloat(cash_tendered).toFixed(2))

            if (change_due > 0) {
                $('#multipay_change_due').val(Number.parseFloat(change_due).toFixed(2))
            } else {
                $('#multipay_change_due').val('0.00')
            }
        })
    }
})
