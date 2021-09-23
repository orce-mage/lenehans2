define([
    'jquery',
    'Magento_Ui/js/form/element/ui-select',
    'underscore'
], function ($, Select, _) {
    'use strict';
    return Select.extend({
        /**
         * Gets initial value of element
         *
         * @returns {*} Elements' value.
         */

        getInitialValue: function () {
            var self = this,
                defaultValue = this.value(),
                optArr = [];

            if (defaultValue.length) {
                var selected = this.getSelected();

                _.each(selected, function (opt) {
                    optArr.push(opt.value);
                });

                _.each(defaultValue, function (v) {
                    if (-1 === optArr.indexOf(v)) {
                        self.updateValue(v, false);
                    }
                });
            }

            var values = [this.value(), this.default],
                value;

            values.some(function (v) {
                if (v !== null && v !== undefined) {
                    value = v;

                    return true;
                }

                return false;
            });

            return this.normalizeData(value);
        },

        getSelectedSort() {
            var input = $('#' + this.inputId),
                values = input.val().trim(),
                selected = this.getSelected(),
                newSelected = [],
                v = 0;

            if (values) values = values.split(',');
            else values = [];

            if (values.length) {
                for (var i = 0; i < values.length; i++) {
                    for (var j = 0; j < selected.length; j++) {
                        if (values[i] == selected[j].value) {
                            newSelected[v] = {
                                'value': selected[j].value,
                                'label': selected[j].label
                            };
                            v += 1;
                        }
                    }
                }
            }

            return newSelected;
        },

        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function (data) {
            var isSelected = this.isSelected(data.value);

            if (this.lastSelectable && data.hasOwnProperty(this.separator)) {
                return this;
            }

            if (!this.multiple) {
                if (!isSelected) {
                    this.value(data.value);
                }
                this.listVisible(false);
            } else {
                if (!isSelected) { /*eslint no-lonely-if: 0*/
                    this.updateValue(data.value, true);
                    this.value.push(data.value);
                } else {
                    this.updateValue(data.value, false);
                    this.value(_.without(this.value(), data.value));
                }
            }

            return this;
        },

        /**
         * Remove element from selected array
         */
        removeSelected: function (value, data, event) {
            event ? event.stopPropagation() : false;
            this.updateValue(value, false);
            this.value.remove(value);
        },

        updateValue: function (value, isAdd) {
            var input = $('#' + this.inputId),
                values = input.val().trim();
            if (values) values = values.split(',');
            else values = [];
            if (isAdd) {
                if (-1 === values.indexOf(value)) {
                    values.push(value);
                    input.val(values.join(','));
                }
            } else {
                if (-1 !== values.indexOf(value)) {
                    values.splice(values.indexOf(value), 1);
                    input.val(values.join(','));
                }
            }
        },

        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function (data) {
            var option = this.parseData(data);
            if (data.error) {
                return this;
            }
            this.options([]);
            this.setOption(option);
            this.set('newOption', option);
        },

        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function (data) {
            return {
                value: data.category.entity_id,
                label: data.category.name
            };
        }
    });
});
