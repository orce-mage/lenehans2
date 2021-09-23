define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent'
], function ($, _, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mirasvit_CacheWarmer/component/serverloadinfo',

            fillServerHistory: [],
            serverHistory:     [],
            serverHistoryFrom: '',
            serverHistoryTo:   '',
        },

        initialize: function () {
            var self = this;

            this._super();
            this.initServerLoadHistory();

            return this;
        },

        initServerLoadHistory: function () {
            var serverPoints = [];
            var serverLen = _.keys(this.fillServerHistory).length;


            if (serverLen > 1) {
                this.serverHistoryFrom = _.first(_.keys(this.fillServerHistory));
                this.serverHistoryTo = _.last(_.keys(this.fillServerHistory));

                serverPoints = ['0,100'];

                var i = 0;
                _.each(this.fillServerHistory, function (serverData) {
                    var x = Math.round(100 / (serverLen - 1) * i, 3);
                    var y = 100 - serverData;
                    serverPoints.push(x + "," + y);
                    i++;
                });

                serverPoints.push('100,100');
            }

            this.history = serverPoints.join(' ');
        }
    });
});
