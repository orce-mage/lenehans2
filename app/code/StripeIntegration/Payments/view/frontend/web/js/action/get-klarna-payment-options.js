define(
    [
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
    ],
    function (urlBuilder, storage, errorProcessor, fullScreenLoader, quote) {
        'use strict';
        return function (shippingAddress, guestEmail, sourceId) {
            var serviceUrl = urlBuilder.createUrl('/stripe/payments/get_klarna_payment_options', {});

            var payload = {
                shippingAddress: shippingAddress,
                shippingMethod: null,
                sourceId: sourceId
            };

            if (typeof guestEmail == "string" && guestEmail.length > 0)
                payload.guestEmail = guestEmail;

            var shippingMethod = quote.shippingMethod();
            if (shippingMethod && typeof shippingMethod.method_title != "undefined")
                payload.shippingMethod = shippingMethod.method_title + " (" + shippingMethod.carrier_title + ")";

            return storage.post(serviceUrl, JSON.stringify(payload));
        };
    }
);
