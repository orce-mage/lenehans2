define([
    'jquery',
    'mage/backend/notification'
], function ($, notification) {
    'use strict';

    return {
        pageSelector: '.page-main-actions',
        containerSelector: '[data-role="messages"]',
        css: {
            success: 'message-success success',
            container: 'messages'
        },

        add: function (text, isError, selector) {
            var wrapper;

            notification().clear();
            notification().add({
                error: isError,
                message: text,
                insertMethod: function (message) {
                    wrapper = $(message);

                    if (!isError) {
                        wrapper.addClass(this.css.container).children().addClass(this.css.success);
                    }

                    $(selector || this.pageSelector).after(wrapper);
                }.bind(this)
            });
        },

        clear: function () {
            notification().clear();
        }
    };
});
