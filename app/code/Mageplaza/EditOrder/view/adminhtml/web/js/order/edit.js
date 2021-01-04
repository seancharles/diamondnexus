/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    $.widget('mageplaza.mpEditOrder', {
        params: {
            prefix: '',
            editBtn: '',
            locateBtn: '',
            popup: '',
            popupTitle: '',
            errorMessage: '',
            isValid: false
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            this.setLocate();
            this._EventListener();
        },

        /**
         * Set locate for edit button
         */
        setLocate: function () {
            if ($(this.params.locateBtn).length) {
                $(this.params.editBtn).appendTo($(this.params.locateBtn));
            }
        },

        /**
         * Event open and submit edit popup
         *
         * @private
         */
        _EventListener: function () {
            var _this = this;

            $('body').on('click', this.params.editBtn, function (e) {
                var url   = $(this).attr('href') ? $(this).attr('href') : $('#mpeditorder-quick-edit-btn').attr('href'),
                    popup = $(_this.params.popup);

                e.preventDefault();
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true,
                    success: function (res) {
                        if (res.success) {
                            popup.html(res.success);
                            popup.trigger('contentUpdated');
                            $('body').trigger('contentUpdated');
                            _this.updateForm();
                            _this.openPopup(popup);
                        }
                    },
                    error: function (res) {
                        popup.html(res.responseText);
                    }
                });
            });
        },

        /**
         * Open popup when click edit button
         *
         * @param popup
         */
        openPopup: function (popup) {
            var _this = this,
                oldData,
                form  = $('#edit_form');

            modal({
                autoOpen: true,
                responsive: true,
                type: 'slide',
                clickableOverlay: true,
                modalClass: 'mpeditorder-popup',
                title: $t(_this.params.popupTitle),
                buttons: [{
                    text: $t('Update'),
                    class: 'action mpeditorder-submit primary',
                    click: function () {

                        _this.processUpdateOrder(form, oldData, this);
                    }
                }]
            }, form);

            form.modal('openModal').on('modalclosed', function () {
                popup.html('');
            });

            /** convert format form **/
            form.modal('openModal').on('modalopened', function () {
                oldData = form.serialize();
            });
        },

        /**
         * Process result update order
         * @param form
         * @param oldData
         * @param modal
         */
        processUpdateOrder: function (form, oldData, modal) {
            var self           = this,
                successMessage = $('#mpeditorder-messages .message-success'),
                errorMessage   = $(self.params.errorMessage);

            form.validate({
                errorClass: 'mage-error'
            });
            if (self.params.isValid || form.validation('isValid')) {
                var form_data = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'newData': form_data,
                        'oldData': oldData
                    },
                    showLoader: true,
                    success: function (response) {
                        var isError = false, error;

                        if (typeof response === 'undefined' || response.length === 0) {
                            modal.closeModal();
                        } else if (!self.params.prefix
                            || typeof response[self.params.prefix] !== 'undefined') {
                            $.each(response, function (key, value) {
                                if (typeof response[key].error !== 'undefined') {
                                    isError = true;
                                    error   = value;
                                }
                            });

                            if (isError) {
                                errorMessage.text(error.message);
                                errorMessage.show();
                            } else {
                                self.updateData(response);
                                successMessage.show();
                                errorMessage.hide();
                                modal.closeModal();
                                $('html, body').animate({scrollTop: 0}, 'slow');
                            }
                        }
                    },
                    error: function (response) {
                        var popup = $(self.params.popup);

                        popup.html(response.responseText);
                    }
                });
            } else {
                /** auto expand if not validate */
                var elementError = form.validate().errorList[0].element;

                if (elementError) {
                    var parent = $('#' + elementError.id).closest('.mp_collapsible');

                    if (parent.hasClass('_hide')) {
                        parent.find('.title').trigger('click');
                    }
                }
            }
        },

        /**
         * Update form after load html
         */
        updateForm: function () {
        },

        /**
         * Update text in order view after submit success
         */
        updateData: function (res) {
            return res;
        },

        /**
         * Update text in order view after submit success
         */
        updateOrderInfo: function () {
            var orderNumberEl  = $('.order-information .title, .page-title-wrapper .page-title'),
                orderInfoTable = $('.order-information-table tr td'),
                orderNumber    = $('#mpeditorder-order-number').val(),
                orderDate      = $('#mpeditorder-order-date').val(),
                orderStatus    = $('#mpeditorder-order-status :selected').text();

            orderNumberEl.text($t('Order # ' + orderNumber));
            orderInfoTable.eq(0).text(orderDate);
            orderInfoTable.eq(1).text(orderStatus);
        },

        /**
         * Update text in order view after submit success
         */
        updateOrderCustomer: function () {
            var email            = $('#mpeditorder-customer-email').val(),
                firstName        = $('#mpeditorder-customer-first-name').val(),
                lastName         = $('#mpeditorder-customer-last-name').val(),
                customerGroup    = $('#mpeditorder-customer-group :selected').text(),
                accountInfoTable = $('.order-account-information-table tr td');

            accountInfoTable.eq(0).text(firstName + ' ' + lastName);
            accountInfoTable.eq(1).find('a').text(email);
            accountInfoTable.eq(2).text(customerGroup);
        },

        /**
         * Update html billing address after submit
         * @param res
         */
        updateBillingAddress: function (res) {
            $('.order-billing-address address').html(res.success);
        },

        /**
         * Update html shipping address after submit
         * @param res
         */
        updateShippingAddress: function (res) {
            $('.order-shipping-address address').html(res.success);

            if (this.options.isRecalculateShippingFee) {
                $('#mpeditorder-shipping-method-btn').trigger('click');
            }
        },

        /**
         * Update html shipping method after submit
         * @param res
         */
        updateShippingMethod: function (res) {
            var newShippingMethod = $('.new-shipping-method'),
                shippingContent;

            newShippingMethod.html(res.success.shippingMethodHtml);
            shippingContent = newShippingMethod.find('.admin__page-section-item-content').html();

            $('.order-shipping-method .admin__page-section-item-content').first().html(shippingContent);
            $('.order-totals table').html(res.success.orderTotalHtml);
        },

        /**
         * update html items and order total after submit edit items form
         * @param res
         */
        updateItems: function (res) {
            $('.order-totals table').html(res.success.orderTotalHtml);
            $('.edit-order-table').parent().html(res.success.itemsHtml);
        },

        /**
         * Update html payment method after submit
         * @param res
         */
        updatePaymentMethod: function (res) {
            $('.order-payment-method .admin__page-section-item-content').html(res.success);
        },

        /**
         * datetime picker
         */
        editDateTime: function () {
            $('#mpeditorder-order-date').datetimepicker({
                changeYear: true,
                changeMonth: true,
                showsTime: true,
                showSecond: true,
                dateFormat: 'MM dd, yy',
                ampm: true,
                timeFormat: 'hh:mm:ss TT'
            });
        },

        /**
         * depend select type modify customer
         */
        dependent: function () {
            var self   = this,
                select = $('#mpeditorder-modify-type');

            self.initCustomerGrid();
            select.on('change', function () {
                self.actionDependent();
            });

            select.trigger('change');
        },

        /**
         * Load customer grid
         */
        initCustomerGrid: function () {
            var self = this;

            $.ajax({
                method: 'POST',
                url: self.options.url,
                data: {formKey: window.FORM_KEY},
                showLoader: true
            }).done(function (response) {
                $('#mpeditorder-select-customer-grid').html(response);
                self.autoFillData();
            });
        },

        /**
         * Auto fill data when select customer grid
         */
        autoFillData: function () {
            var self            = this;

            $('body').delegate('#mpeditorder-customer-grid tbody tr', 'click', function () {
                $(this).find('input').attr('checked', 'checked');
                var data       = {},
                    rootElData = $(this).closest('tr');

                data.id        = rootElData.find('td.col-entity_id').text().trim();
                data.webId     = rootElData.find('td.col-website_id').text().trim();
                data.email     = rootElData.find('td.col-email').text().trim();
                data.prefix    = rootElData.find('td.col-prefix').text().trim();
                data.firstName = rootElData.find('td.col-firstname').text().trim();
                data.midName   = rootElData.find('td.col-middlename').text().trim();
                data.lastName  = rootElData.find('td.col-lastname').text().trim();
                data.suffix    = rootElData.find('td.col-suffix').text().trim();
                data.groupId   = rootElData.find('td.col-group_id').text().trim();
                data.dob       = rootElData.find('td.col-dob').text().trim();
                data.taxvat    = rootElData.find('td.col-taxvat').text().trim();
                data.gender    = rootElData.find('td.col-gender').text().trim();

                self.fillAction(data);
            });
        },

        /**
         * process depend
         */
        actionDependent: function () {
            var self      = this,
                type      = $('#mpeditorder-modify-type').val(),
                email     = $('#mpeditorder-customer-email'),
                grid      = $('#mpeditorder-select-customer-grid'),
                gridRadio = $('#mpeditorder-customer-grid').find('input[type="radio"]');

            if (type === 'edit') {
                email.prop('readonly', false);
                self.fillAction(window.mpCustomerData.currentCustomer);
                grid.hide();
            } else {
                gridRadio.attr("checked", false);
                gridRadio.first().trigger('click');
                email.prop('readonly', true);
                grid.show();
            }
        },

        /**
         * process fill data
         * @param data
         */
        fillAction: function (data) {
            $('#mpeditorder-customer-website-id').val(data.webId);
            $('#mpeditorder-customer-email').val(data.email);
            $('#mpeditorder-customer-name-prefix').val(data.prefix);
            $('#mpeditorder-customer-first-name').val(data.firstName);
            $('#mpeditorder-customer-middle-name').val(data.midName);
            $('#mpeditorder-customer-last-name').val(data.lastName);
            $('#mpeditorder-customer-name-suffix').val(data.suffix);
            $('#mpeditorder-customer-group').val(data.groupId);
            $('#mpeditorder-customer-dob').val(data.dob);
            $('#mpeditorder-customer-taxvat').val(data.taxvat);
            $('#mpeditorder-customer-gender').val(data.gender ? data.gender : 1);
        }
    });

    return $.mageplaza.mpEditOrder;
});

