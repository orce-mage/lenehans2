define([
    'Magento_Ui/js/form/element/select',
    '../../storage/typical-fields'
], function (Select, typicalFields) {
    'use strict';

    return Select.extend({
        defaults: {
            entityCode: '',
            fieldsUrl: '',
            autofill: '',
            fieldsProvider: '',
            listens: {
                autofill: 'checkAutofill'
            },
            modules: {
                dataProvider: '${ $.provider }',
                fields: '${ $.fieldsProvider }'
            }
        },

        initObservable: function () {
            this._super().observe(['autofill']);

            return this;
        },

        checkAutofill: function (value) {
            if (value) {
                this.updateTypicalFields();
            }
        },

        updateTypicalFields: function () {
            var behaviorCode = this.value(),
                params = {},
                options = {};

            if (behaviorCode !== undefined && this.autofill()) {
                params.entity_code = this.entityCode;
                params.behavior_code = behaviorCode;
                options.dataProvider = this.dataProvider();
                options.fieldsUrl = this.fieldsUrl;
                options.dataScope = this.fields().dataScope;

                typicalFields.update(params, options);
            }
        },

        onUpdate: function () {
            this._super();

            if (this.value()) {
                this.updateTypicalFields();
            }
        }
    });
});
