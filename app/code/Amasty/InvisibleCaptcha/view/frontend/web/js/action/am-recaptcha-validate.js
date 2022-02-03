/**
 * Ajax actions
 * @api
 */

define([
    'jquery',
    'underscore',
    'Amasty_InvisibleCaptcha/js/model/am-recaptcha'
], function ($, _, amReCaptchaModel) {
    'use strict';

    return {
        /**
         * Ajax call
         * @param {Element} tokenField
         * @param {String} token
         * @returns {Deferred}
         */
        validateCaptcha: function (tokenField, token) {
            return $.ajax({
                url: amReCaptchaModel.checkoutRecaptchaValidateUrl,
                data: {
                    'g-recaptcha-response': token
                },
                type: 'POST'
            });
        }
    };
});
