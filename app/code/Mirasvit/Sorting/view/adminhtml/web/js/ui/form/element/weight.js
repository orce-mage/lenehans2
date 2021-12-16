define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function ($, _, Text) {
    return Text.extend({
        defaults: {
            elementTmpl: 'Mirasvit_Sorting/ui/form/element/weight',

            valueUpdate: 'keyup',

            listens: {
                value: 'onUpdateValue'
            }
        },

        initialize: function () {
            this._super();

            _.bindAll(this, 'onUpdateValue');
        },

        onUpdateValue: function () {
            var val = this.value();

            if (val !== '-' && val !== '') {
                if (isNaN(parseInt(val))) {
                    this.value(0);
                } else if (val !== parseInt(val)) {
                    this.value(parseInt(val));
                }
            }

            if (val > 100) {
                this.value(100);
            } else if (val < -100) {
                this.value(-100);
            }
        }

    });
});
