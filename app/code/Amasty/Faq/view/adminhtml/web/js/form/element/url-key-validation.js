define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function(Abstract, $, validator){
    'use strict';

    validator.addRule(
        'url-key-validation',
        function (value) {
            return /^[a-z0-9_-]+(\.[a-z0-9_-]+)?$/.test(value);
        },
        $.mage.__('Invalid url key. Only a-z, 0-9, ., -, _ expected.')
    );

    return Abstract.extend({
        'url-key-validation': function () {
        return validator('url-key-validation', this.value()).passed;
        }
    });
});