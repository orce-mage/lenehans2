/**
 * reCaptcha model
 */

define([
    'ko',
    'underscore'
], function (ko, _) {
    'use strict';

    return {
        onLoadCallback: 'amInvisibleCaptchaOnloadCallback',
        isEnabledOnPayments: false,
        isScriptLoaded: false,
        tokenFields: [],
        url: 'https://www.google.com/recaptcha/api.js',
        isCaptchaAppended: false,
        checkoutRecaptchaValidateUrl: null,
        invisibleCaptchaCustomForm: false,
        isValidationPassed: ko.observable(false),
        recaptchaConfig: {
            lang: 'hl=en',
            size: 'invisible'
        },
        formToProtect: '',

        setConfig: function (config) {
            if (_.has(config, 'recaptchaConfig')) {
                this.setRecaptchaConfig(config.recaptchaConfig);
            }

            if (_.has(config, 'formsToProtect')) {
                this.setFormsList(config.formsToProtect);
            }

            this.checkoutRecaptchaValidateUrl = config.checkoutRecaptchaValidateUrl;
            this.invisibleCaptchaCustomForm = config.invisibleCaptchaCustomForm;
            this.isEnabledOnPayments = !!config.isEnabledOnPayments;
        },

        setRecaptchaConfig: function (config) {
            _.extend(this.recaptchaConfig, config);
        },

        getRecaptchaConfig: function () {
            return this.recaptchaConfig;
        },

        setFormsList: function (formsList) {
            this.formToProtect = formsList;
        },

        getFormsList: function () {
            return this.formToProtect;
        }
    };
});
