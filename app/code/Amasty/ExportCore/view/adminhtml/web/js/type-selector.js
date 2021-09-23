define([
    'Magento_Ui/js/form/element/select',
    'underscore'
], function (Select, _) {
    'use strict';

    return Select.extend({
        defaults: {
            prefix: null,
            listens: {
                value: 'checkElements',
                '${ $.parentName }:elems': 'checkElements'
            },
            modules: {
                fieldsetsContainer: '${ $.parentName }'
            }
        },
        checkElements: function () {
            if (!_.isUndefined(this.containers[0])) {
                _.each(this.containers[0].elems(), function (elem) {
                    if (elem.componentType === 'fieldset') {
                        var elementIsVisible = (this.prefix + this.value()) === elem.index;

                        elem.visible(elementIsVisible);
                        this.disableElements(elem, !elementIsVisible)
                    }
                }.bind(this));
            }
        },
        initContainer: function (parent) {
            this._super();
            this.checkElements();

            return this;
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
