/**
 * URL builder model
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return {
        method: 'rest',
        storeCode: 'default',
        version: 'V1',
        serviceUrl: ':method/:storeCode/:version',

        /**
         * @param {String} url
         * @param {Object} params
         * @return {*}
         */
        createUrl: function (url, params) {
            var completeUrl = this.serviceUrl + url;

            return this.bindParams(completeUrl, params);
        },

        /**
         * @param {String} url
         * @param {Object} params
         * @return {*}
         */
        bindParams: function (url, params) {
            var urlParts;

            params.method = this.method;
            params.storeCode = this.storeCode;
            params.version = this.version;

            urlParts = url.split('/');
            urlParts = urlParts.filter(Boolean);

            $.each(urlParts, function (key, part) {
                var urlPart = part.replace(':', '');

                if (!_.isUndefined(params[urlPart])) {
                    urlParts[key] = params[urlPart];
                }
            });

            return urlParts.join('/');
        }
    };
});
