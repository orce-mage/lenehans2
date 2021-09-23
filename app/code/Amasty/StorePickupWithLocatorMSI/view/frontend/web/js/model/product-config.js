/**
 * Product config model
 */

define([
    'ko'
], function (ko) {
    'use strict';

    return {
        isConfigurable: ko.observable(false),
        productId: ko.observable(''),
        isMsiEnabled: false
    };
});
