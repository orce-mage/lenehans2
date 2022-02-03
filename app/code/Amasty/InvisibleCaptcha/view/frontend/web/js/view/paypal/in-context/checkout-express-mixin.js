/**
 * Mixin paypal express checkout
 */
define([
    'jquery',
    'underscore',
    'Amasty_InvisibleCaptcha/js/model/am-recaptcha',
    'Magento_Checkout/js/model/payment/additional-validators'
], function ($, _, amReCaptchaModel, additionalValidators) {
    'use strict';

    var mixin = {
        defaults: {
            reCaptchaButton: false
        },
        isCaptchaValid: false,

        /** @inheritDoc */
        initListeners: function (context) {
            this._super(context);

            if (amReCaptchaModel.isEnabledOnPayments) {
                $.async('button[data-payment-name="' + this.index + '"]', function (element) {
                    this.reCaptchaButton = $(element);
                    this.validate();
                }.bind(this));

                amReCaptchaModel.isValidationPassed.subscribe(function (data) {
                    this.isCaptchaValid = data;
                }.bind(this));
            }

            return this;
        },

        /** @inheritDoc */
        validate: function (actions) {
            this.actions = actions || this.actions;

            if (this.actions) {
                if (amReCaptchaModel.isEnabledOnPayments && !this.isCaptchaValid) {
                    this.actions.disable();
                } else {
                    additionalValidators.validate(true)
                        ? this.actions.enable()
                        : this.actions.disable();

                }
            }
        },

        /**
         * Adding logic to be triggered onClick action for smart buttons component
         * @returns {void}
         */
        onClick: function () {
            var savedCallback = this._super;

            if (!this.isCaptchaValid && amReCaptchaModel.isEnabledOnPayments) {
                // eslint-disable-next-line one-var
                var subscribe = amReCaptchaModel.isValidationPassed.subscribe(function (data) {
                    if (this.actions) {
                        if (data) {
                            this.actions.enable();

                            savedCallback.apply(this);
                        } else {
                            this.actions.disable();
                        }
                    }

                    subscribe.dispose();
                }.bind(this));

                this.validateCaptcha();
            } else {
                savedCallback.apply(this);
            }
        },

        /**
         * Trigger captcha's invisible button
         * @returns {void}
         */
        validateCaptcha: function () {
            if (this.reCaptchaButton.length) {
                this.reCaptchaButton.trigger('click');
            }
        }
    };

    return function (ExpressCheckout) {
        return ExpressCheckout.extend(mixin);
    };
});
