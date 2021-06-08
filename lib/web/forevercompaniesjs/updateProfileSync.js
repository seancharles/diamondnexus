// require(['forevercompaniesjs/dompurify/dompurify.min'], function (DOMPurify) {
//     'use strict'
//     function fcDomFix() {
//         let selects = document.querySelectorAll('option')
//         console.log('selects', selects)
//         if (selects != undefined && selects.length > 0) {
//             for (let i = 0; i < selects.length; i++) {
//                 console.log('purifying select', selects[i])
//                 if (typeof DOMPurify != undefined) {
//                     let clean = DOMPurify.sanitize(selects[i], {
//                         SAFE_FOR_JQUERY: true,
//                     })
//                     let newSelect = document.createElement('select')
//                     newSelect.innerHTML = clean
//                     selects[i].parentNode.replaceChild(newSelect, selects[i])
//                 } else {
//                     console.log('DOMPurify is undefined')
//                 }
//             }
//         }
//     }

//     var callbackFCCustomMag = function () {
//         fcDomFix()

//         //tell static pages to update the profile after visiting a magento page
//         window.localStorage.setItem('fcProfileUpdate', 'true')
//     }
//     if (
//         document.readyState === 'complete' ||
//         (document.readyState !== 'loading' &&
//             !document.documentElement.doScroll)
//     ) {
//         callbackFCCustomMag()
//     } else {
//         document.addEventListener('DOMContentLoaded', callbackFCCustomMag)
//     }
//     return
// })
require([], function () {
    'use strict'

    var callbackFCCustomMag = function () {
        //tell static pages to update the profile after visiting a magento page
        window.localStorage.setItem('fcProfileUpdate', 'true')
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
