/**
 * MSI locations model
 */

define([
    'jquery',
    'ko',
    'mage/storage',
    'Amasty_StorePickupWithLocatorMSI/js/model/url-builder'
], function ($, ko, storage, urlBuilder) {
    'use strict';

    return {
        deliveryMessage: ko.observableArray(),

        /**
         * @param {String} productId
         * @return {void}
         */
        getDeliveryMessageByProductId: function (productId) {
            var urlForCreation = '/midoriweb_delivery_message/:productId/getDeliveryMessageByProduct',
                deliveryMessageApiUrl;

            if (!productId) {
                return;
            }

            deliveryMessageApiUrl = urlBuilder.createUrl(urlForCreation, {
                productId: productId
            });

            storage
                .get(deliveryMessageApiUrl)
                .success(function (response) {
                    if (!response.delivery_message) {
                        return;
                    }

                    this.setDeliveryMessage(response.delivery_message);

                }.bind(this))
                .fail(function (response) {
                    console.log(response);
                })
                .always(function () {

                });
        },

        /**
         * @param {Array} locations
         * @returns {void}
         */
        setDeliveryMessage: function (deliveryMessage) {
            if(deliveryMessage.from_day == '') {
                deliveryMessage = '';
            }
            this.deliveryMessage(deliveryMessage);
        }
    };
});
