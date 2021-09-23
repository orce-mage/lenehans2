var config = {
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Amasty_StorePickupWithLocatorMSI/js/mixins/swatch-renderer-mixin': true
            },
            'Magento_Catalog/js/validate-product': {
                'Amasty_StorePickupWithLocatorMSI/js/mixins/validate-product-mixin': true
            },
            'Amasty_Storelocator/js/main': {
                'Amasty_StorePickupWithLocatorMSI/js/mixins/locator-main-mixin': true
            }
        }
    }
};
