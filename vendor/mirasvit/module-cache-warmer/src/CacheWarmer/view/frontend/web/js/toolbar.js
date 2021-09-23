define([
    'jquery',
    'underscore',
    'uiComponent'
], function ($, _, Component) {
    'use strict';
    return Component.extend({
        defaults: {
            cookieName:    '',
            cookValue:     '',
            toolbarUrl:    '',
            pageId:        '',
            pageType:      '',
            warmRules:     '',
            defaultStatus: '',
        },

        initialize: function () {
            this._super();

            var originCookieValue = $.cookie(this.cookieName);
            $.cookie(this.cookieName, window.performance.timing.fetchStart);
            if (this.cookieValue === null && originCookieValue === null) {
                var isHit = this.defaultStatus;
            } else {
                var isHit = this.cookieValue != originCookieValue ? 1 : 0;
            }

            var nonCacheableBlocks = $('.mst-cache-warmer__ncb').data('ncb');

            $.ajax(this.toolbarUrl, {
                method:  'get',
                data:    {
                    uri:                window.location.href,
                    isHit:              isHit,
                    pageId:             this.pageId,
                    pageType:           this.pageType,
                    warmRules:          this.warmRules,
                    userAgent:          window.navigator.userAgent,
                    nonCacheableBlocks: nonCacheableBlocks
                },
                success: function (response) {
                    $('body').append(response.html);
                }
            });
        }
    })
});
