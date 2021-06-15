// Tealium tracking init for www.foreverartisans.com
var fcTealSiteEventsData = {
    get: {
        site: {
            site_name: ["www", "foreverartisans"],
            brand_name: "Forever Artisans",
            brand: "fa"
        },
        links: function (eName, eTarget) {
            var eData = {};

            switch (eName) {
                case "social_click":
                    eData.tealium_event = "social_click";
                    eData.link_category = "footer";
                    eData.link_action = eTarget.getAttribute("href");
                    eData.link_name = "logo";

                    // For ga events
                    eData.event_category = "social";
                    eData.event_action = "visit";
                    eData.event_label = eData.link_action;
                    eData.event_value = "";
                    break;

                case "referral_click":
                    eData.tealium_event = "referral_click";
                    eData.link_category = "footer";
                    eData.link_action = eTarget.getAttribute("href");
                    eData.link_name = "link";

                    // For ga events
                    eData.event_category = "referral";
                    eData.event_action = "visit";
                    eData.event_label = eData.link_action;
                    eData.event_value = "";
                    break;

                default:
                    break;
            }

            return eData;
        },
        forms: function (eName, eTarget) {
            var eData = {};

            switch (eName) {
                case "mailinglist_subscribe":
                    eData.link_category = "footer";
                    eData.link_action = "submit";
                    eData.link_name = "button";
                    eData.customer_email = eTarget.elements["email"].value.trim();

                    // For ga events
                    eData.event_category = "mailinglist";
                    eData.event_action = "subscribe";
                    eData.event_label = "page_footer";
                    break;

                case "search":
                    eData.tealium_event = "search_keyword";
                    eData.link_category = "header";
                    eData.link_action = "search";
                    eData.search_keyword = eTarget.elements["q"].value;
                    break;

                case "getstarted":
                    //eData.tealium_event = "design";
                    eData.link_category = "inline";
                    eData.link_action = "submit";
                    eData.link_name = "button";
                    eData.form_name = "getstarted";
                    //eData.form_category = '';
                    //eData.form_data = '';
                    //eData.form_summary = eTarget.elements["summary"].value;

                    eData.customer_email = eTarget.elements["email"].value.trim();
                    eData.customer_first_name = eTarget.elements[
                        "first_name"
                        ].value.trim();
                    eData.customer_last_name = eTarget.elements["last_name"].value.trim();
                    //eData.customdesign_category = eTarget.elements["design"].value;

                    eData.customer_phone = eTarget.elements["phone"].value.trim();

                    // For ga events
                    eData.event_category = "customdesign";
                    eData.event_action = "request";
                    eData.event_label = "getstarted";
                    break;

                case "getstarted_details":
                    //eData.tealium_event = "getstarted";
                    eData.form_name = "getstarted_details";
                    //eData.form_category = 'info';
                    //eData.form_data = '';
                    //eData.form_summary = eTarget.elements["summary"].value;

                    eData.customer_email = eTarget.elements["email"].value.trim();
                    eData.customer_last_name = eTarget.elements["last_name"].value.trim(); // Check on phone

                    // For ga events
                    eData.event_category = "customdesign";
                    eData.event_action = "info";
                    eData.event_label = "getstarted_details";
                    break;

                case "contest_entry":
                    if (eTarget.getAttribute("id") == "giveawaySliderForm") {
                        // On lead pages - email only
                        eData.link_category = "inline";
                        eData.link_name = "button";
                        eData.link_action = "enter now";
                        eData.customer_email = eTarget.elements[
                            "email_address"
                            ].value.trim(); // For ga events

                        eData.event_category = "mailinglist";
                        eData.event_action = "subscribe";
                        eData.event_label = "highlight_contest";
                    } else {
                        eData.link_category = "modal";
                        eData.link_name = "button";

                        if (
                            !("firstname" in eTarget.elements) ||
                            eTarget.elements["firstname"].value == ""
                        ) {
                            // Step 1:  Contest entry
                            eData.link_action = "enter to win";
                            eData.customer_email = eTarget.elements[
                                "email_address"
                                ].value.trim();
                            eData.customer_gender =
                                "gender" in eTarget.elements
                                    ? eTarget.elements["gender"].value
                                    : ""; // For ga events

                            eData.event_category = "mailinglist";
                            eData.event_action = "subscribe";
                            eData.event_label = "modal_contest";
                        } else {
                            // Step 2:  Catalog request
                            eData.tealium_event = "catalog_request";
                            eData.link_action = "get free catalog";
                            eData.customer_email = eTarget.elements[
                                "email_address"
                                ].value.trim();
                            eData.customer_first_name = eTarget.elements[
                                "firstname"
                                ].value.trim();
                            eData.customer_last_name = eTarget.elements[
                                "lastname"
                                ].value.trim();
                            eData.customer_city = eTarget.elements["city"].value.trim();
                            eData.customer_state = eTarget.elements["state"].value.trim();
                            eData.customer_postal_code = eTarget.elements["zip"].value.trim();
                            //eData.is_shopping_engagement = '';
                            // For ga events

                            eData.event_category = "catalog";
                            eData.event_action = "request";
                            eData.event_label = "modal";
                        }
                    }

                    break;

                case "cart_add":
                    var cartProductIds = [];
                    cartProductIds.push(eTarget.elements["product"].value);

                    if (eTarget.elements["related_product"].value != "") {
                        cartProductIds.push(eTarget.elements["related_product"].value);
                    }

                    eData.link_name = "button";
                    eData.link_category = "form";
                    eData.link_action = "cart_add";
                    eData.link_text = "Add to cart";
                    eData.product_id = cartProductIds;
                    eData.product_name = new Array(
                        document.getElementsByTagName("h1")[0].textContent
                    );

                    if (
                        eTarget.elements["super_attribute[145]"] &&
                        eTarget.elements["super_attribute[145]"].value != ""
                    ) {
                        eData.product_metal = new Array(
                            eTarget.elements["super_attribute[145]"].selectedOptions[0].text
                        );
                    } // metal

                    if (
                        eTarget.elements["super_attribute[149]"] &&
                        eTarget.elements["super_attribute[149]"].value != ""
                    ) {
                        eData.product_stone_size = new Array(
                            eTarget.elements["super_attribute[149]"].selectedOptions[0].text
                        );
                    } // center stone

                    if (
                        eTarget.elements["options[6360]"] &&
                        eTarget.elements["options[6360]"].value != ""
                    ) {
                        eData.product_ring_size = new Array(
                            eTarget.elements["options[6360]"].selectedOptions[0].text
                        );
                    } // ring size

                    // For ga events
                    eData.event_category = "cart";
                    eData.event_action = "add";
                    eData.event_label = eTarget.elements["product"].value;
                    eData.event_value = "";
                    break;

                case "catalog_request":
                    eData.customer_first_name = eTarget.elements[
                        "first_name"
                        ].value.trim();
                    eData.customer_last_name = eTarget.elements["last_name"].value.trim();
                    eData.customer_email = eTarget.elements["email"].value.trim();
                    eData.customer_city = eTarget.elements["city"].value.trim();
                    eData.customer_state = eTarget.elements["State"].value.trim();
                    eData.customer_postal_code = eTarget.elements["zip"].value.trim();
                    //eData.is_shopping_engagement = eTarget.elements["engagement"].value;

                    eData.link_category = "form";
                    eData.link_action = "Send my free catalog!";
                    eData.link_name = "button";

                    // For ga events
                    eData.event_category = "catalog";
                    eData.event_action = "request";
                    eData.event_label = "page";
                    eData.event_value = "";
                    break;

                default:
                    break;
            }

            return eData;
        }
    }
};
