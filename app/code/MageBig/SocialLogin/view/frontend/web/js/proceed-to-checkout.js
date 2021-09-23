/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    return function (config, element) {
        $(element).click(function (event) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer');

            event.preventDefault();

            if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                if ($('.authorization-link a').length) {
                    $('.authorization-link a')[0].click();
                }

                return false;
            }
            $(element).attr('disabled', true);
            location.href = config.checkoutUrl;
        });

    };
});
