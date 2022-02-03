define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'Amasty_InvisibleCaptcha/js/model/am-recaptcha',
    'Amasty_InvisibleCaptcha/js/action/am-recaptcha-loader',
    'mage/loader',
    'domReady!'
], function (
    Component,
    $,
    ko,
    _,
    utils,
    amReCaptchaModel,
    amReCaptchaLoader
) {
    'use strict';

    return Component.extend({
        defaults: {
            formsToProtect: '',
            showLoaderOnCaptchaLoading: false,
            reCaptchaId: 'am-recaptcha-place-order'
        },

        /**
         * Loads reCaptcha API
         * @returns {void}
         */
        loadApi: function () {
            if (!amReCaptchaModel.isScriptLoaded) {
                window[amReCaptchaModel.onLoadCallback] = function () {
                    $(window).trigger('amcaptchaReady');
                };

                amReCaptchaLoader.addReCaptchaScript();
            }
        },

        /**
         * Add captcha
         * @returns {void}
         */
        appendCaptcha: function () {
            if (!amReCaptchaModel.isCaptchaAppended) {
                this.add();

                window.grecaptcha.render(this.reCaptchaId, this.getParameters());

                amReCaptchaModel.isCaptchaAppended = true;
            }
        },

        /**
         * Initialize additional reCaptcha instance.
         * @returns {void}
         */
        add: function () {
            var rendererReCaptcha = $('<div/>', {
                'id': this.reCaptchaId
            });

            $('body').append(rendererReCaptcha);
        },

        setIsCaptchaValidationPassed: function (flag) {
            amReCaptchaModel.isValidationPassed(flag);
            amReCaptchaModel.isValidationPassed.valueHasMutated();
        },

        /**
         * Reset captcha
         * @returns {void}
         */
        resetCaptcha: function () {
            _.each(amReCaptchaModel.tokenFields, function (tokenBlock) {
                grecaptcha.reset(tokenBlock.data('id'));
            });

           this.setIsCaptchaValidationPassed(false);
        }
    });
});
