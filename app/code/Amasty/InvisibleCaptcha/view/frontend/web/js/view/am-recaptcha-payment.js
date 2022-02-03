define([
    'jquery',
    'underscore',
    'mageUtils',
    'Amasty_InvisibleCaptcha/js/view/am-recaptcha-abstract',
    'Amasty_InvisibleCaptcha/js/model/am-recaptcha',
    'Amasty_InvisibleCaptcha/js/action/am-recaptcha-validate',
    'Magento_Ui/js/model/messageList'
], function (
    $,
    _,
    utils,
    Component,
    amReCaptchaModel,
    recaptchaValidate,
    messageList
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_InvisibleCaptcha/payment-recaptcha-container',
            isEnabledOnPayments: amReCaptchaModel.isEnabledOnPayments
        },

        renderReCaptcha: function (element) {
            if (window.grecaptcha && window.grecaptcha.render) {
                this.appendCaptcha();
                this.initCaptcha(element);
            } else {
                $(window).on('amcaptchaReady', function () {
                    this.appendCaptcha();
                    this.initCaptcha(element);
                }.bind(this));

                this.loadApi();
            }
        },

        getPaymentName: function (element) {
            var $element = $(element),
                // eslint-disable-next-line max-len
                paymentMethodField = $element.closest('.payment-method').find('.payment-method-title > input[type="radio"]');

            return paymentMethodField.length ? paymentMethodField.val() : '';
        },

        /**
         * Init captcha on each payment method
         * @param {Element} element
         * @return {void}
         */
        initCaptcha: function (element) {
            var $element = $(element),
                widgetId,
                listeners,
                id = utils.uniqueid(),
                $button = $element.closest('.payment-method-content').find('button[type="submit"]'),
                messagesContainer = $element.closest('.am-recaptcha-container').find('.messages-container'),
                paymentName = this.getPaymentName(element);

            $(messagesContainer).attr('id', 'message-' + id);
            $element.attr('id', id);

            if (!$button.length) {
                $button = $('<button type="button" class="hidden" data-payment-name="' + paymentName + '"></button>');

                $button.insertAfter($element);
            }

            widgetId = window.grecaptcha.render($button[0], this.getParameters($element, $button));

            $button.click(function (event) {
                if (!$element.val()) {
                    event.preventDefault(event);
                    event.stopImmediatePropagation();

                    window.grecaptcha.execute(widgetId);
                } else {
                    this.setIsCaptchaValidationPassed(true);
                }
            }.bind(this));

            listeners = $._data($button[0], 'events').click;
            listeners.unshift(listeners.pop());

            amReCaptchaModel.tokenFields.push($element);
        },

        /**
         * Get captcha parameters
         * @param {Element} tokenField
         * @param {Element} element
         * @return {Object}
         */
        getParameters: function (tokenField, element) {
            return _.extend(amReCaptchaModel.getRecaptchaConfig(), {
                'callback': function (token) {
                    recaptchaValidate.validateCaptcha(tokenField, token)
                        .done(function (response) {
                            var $element = $(element);

                            if (_.has(response, 'error') && response.error) {
                                this.resetCaptcha();
                                this.setIsCaptchaValidationPassed(false);
                                this.handleTokenError($(tokenField).attr('id'), response.message);

                                messageList.addErrorMessage({message: response.message});
                            } else {
                                this.setIsCaptchaValidationPassed(true);

                                $(tokenField).val(token);

                                if (!$(element).hasClass('hidden')) {
                                    $element.trigger('click');
                                }
                            }
                        }.bind(this));
                }.bind(this),
                'expired-callback': this.resetCaptcha
            });
        },

        handleTokenError: function (tokenFieldId, message) {
            var container = $('#message-' + tokenFieldId),
                messageBlock = container.find('.message');

            messageBlock.html(message);
            container.show(0).delay(5000).hide('fast', function () {
                messageBlock.html('');
            });
        }
    });
});
