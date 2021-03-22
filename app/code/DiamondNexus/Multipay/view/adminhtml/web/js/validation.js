define(['jquery', 'jquery/ui', 'domReady!'], function ($) {
    'use strict'

    $('#multipay_option_partial').bind('keyup mouseup', function () {
        console.log('Trigger Amount to Pay ')
        var amount_to_pay = $('#multipay_option_partial').val()
        var multipay_amount_due = $('#multipay_amount_due').val()
        var balance_due = multipay_amount_due - amount_to_pay
        $('#multipay_new_balance').val(balance_due)
    })

    $('#multipay_cash_tendered').on('input', function () {
        console.log('Trigger Cash tendered')
        var amount_due = $('#multipay_amount_due').val()
        console.log('amount_due = ' + amount_due)
        var multipay_cash_tendered = $('#multipay_cash_tendered').val()
        console.log('multipay_cash_tendered = ' + multipay_cash_tendered)

        res = multipay_cash_tendered - amount_due
        console.log('multipay_cash_tendered - amount_due = ' + res)

        if (res > 0) {
            var res = multipay_cash_tendered - amount_due
            $('#multipay_cash_tendered').val(res)
            console.log(
                'multipay_cash_tendered = ' + $('#multipay_cash_tendered').val()
            )
        }
    })
})
