define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _RenderControls: function () {
                this._super();
                if (typeof this.options.jsonConfig.original_products != 'undefined') {
                    this._CrossOutofstockOptions();
                }
            },

            _Rebuild: function () {
                this._super();
                if (typeof this.options.jsonConfig.original_products != 'undefined') {
                    this._CrossOutofstockOptions();
                }
            },

            _Rewind: function (controls) {
                this._super(controls);
                controls.find('div[option-id], option[option-id], div[data-option-id], option[data-option-id]')
                    .removeClass('am-stockstatus-disabled');
            },

            _CrossOutofstockOptions: function () {
                var $widget = this,
                    controls = $widget.element.find(
                        '.' + $widget.options.classes.attributeClass + '[attribute-id], .'
                        + $widget.options.classes.attributeClass + '[data-attribute-id]'
                    ),
                    selected = controls.filter('[option-selected], [data-option-selected]'),
                    isOnlyOneOption = controls.length === 1;

                // done if nothing selected and more than one options
                if (selected.length <= 0 && !isOnlyOneOption) {
                    return;
                }

                // Crossed-out out of stock options
                controls.each(function () {
                    var $this = $(this),
                        id = $this.attr('attribute-id') || $this.data('attribute-id'),
                        products = $widget._CalcProducts(id),
                        selectedId = selected.first().attr('attribute-id') || selected.first().data('attribute-id');

                    if (selected.length === 1 && selectedId === id && !isOnlyOneOption) {
                        return;
                    }

                    $this.find('[option-id], [data-option-id]').each(function () {
                        var $element = $(this),
                            option = $element.attr('option-id') || $element.data('option-id');

                        if (!$widget.optionsMap.hasOwnProperty(id) ||
                            !$widget.optionsMap[id].hasOwnProperty(option)
                        ) {
                            return true;
                        }

                        var isProductsNotAvailableAfterSelection = !isOnlyOneOption
                                && _.intersection(products, $widget.options.jsonConfig.original_products[id][option]).length <= 0,
                            isProductsNotAvailableBeforeSelection = isOnlyOneOption
                                && (typeof $widget.options.jsonConfig.original_products[id][option] === 'undefined'
                                        || $widget.options.jsonConfig.original_products[id][option].length === 0);
                        if (isProductsNotAvailableBeforeSelection || isProductsNotAvailableAfterSelection) {
                            $element.addClass('am-stockstatus-disabled');
                        }
                    });
                });
            },

            /**
             * disable Amasty_Conf cross options
             * @private
             */
            _addOutOfStockLabels: function () {
            }
        });

        return $.mage.SwatchRenderer;
    }
});
