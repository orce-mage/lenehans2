define([
    'Magento_Ui/js/form/components/button'
], function (Button) {
    return Button.extend({
        validate: function () {
            return {
                valid: true
            };
        },
        _setButtonClasses: function () {
            this._super();
            this.buttonClasses['action-basic'] = false;
        }
    });
});
