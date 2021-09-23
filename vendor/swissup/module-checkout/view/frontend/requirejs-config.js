var config = {
    config: {
        mixins: {
            // Added ability to reload shipping rates
            'Magento_Checkout/js/model/shipping-rate-service': {
                'Swissup_Checkout/js/mixin/model/shipping-rate-service-mixin': true
            }
        }
    }
};
