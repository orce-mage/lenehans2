define([
    'jquery'
], function ($) {
    "use strict";

    return function () {
        $.validator.addMethod(
            'validate-multiline-email',
            function (value) {
                return value.split(/\n/g).every(function (email) {
                    return $.validator.methods['validate-email'](email.trim());
                });
            },
            $.mage.__('Please enter a valid email address (Ex: johndoe@domain.com).')
        );
    }
});