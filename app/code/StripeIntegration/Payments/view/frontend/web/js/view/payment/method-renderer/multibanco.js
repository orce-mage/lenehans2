/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'StripeIntegration_Payments/js/view/payment/method-renderer/method'
    ],
    function (
        $,
        Component
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                self: this,
                template: 'StripeIntegration_Payments/payment/redirect_form',
                code: "multibanco"
            },
            redirectAfterPlaceOrder: false
        });
    }
);
