/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    /**
     * @param url
     * @param windowObj
     */
    window.socialCallback = function (url, windowObj) {
        customerData.invalidate(['customer']);
        customerData.reload(['customer'], true).done(function () {
            if (url !== '') {
                window.location.href = url;
            } else {
                window.location.reload();
            }
            windowObj.close();
        });
    };

    return function (config, element) {
        var model = {
            initialize: function () {
                var self = this;
                $(element).on('click', function (e) {
                    e.preventDefault();
                    self.openPopup();
                });
            },

            openPopup: function () {
                window.open(config.url, config.label, this.getPopupParams());
            },

            getPopupParams: function () {
                var w = 600,
                    h = 500;

                const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
                const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;

                const width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                const height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                const systemZoom = width / window.screen.availWidth;
                var left = (width - w) / 2 / systemZoom + dualScreenLeft / 2
                const top = (height - h) / 2 / systemZoom + dualScreenTop / 2

                if (dualScreenLeft < 0) {
                    left = -(-dualScreenLeft / 2 + w);
                }

                return (
                    'width=' + w +
                    ',height=' + h +
                    ',left=' + left +
                    ',top=' + top
                );
            }
        };
        model.initialize();

        return model;
    };
});
