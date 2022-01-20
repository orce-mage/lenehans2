define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils',
    'StripeIntegration_Payments/js/view/checkout/trialing_subscriptions'
], function (
    quote,
    totals,
    priceUtils,
    trialingSubscriptions
) {
    'use strict';

    return function (grandTotal)
    {
        return grandTotal.extend(
        {
            totals: quote.getTotals(),

            getValue: function()
            {
                var price = 0;

                if (this.totals())
                    price = totals.getSegment('grand_total').value + trialingSubscriptions().getPureValue();

                return grandTotal().getFormattedPrice(price);
            },

            getBaseValue: function () {
                var price = 0;

                if (this.totals())
                    price = this.totals()['base_grand_total'] + trialingSubscriptions().getPureBaseValue();

                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }

        });
    }
});
