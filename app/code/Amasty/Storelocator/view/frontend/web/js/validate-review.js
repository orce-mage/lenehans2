define([
    'jquery',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';

    $.validator.addMethod(
        'amasty-rating-required', function (value) {
            return value !== undefined;
        }, $.mage.__('Please select one of each of the ratings above.'));
});
