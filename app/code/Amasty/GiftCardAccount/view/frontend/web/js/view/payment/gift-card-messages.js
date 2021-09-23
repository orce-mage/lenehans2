define([
    'Magento_Ui/js/view/messages',
    'Amasty_GiftCardAccount/js/model/payment/gift-card-messages'
], function (Component, messageContainer) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function (config) {
            return this._super(config, messageContainer);
        }
    });
});
