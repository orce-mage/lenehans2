define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    return function (config, element) {
        var elm = $(element);
        setTimeout(function () {
            elm.trigger('click');
        }, 1000);
    };
});
