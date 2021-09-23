define([
    'Magento_Ui/js/form/element/ui-select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            switchEntityUrl: '',
            entityUrl: '',
            indexUrl: ''
        },
        onUpdate: function () {
            this._super();

            if (this.value() !== '' && this.value() !== undefined) {
                location.href = this.entityUrl.replace('__entity_code__', this.value());
            } else {
                location.href = this.indexUrl;
            }
        },
        toggleOptionSelected: function (data) {
            if (data.hasOwnProperty(this.separator)) {
                this.openChildLevel(data);
                return;
            }

            return this._super();
        }
    });
});
