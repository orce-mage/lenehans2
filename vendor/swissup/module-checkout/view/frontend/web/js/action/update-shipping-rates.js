define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (quote, shippingRateRegistry, shippingRateService) {
    'use strict';

    return function () {
        var address = quote.shippingAddress();

        shippingRateRegistry.set(address.getCacheKey(), '');
        shippingRateService.getProcessor(address.getType()).getRates(address);
    };
});
