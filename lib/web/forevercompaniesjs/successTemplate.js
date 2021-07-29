require(['forevercompaniesjs/flatpickr/flatpickr.min'], function (flatpickr) {
    'use strict'
    function fcSuccessTemplateScript() {
        let calendar = document.getElementById('js-calendar-success')
        if (calendar != null) {
            console.log('success if working')
            console.log(typeof calendar)
            if (calendar.hasAttribute('data-date')) {
                let delivery_date = calendar.getAttribute('data-date')
                console.log('delivery_date', delivery_date)
                flatpickr.l10ns.default.weekdays.shorthand = [
                    'S',
                    'M',
                    'T',
                    'W',
                    'T',
                    'F',
                    'S',
                ]
                flatpickr('#js-calendar-success', {
                    inline: true,
                    defaultDate: delivery_date,
                })
            }
        }
        console.log('success template js', flatpickr)
    }
    var callbackFCCustomMag = function () {
        fcSuccessTemplateScript()
    }
    if (
        document.readyState === 'complete' ||
        (document.readyState !== 'loading' &&
            !document.documentElement.doScroll)
    ) {
        callbackFCCustomMag()
    } else {
        document.addEventListener('DOMContentLoaded', callbackFCCustomMag)
    }
    return
})
