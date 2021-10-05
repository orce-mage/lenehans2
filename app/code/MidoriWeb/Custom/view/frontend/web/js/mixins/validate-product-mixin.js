/*
 * Product validation mixin
 */

define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/product/view/product-ids-resolver',
    'MidoriWeb_Custom/js/model/delivery-message',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config'
], function (
    $,
    _,
    productIdsResolver,
    deliveryMessageModel,
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

                if (!productConfig.isConfigurable()) {
                    productId = productIdsResolver($(this.options.selectors.addToCartForm))[0];
                    deliveryMessageModel.getDeliveryMessageByProductId(productId);
                }
            }
        });

        return $.mage.ProductValidate;
    };
});
