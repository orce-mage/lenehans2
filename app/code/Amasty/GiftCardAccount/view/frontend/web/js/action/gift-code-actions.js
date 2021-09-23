define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Amasty_GiftCardAccount/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Amasty_GiftCardAccount/js/model/payment/gift-card-messages',
    'mage/storage'
], function ($, quote, urlManager, errorProcessor, messageContainer, storage) {
    'use strict';

    return {
        remove: function (giftCode) {
            var quoteId = quote.getQuoteId(),
                url = urlManager.getGiftCodeUrl(giftCode, quoteId);

            messageContainer.clear();

            return storage.delete(url, false);
        },

        check: function (giftCode) {
            var url = urlManager.getCheckGiftCodeUrl();

            return $.ajax({
                url: url,
                data: { amgiftcard: giftCode },
                type: 'post'
            });
        },

        set: function (giftCode) {
            var quoteId = quote.getQuoteId(),
                url = urlManager.getGiftCodeUrl(giftCode, quoteId);

            messageContainer.clear();

            return storage.put(url, {}, false);
        }
    };
});
