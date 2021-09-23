define([
    "Magento_Ui/js/form/element/select",
    "mageUtils"
], function (Select, utils) {

    return Select.extend({
        defaults: {
            imports: {
                'fieldValue' : '${ $.parentName}.field:value'
            },
            listens: {
                fieldValue: 'updateConditions'
            },
            modules: {
                recordComponent: '${ $.parentName }'
            }
        },
        initObservable: function () {
            this._super().observe(['fieldValue']);

            return this;
        },
        getInitialValue: function () {
            var values = [this.value(), this.default],
                value;

            values.some(function (v) {
                if (v !== null && v !== undefined) {
                    value = v;

                    return true;
                }

                return false;
            });

            return utils.isEmpty(value) ? '' : value;
        },
        updateConditions: function () {
            var fieldData = this.recordComponent().parentComponent().filterConfig[this.fieldValue()] || {};
            if (!_.isUndefined(fieldData.conditions)) {
                this.options(fieldData.conditions);
            } else {
                this.options([]);
            }
        }
    });
})
