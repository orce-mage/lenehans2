/**
 * Paypal Express Review page actions
 */
define([
    'jquery',
    'mage/tooltip',
    'mage/translate'
], function ($, tooltip) {
    'use strict';

    return {
        options: {
            // Paypal express review page selectors
            paypalShippingAddressSelector: '.paypal-review .box-order-shipping-address .box-content address:not(.ampickup-details-container)',
            shippingAddressEditLinkSelector: '.paypal-review .box-order-shipping-address .action.edit',
            tooltipText: $.mage.__('Editing is not applicable for the selected shipping method'),
            storeDetailsPlaceholder: $.mage.__('Please, choose a store where you would like to pick up your order')
        },

        /**
         * Toggle Shipping address block depending on selected shipping method and Pickup Store
         *
         * @param {Boolean} state
         * @param {Object} storeInfo
         */
        toggleShippingAddress: function (state, storeInfo) {
            var storeAddressString,
                pickupAddressHtml = $('[data-ampickup-js="store-address"]');

            if (state) {
                if (!pickupAddressHtml.length) {
                    pickupAddressHtml = $('<address/>', {
                        'class': 'ampickup-details-container',
                        'data-ampickup-js': 'store-address'
                    });
                    $(this.options.paypalShippingAddressSelector).after(pickupAddressHtml);
                }

                if (storeInfo) {
                    storeAddressString = storeInfo.details;
                } else {
                    storeAddressString = this.options.storeDetailsPlaceholder;
                }

                pickupAddressHtml.html(storeAddressString);
            }

            $(this.options.paypalShippingAddressSelector).toggle(!state);
            pickupAddressHtml.toggle(state);
            this.toggleShippingEditLink(state);
        },

        /**
         * Toggle Edit Shipping Address link depending on selected Shipping Method
         * Add Tooltip for Edit Shipping Address link
         *
         * @param {Boolean} state
         */
        toggleShippingEditLink: function (state) {
            var shippingEditLink = $(this.options.shippingAddressEditLinkSelector),
                tooltipStatus = state ? 'enable' : 'disable';

            shippingEditLink.attr('title', this.options.tooltipText);
            shippingEditLink.tooltip({
                position: {
                    my: "left",
                    at: "right+15"
                }
            });

            shippingEditLink.tooltip(tooltipStatus);
            shippingEditLink.toggleClass('-disabled', state);
            shippingEditLink.off('click').on('click', function (event) {
                if ($(event.target).is('.-disabled') || $(event.target).closest('.-disabled').length) {
                    event.preventDefault()
                }
            });
        }
    }
});
