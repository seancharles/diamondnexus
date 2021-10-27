require(['forevercompaniesjs/flatpickr/flatpickr.min'], function (flatpickr) {
    'use strict'
    function fcSuccessTemplateScript() {
        let calendar = document.getElementById('js-calendar-success')
        if (calendar != null) {
            if (calendar.hasAttribute('data-date')) {
                let delivery_date = calendar.getAttribute('data-date')
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
