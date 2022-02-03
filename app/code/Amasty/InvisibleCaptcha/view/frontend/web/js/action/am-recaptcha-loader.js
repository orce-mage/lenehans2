/**
 * reCaptcha API loader
 */

define([
    'Amasty_InvisibleCaptcha/js/model/am-recaptcha'
], function (amReCaptchaModel) {
    'use strict';

    return {
        /**
         *  Add script tag.
         * @returns {void}
         */
        addReCaptchaScript: function () {
            var element,
                scriptTag;

            if (amReCaptchaModel.isScriptLoaded) {
                return;
            }

            scriptTag = document.getElementsByTagName('body')[0];
            element = document.createElement('script');
            element.async = true;
            element.src = this.getUrl();
            scriptTag.append(element);
            amReCaptchaModel.isScriptLoaded = true;
        },

        /**
         * Build url for captcha loading
         * @returns {string}
         */
        getUrl: function () {
            return amReCaptchaModel.url + '?onload=' + amReCaptchaModel.onLoadCallback
                + '&render=explicit' + this.getLang();
        },

        getLang: function () {
            return amReCaptchaModel.recaptchaConfig.lang ? '&' + amReCaptchaModel.recaptchaConfig.lang : '';
        }
    };
});
