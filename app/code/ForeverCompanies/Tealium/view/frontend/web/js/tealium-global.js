/**
 * Global class managing events for Tealium & M2;
 * tracking links, form submits, etc.
 * Note:  Site-specific variables included prior to this under var fcTealSiteEventsData
 */
const FcTeal = (function () {

    const settings = {
        sendUtag: true,
        defaults: {
            events: {
                tData: {
                    "tealium_event": "default",
                    "event_category": "", // ga
                    "event_action": "", // ga
                    "event_label": "", // ga
                    "event_value": "" // ga
                }
            }
        }
    };
    const transmit = {
        view: function (eData, eCallback, eTagIdsAry) {
            if (eData === '') return false;
            if (eCallback === '') eCallback = null;
            if (eTagIdsAry === '') eTagIdsAry = null;
            console.log('Sending view data to Tealium: ' + JSON.stringify(eData));
            if (settings.sendUtag) return window.utag.link(eData, eCallback);
        },
        link: function (eData, eCallback, eTagIdsAry) {
            if (eData === '') return false;
            if (eCallback === '') eCallback = false;
            if (eTagIdsAry === '') eTagIdsAry = false;
            console.log('Sending link data to Tealium: ' + JSON.stringify(eData));
            if (eCallback) {
                if (settings.sendUtag) return window.utag.link(eData, eCallback);
            }
            if (settings.sendUtag) return window.utag.link(eData);
        }
    };


    const utils = {

        stringify: function (eData) {
            if (utils.isobj(eData)) {
                eData = JSON.stringify(eData);
            }
            return eData;
        },
        parse: function (eData) {
            // convert any single quotes to double and then parse
            if (eData !== '') eData = JSON.parse(eData.replace(/'/g, '"'));
            return eData;
        },
        merge: function (objBase, objNew) {
            return Object.assign(objBase, objNew); // For merging attrs
        },
        isobj: function (eData) {
            // Check if this value is an object or array
            return (typeof eData === "object" && !Array.isArray(eData) && eData !== null);
        }
    };

    const redirect = function (eUrl, addHistory) {
        if (!settings.sendUtag) return false;
        if (addHistory === '') addHistory = true;
        (addHistory) ? window.location.href = eUrl : window.location.replace(eUrl);
        return true;
    };

    const form = {

        submit: function (eFormTarget) {
            return eFormTarget.submit();
        }

    };

    const track = {

        link: function (event) {

            if (!event) return false;

            // Event parse
            let eData = {};
            let eTarget = event.target;
            let eTargetParent = eTarget.parentNode;
            let eName = eTarget.getAttribute('data-track');
            let eAttrs = eTarget.getAttribute('data-track-attrs'); // e.g. data-track-attrs='{ "where": "header" }'

            // Handle for link click
            if (eTarget.nodeName === 'A') {

                // Init Tealium with global defaults
                let tData = window.fcTealSiteEventsData.get.site;
                utils.merge(tData, settings.defaults.events.tData);
                console.log('tData init: ' + JSON.stringify(tData));

                // Set defaults
                tData.tealium_event = eName;

                // Populate event data using site-specific defaults
                if (window.fcTealSiteEventsData) {
                    let siteEventsData = window.fcTealSiteEventsData.get.links(eName, eTarget);
                    console.log('siteEventsData=' + JSON.stringify(siteEventsData));
                    utils.merge(tData, siteEventsData);
                }

                // Add any overrides from data-track-attrs
                if (eAttrs) {
                    let eAttrsObj = utils.parse(eAttrs);
                    utils.merge(tData, eAttrsObj);
                }

                // Debug
                console.log('Link clicked: ' + eName);

                transmit.link(tData, redirect(eTarget.getAttribute('href'), true));


            }

            return true;

        },
        form: function (formObj, formSubmit) {

            if (!formObj) return false;
            if (formSubmit !== false) formSubmit = true;

            console.log('formSubmit = ' + formSubmit);

            // Parse form
            let eTarget = formObj;
            let eTargetParent = eTarget.parentNode;
            let eName = eTarget.getAttribute('data-track');
            let eAttrs = eTarget.getAttribute('data-track-attrs');

            // Init Tealium with global defaults
            let tData = window.fcTealSiteEventsData.get.site;
            utils.merge(tData, settings.defaults.events.tData);
            console.log('tData init: ' + JSON.stringify(tData));

            // Set defaults
            tData.tealium_event = eName;

            // Populate event data using site-specific defaults
            if (window.fcTealSiteEventsData) {
                let siteEventsData = window.fcTealSiteEventsData.get.forms(eName, eTarget);
                console.log('siteEventsData=' + JSON.stringify(siteEventsData));
                utils.merge(tData, siteEventsData);
            }

            // Add any overrides from data-track-attrs
            if (eAttrs) {
                let eAttrsObj = utils.parse(eAttrs);
                utils.merge(tData, eAttrsObj);
            }

            // Debug
            console.log('Form submitted: ' + eName);

            if (formSubmit) {
                transmit.link(tData, form.submit(eTarget));
            } else {
                transmit.link(tData);
            }
            return true;

        }

    };

    return {
        trackLink: track.link,
        trackForm: track.form
    };

})();

// Including legacy method aliases
function fcTealTrackForm(formObj, formSubmit) {
    let result = window.FcTeal.trackForm(formObj, formSubmit);
    console.log('fcTealTrackForm called. Result = ' + result);
}

function fcTealTrackLink(event) {
    let result = window.FcTeal.trackLink(event);
    console.log('fcTealTrackLink called. Result = ' + result);
}

// Listen for tracked link clicks
document.addEventListener('click', function (event) {
    console.log('Clicked on ' + event.target);
    // If the clicked element doesn't have tracking attribute, bail
    if (!event.target.hasAttribute('data-track')) return;
    // Else we are handling. Don't follow links or submit forms
    event.preventDefault();
    return fcTealTrackLink(event);
}, false);

// Listen for tracked form submits
document.addEventListener('submit', function (event) {
    // -- 6/12/2020, added by SteveC
    // added form attribute 'data-do-not-submit-form' to handle scenario where we do not want to
    // automatically submit FA modal form on homepage because it's being handled through ajax
    let doNotSubmitForm = !!(event.target.hasAttribute('data-do-not-submit-form'));
    // old format of above code: (event.target.hasAttribute('data-do-not-submit-form')) ? true : false;

    // If the clicked element doesn't have tracking attribute, bail
    if (!event.target.hasAttribute('data-track') || doNotSubmitForm) {
        return;
    }
    // Else we are handling. Don't follow links or submit forms
    event.preventDefault();
    return fcTealTrackForm(event.target);
}, false);