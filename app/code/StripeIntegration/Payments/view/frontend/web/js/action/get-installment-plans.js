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
        return function (paymentMethodId)
        {
            var serviceUrl = urlBuilder.createUrl('/stripe/payments/get_installment_plans', {});

            var payload = {
                paymentMethodId: paymentMethodId
            };

            return storage.post(serviceUrl, JSON.stringify(payload));
        };
    }
);
