define([
    'Magento_Ui/js/form/element/single-checkbox',
], function (Checkbox) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            listens: {
                value: 'checkElements'
            },
            modules: {
                metaFieldset: '${ $.parentName }.${ $.fileDestinationFieldset }'
            }
        },
        setInitialValue: function () {
            this._super();

            this.checkElements();

            return this;
        },
        checkElements: function () {
            if (this.metaFieldset()) {
                var elementIsVisible = this.value() === this.valueMap.true;

                this.metaFieldset().visible(elementIsVisible);
                this.disableElements(this.metaFieldset(), !elementIsVisible);
            }
        },
        disableElements: function (element, disable) {
            if (_.isFunction(element.disabled)) {
                element.disabled(disable);
            } else {
                element.disabled = disable;
            }
            if (!_.isUndefined(element.elems) && _.isFunction(element.elems)) {
                _.each(element.elems(), function (elem) {
                    this.disableElements(elem, disable);
                }.bind(this));
            }
        }
    });
});
