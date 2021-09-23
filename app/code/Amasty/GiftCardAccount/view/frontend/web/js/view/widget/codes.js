/**
 * Account gift codes
 */
define([
    'Amasty_GiftCardAccount/js/view/account/codes'
], function (CodesElement) {
    'use strict';

    return CodesElement.extend({
        defaults: {
            links: {
                cards: '${ "amcard-giftcards-checker" }:cards',
                isVisibleMessage: '${ "amcard-giftcards-checker" }:isVisibleMessage',
                errorMessage: '${ "amcard-giftcards-checker" }:errorMessage'
            }
        }
    });
});
