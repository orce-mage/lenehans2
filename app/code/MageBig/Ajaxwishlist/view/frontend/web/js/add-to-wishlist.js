define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.addToWishlist', widget, {
            /**
             * @param {HTMLElement} element
             * @return {Object}
             * @private
             */
            _getElementData: function (element) {
                var data, elementName, elementValue;

                element = $(element);
                data = {};
                elementName = element.data('selector') ? element.data('selector') : element.attr('name');
                elementValue = element.val();

                if (element.is('select[multiple]') && elementValue !== null) {
                    if (elementName.substr(elementName.length - 2) == '[]') { //eslint-disable-line eqeqeq
                        elementName = elementName.substring(0, elementName.length - 2);
                    }
                    $.each(elementValue, function (key, option) {
                        data[elementName + '[' + option + ']'] = option;
                    });
                } else {
                    if (elementValue) { //eslint-disable-line no-lonely-if
                        if (elementName.substr(elementName.length - 2) == '[]') { //eslint-disable-line eqeqeq, max-depth
                            elementName = elementName.substring(0, elementName.length - 2);

                            if (elementValue) { //eslint-disable-line max-depth
                                data[elementName + '[' + elementValue + ']'] = elementValue;
                            }
                        } else {
                            data[elementName] = elementValue;
                        }
                    }
                }

                return data;
            }
        });

        return $.mage.addToWishlist;
    }
});
