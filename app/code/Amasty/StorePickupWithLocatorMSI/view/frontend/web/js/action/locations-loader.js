/**
 * Locations loader actions
 */

define([
    'jquery',
    'loader'
], function ($) {
    'use strict';

    var selectors = {
        locationsContainer: '[data-ampickupmsi-js="locations-container"]'
    };

    return {
        show: function () {
            $(selectors.locationsContainer).loader('show');
        },

        hide: function () {
            $(selectors.locationsContainer).loader('hide');
        }
    };
});
