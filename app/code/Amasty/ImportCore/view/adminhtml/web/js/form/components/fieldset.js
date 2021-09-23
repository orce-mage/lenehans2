define([
    'Magento_Ui/js/form/components/fieldset'
], function (FieldSet) {
    return FieldSet.extend({
        validate: function () {
            return {
                valid: true
            };
        }
    });
});
