define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'stripe_payments',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/stripe_payments'
            },
            {
                type: 'stripe_payments_bancontact',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/bancontact'
            },
            {
                type: 'stripe_payments_giropay',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/giropay'
            },
            {
                type: 'stripe_payments_ideal',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/ideal'
            },
            {
                type: 'stripe_payments_sepa',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/sepa'
            },
            {
                type: 'stripe_payments_sepa_credit',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/sepa_credit'
            },
            {
                type: 'stripe_payments_sofort',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/sofort'
            },
            {
                type: 'stripe_payments_multibanco',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/multibanco'
            },
            {
                type: 'stripe_payments_eps',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/eps'
            },
            {
                type: 'stripe_payments_p24',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/p24'
            },
            {
                type: 'stripe_payments_alipay',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/alipay'
            },
            {
                type: 'stripe_payments_wechat',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/wechat'
            },
            {
                type: 'stripe_payments_fpx',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/fpx'
            },
            {
                type: 'stripe_payments_klarna',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/klarna'
            },
            {
                type: 'stripe_payments_paypal',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/paypal'
            },
            {
                type: 'stripe_payments_ach',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/ach'
            },
            {
                type: 'stripe_payments_oxxo',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/oxxo'
            },
            {
                type: 'stripe_payments_checkout',
                component: 'StripeIntegration_Payments/js/view/payment/method-renderer/checkout'
            }
        );
        // Add view logic here if needed
        return Component.extend({});
    }
);
