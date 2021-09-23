/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/action/login',
    'Magento_Customer/js/model/customer',
    'mage/validation',
    'Magento_Checkout/js/model/authentication-messages',
    'Magento_Checkout/js/model/full-screen-loader',
    'magnificpopup'
], function ($, Component, loginAction, customer, validation, messageContainer, fullScreenLoader) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return Component.extend({
        isGuestCheckoutAllowed: checkoutConfig.isGuestCheckoutAllowed,
        isCustomerLoginRequired: checkoutConfig.isCustomerLoginRequired,
        registerUrl: checkoutConfig.registerUrl,
        forgotPasswordUrl: checkoutConfig.forgotPasswordUrl,
        autocomplete: checkoutConfig.autocomplete,
        defaults: {
            template: 'MageBig_SocialLogin/authentication'
        },

        /**
         * Returns path to the template
         * defined for a current display mode.
         *
         * @returns {String} Path to the template.
         */
        getTemplate: function () {
            if ($('#social-login-popup').length) {
                return this.template;
            }

            return 'Magento_Checkout/authentication';
        },

        /**
         * Init Social Login popup
         */
        initLoginPopup: function () {
            $('.action-auth-toggle').magnificPopup({
                type: 'inline',
                removalDelay: 300,
                mainClass: 'mfp-move-from-top',
                closeOnBgClick: false,
                callbacks: {
                    beforeOpen: function () {
                        $('#social-login-popup .social-login').hide();
                        $('#social-login-popup .social-login.authentication').show();

                    }
                },
                midClick: true
            });
        },

        /**
         * Is login form enabled for current customer.
         *
         * @return {Boolean}
         */
        isActive: function () {
            return !customer.isLoggedIn();
        },

        /**
         * Provide login action.
         *
         * @param {HTMLElement} loginForm
         */
        login: function (loginForm) {
            var loginData = {},
                formDataArray = $(loginForm).serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            if ($(loginForm).validation() &&
                $(loginForm).validation('isValid')
            ) {
                fullScreenLoader.startLoader();
                loginAction(loginData, checkoutConfig.checkoutUrl, undefined, messageContainer).always(function () {
                    fullScreenLoader.stopLoader();
                });
            }
        }
    });
});
