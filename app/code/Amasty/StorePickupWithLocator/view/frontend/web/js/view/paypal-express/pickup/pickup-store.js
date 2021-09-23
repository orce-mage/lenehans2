/**
 * Pickup Store UIElement for Paypal express review page
 * Nested from main Pickup Store UIElement
 */
define([
    'jquery',
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-store',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/action/paypal-express-actions',
    'Amasty_StorePickupWithLocator/js/model/resource-url-manager',
    'Amasty_StorePickupWithLocator/js/action/messages-resolver'
], function (
    $,
    PickupStore,
    storage,
    quote,
    pickupDataResolver,
    paypalExpressActions,
    urlManager,
    messagesResolver
) {
    'use strict';

    return PickupStore.extend({
        defaults: {
            visible: false,
            required: true,
            template: 'Amasty_StorePickupWithLocator/checkout/pickup/pickup-store',
            pickupMethodName: 'amstorepickup_amstorepickup'
        },

        initialize: function () {
            this._super();

            // Paypal Express Checkout isn't a UIComponent structure, so here we need to use jquery selection.
            var shippingMethodElement = $('#shipping-method');

            shippingMethodElement.on('change', this.onShippingMethodChange.bind(this));

            return this;
        },

        onShippingMethodChange: function () {
            var isPickup = event.target.value === this.pickupMethodName;

            paypalExpressActions.toggleShippingAddress(isPickup, null);
        },

        onChangeStore: function (storeId) {
            this._super();

            var storeInfo = pickupDataResolver.getCurrentStoreData();

            paypalExpressActions.toggleShippingAddress(true, storeInfo);

            if (!storeId) {
                return;
            }

            messagesResolver.clearMessages();

            $.ajax({
                url: urlManager.getPathUrl('amstorepickup/paypal/saveShippingAddress'),
                data: {location_id: storeId},
                type: 'post',
                success: function (response) {
                    if (response) {
                        $('#details-reload').html(response);
                    }
                }
            });
        },

        pickupStateObserver: function (isActive) {
            this._super();

            this.visible(isActive);
        }
    });
});
