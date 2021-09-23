define([
    'jquery'
], function ($) {
    'use strict';

    return {
        remove: function (url) {
            return $.ajax({
                showLoader: true,
                url: url,
                type: 'POST'
            });
        },

        add: function (url, code) {
            return $.ajax({
                showLoader: true,
                url: url,
                data: { am_giftcard_code: code },
                type: 'POST'
            });
        }
    };
});
