define([
    'Magento_Ui/js/form/components/button'
], function (button) {
    return button.extend({
        hide: function () {
            this.visible(false);
        },

        show: function () {
            this.visible(true);
        }
    });
});
