/**
 * Adding info messages to to global message list
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    return {
        /**
         * Add message to global message list and scroll page to it
         *
         * @param {Object} message
         */
        addMessage: function (message) {
            var messagesObservable = customerData.get('messages'),
                messages = messagesObservable();

            // Override full array to avoid several identical messages
            messages.messages = [message];
            messagesObservable(messages);

            // Scroll page to the message block
            $([document.documentElement, document.body]).animate({
                scrollTop: $('.page.messages').offset().top
            }, 500);
        },

        /**
         * Clear global message list
         */
        clearMessages: function () {
            var messagesObservable = customerData.get('messages'),
                messages = messagesObservable();


            messages.messages = [];
            messagesObservable(messages);
        }
    }
});
