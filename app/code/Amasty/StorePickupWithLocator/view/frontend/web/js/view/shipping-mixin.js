define([
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
], function ($, registry, quote, customer) {
    'use strict';

    return function (Shipping) {
        return Shipping.extend({
            /**
             * Validate guest email
             */
            validateGuestEmail: function () {
                var loginFormSelector = 'form[data-role=email-with-possible-login]';

                $(loginFormSelector).validation();

                return $(loginFormSelector + ' input[type=email]').valid();
            },

            validateShippingInformation: function () {
                var result = this._super(),
                    shippingMethod = quote.shippingMethod();

                if (!customer.isLoggedIn() && !this.validateGuestEmail()) {
                    return result;
                }

                if (shippingMethod && shippingMethod['carrier_code'] === 'amstorepickup') {
                    this.source.set('params.invalid', false);
                    this.source.trigger('amStorepickup.data.validate');

                    return !this.source.get('params.invalid');
                }

                return result;
            }
        });
    };
});
