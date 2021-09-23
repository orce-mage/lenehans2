define([
    'jquery',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, fullScreenLoader) {
    'use strict';

    return function (isStandart) {
        return {
            start: function () {
                if (isStandart) {
                    $('body').trigger('processStart');

                    return;
                }

                fullScreenLoader.startLoader();
            },

            stop: function () {
                if (isStandart) {
                    $('body').trigger('processStop');

                    return;
                }

                fullScreenLoader.stopLoader();
            }
        };
    };
});
