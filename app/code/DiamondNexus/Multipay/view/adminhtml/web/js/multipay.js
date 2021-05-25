define(['jquery', 'multipay'], function ($, multipay) {
    'use strict'
    return function multipay() {
        $('.admin__control-radio').click(function () {
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
    }
})