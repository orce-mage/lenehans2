/*
 * Product validation mixin
 */

define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/product/view/product-ids-resolver',
    'Amasty_StorePickupWithLocatorMSI/js/action/product-config',
    'Amasty_StorePickupWithLocatorMSI/js/model/msi-locations',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config'
], function (
    $,
    _,
    productIdsResolver,
    productConfigActions,
    msiLocationsModel,
    productConfig
) {
    'use strict';

    return function (ProductValidate) {
        $.widget('mage.ProductValidate', ProductValidate, {
            options: {
                selectors: {
                    addToCartForm: 'form#product_addtocart_form'
                }
            },

            _create: function () {
                var productId;

                this._super();

                productConfigActions.setConfigurableState();
                productConfigActions.setMsiEnabledState();

                if (productConfig.isMsiEnabled && !productConfig.isConfigurable()) {
                    productId = productIdsResolver($(this.options.selectors.addToCartForm))[0];
                    productConfigActions.setProductId(productId);
                    msiLocationsModel.getMsiLocationsByProductId(productId);
                }
            }
        });

        return $.mage.ProductValidate;
    };
});
