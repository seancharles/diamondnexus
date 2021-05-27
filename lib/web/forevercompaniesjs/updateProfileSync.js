var callbackFCCustomMag = function () {
    //tell static pages to update the profile after visiting a magento page
    window.localStorage.setItem('fcProfileUpdate', 'true')
}
if (
    document.readyState === 'complete' ||
    (document.readyState !== 'loading' && !document.documentElement.doScroll)
) {
    callbackFCCustomMag()
} else {
    document.addEventListener('DOMContentLoaded', callbackFCCustomMag)
}
