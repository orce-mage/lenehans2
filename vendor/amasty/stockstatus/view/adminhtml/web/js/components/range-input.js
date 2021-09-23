define([
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'Amasty_Stockstatus/js/action/validate-ranges'
], function (_, abstract, validateRanges) {
    return abstract.extend({
        defaults: {
            rangesComponentName: ''
        },

        validateAllRanges: function () {
            validateRanges(this.rangesComponentName);
        }
    });
});
