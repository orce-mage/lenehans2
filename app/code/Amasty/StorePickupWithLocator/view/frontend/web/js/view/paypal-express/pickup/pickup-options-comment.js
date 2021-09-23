/**
 * Pickup Options Comment UI Element for Paypal express review page
 * Nested from main Pickup Options Comment UI Element
 */

define([
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-options-comment',
    'Amasty_StorePickupWithLocator/js/action/messages-resolver'
], function (
    PickupOptionsComment,
    messagesResolver
) {
    'use strict';

    return PickupOptionsComment.extend({
        onCommentChange: function () {
            this._super();

            if (!this.validate().valid) {
                return;
            }

            messagesResolver.clearMessages();
        }
    });
});
