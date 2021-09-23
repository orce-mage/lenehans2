define([
    'underscore',
    'Magento_Ui/js/form/element/select',
    'Amasty_Stockstatus/js/action/validate-ranges'
], function (_, select, validateRanges) {
    return select.extend({
        defaults: {
            rangesComponentName: ''
        },

        validateAllRanges: function () {
            validateRanges(this.rangesComponentName);
        }
    });
});
