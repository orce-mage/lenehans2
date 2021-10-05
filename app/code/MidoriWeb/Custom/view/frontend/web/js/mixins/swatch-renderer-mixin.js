/*
 * Swatch renderer mixin
 */

define([
    'jquery',
    'underscore',
    'MidoriWeb_Custom/js/model/delivery-message',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config'
], function ($, _, deliveryMessageModel, productConfig) {
    'use strict';

    return function (SwatchRenderer) {
        $.widget('mage.SwatchRenderer', SwatchRenderer, {
            options: {
                selectors: {
                    addToCartForm: 'form#product_addtocart_form',
                    swatchAttributes: '.swatch-attribute'
                }
            },

            _EventListener: function () {
                this._super();

                this.element.on(
                    'click',
                    '.' + this.options.classes.optionClass,
                    this._onOptionsChangeDeliveryMessage.bind(this)
                );
            },

            _onOptionsChangeDeliveryMessage: function () {
                var selectedProductId = productConfig.productId();

                if (!selectedProductId) {
                    deliveryMessageModel.setDeliveryMessage('');

                    return;
                }

                deliveryMessageModel.getDeliveryMessageByProductId(selectedProductId);
            },

        });

        return $.mage.SwatchRenderer;
    };
});
