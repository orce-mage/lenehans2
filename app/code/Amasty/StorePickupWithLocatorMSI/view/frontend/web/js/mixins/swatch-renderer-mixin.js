/*
 * Swatch renderer mixin
 */

define([
    'jquery',
    'underscore',
    'Amasty_StorePickupWithLocatorMSI/js/action/product-config',
    'Amasty_StorePickupWithLocatorMSI/js/action/toggle-locations-block',
    'Amasty_StorePickupWithLocatorMSI/js/model/msi-locations',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config'
], function ($, _, productConfigActions, toggleLocationsBlockAction, msiLocationsModel, productConfig) {
    'use strict';

    return function (SwatchRenderer) {
        $.widget('mage.SwatchRenderer', SwatchRenderer, {
            options: {
                selectors: {
                    addToCartForm: 'form#product_addtocart_form',
                    swatchAttributes: '.swatch-attribute'

                },
                nodes: {
                    addToCartForm: null,
                    swatchAttributes: null
                }
            },

            _EventListener: function () {
                this._super();

                if (!productConfig.isConfigurable()) {
                    productConfigActions.setConfigurableState();
                    productConfigActions.setProductId(null);
                }

                productConfigActions.setMsiEnabledState();

                if (!productConfig.isMsiEnabled) {
                    return;
                }

                toggleLocationsBlockAction(msiLocationsModel.msiLocations());
                this.element.on(
                    'click',
                    '.' + this.options.classes.optionClass,
                    this._onOptionsChange.bind(this)
                );
            },

            _onOptionsChange: function () {
                productConfigActions.setProductId(this._getProductIdBySelectedAttributes());
                this._updateMsiLocations();
            },

            _updateMsiLocations: function () {
                var selectedProductId = productConfig.productId();

                if (!selectedProductId) {
                    msiLocationsModel.setMsiLocations([]);

                    return;
                }

                msiLocationsModel.getMsiLocationsByProductId(selectedProductId);
            },

            _getProductIdBySelectedAttributes: function () {
                var selectedAttributes = this._getSelectedOptions(),
                    productsIndicesMap = this.options.jsonConfig.index,
                    selectedProductId;

                $.each(productsIndicesMap, function (productId, attributes) {
                    if (_.isEqual(attributes, selectedAttributes)) {
                        selectedProductId = productId;
                    }
                });

                return selectedProductId;
            },

            _getSelectedOptions: function () {
                var selectedAttributes = {},
                    swatchAttributes = this.options.nodes.swatchAttributes;

                if (!swatchAttributes || !swatchAttributes.length) {
                    swatchAttributes = $(this.options.selectors.swatchAttributes);
                }

                swatchAttributes.each(function (index, attribute) {
                    var attributeId = attribute.getAttribute('attribute-id')
                            || attribute.dataset.attributeId,
                        optionSelected = attribute.getAttribute('option-selected')
                            || attribute.dataset.optionSelected;

                    if (!attributeId || !optionSelected) {
                        return;
                    }

                    selectedAttributes[attributeId] = optionSelected;
                });

                return selectedAttributes;
            }
        });

        return $.mage.SwatchRenderer;
    };
});
