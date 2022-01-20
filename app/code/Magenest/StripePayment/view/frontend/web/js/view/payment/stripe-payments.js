/**
 * Copyright © 2018 Magenest. All rights reserved.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'https://js.stripe.com/v2/',
        'https://js.stripe.com/v3/'
    ],
    function (
        $,
        Component,
        rendererList
    ) {
        'use strict';

        var api = window.checkoutConfig.payment.magenest_stripe.api;
        var stripeMethod;
        var paymentIntents = {
            type: 'magenest_stripe_paymentintents',
            component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-paymentintents'
        };
        if(api === "v2") {
            stripeMethod = {
                type: 'magenest_stripe',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-method'
            }
        }
        if(api === "direct") {
            stripeMethod = {
                type: 'magenest_stripe',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-direct'
            }
        }
        if(api === "v3") {
            stripeMethod = {
                type: 'magenest_stripe',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-element'
            };
        }

        var methods = [
            stripeMethod,
            {
                type: 'magenest_stripe_iframe',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-iframe'
            },
            {
                type: 'magenest_stripe_applepay',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payment-applepay'
            },
            {
                type: 'magenest_stripe_giropay',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-giropay'
            },
            {
                type: 'magenest_stripe_alipay',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-alipay'
            },
            {
                type: 'magenest_stripe_sofort',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-sofort'
            },
            {
                type: 'magenest_stripe_ideal',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-ideal'
            },
            {
                type: 'magenest_stripe_bancontact',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-bancontact'
            },
            {
                type: 'magenest_stripe_p24',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-przelewy'
            },
            {
                type: 'magenest_stripe_eps',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-eps'
            },
            {
                type: 'magenest_stripe_multibanco',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-multibanco'
            },
            {
                type: 'magenest_stripe_wechatpay',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-wechatpay'
            },
            {
                type: 'magenest_stripe_sepa',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-sepa'
            },
            {
                type: 'magenest_stripe_checkout',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-checkout'
            },
            {
                type: 'magenest_stripe_intents',
                component: 'Magenest_StripePayment/js/view/payment/method-renderer/stripe-payments-intents'
            },
        ];

        if(paymentIntents){
            methods.push(paymentIntents)
        }

        $.each(methods, function (k, method) {
            rendererList.push(method);
        });
        /** Add view logic here if needed */
        return Component.extend({});
    }
);