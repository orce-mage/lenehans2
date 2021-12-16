define([
    'jquery',
    'uiComponent'
], function ($, Component) {
    'use strict';
    return Component.extend({
        defaults: {
            url: ""
        },

        initialize: function () {
            this._super();

            const $sorter = $('[data-role=sorter],#sorter')

            // if ajax enabled, change will be published. In other case, published after page reload
            $sorter.on('change', function () {
                setTimeout(function () {
                    this.publish($sorter.val())
                }.bind(this), 5000)
            }.bind(this));

            this.publish($sorter.val())
        },

        publish: function (criterion) {
            $.ajax(this.url, {
                method: 'get',
                data:   {
                    criterion: criterion
                }
            });
        }
    })
});
