define([
    'jquery',
    'uiComponent'
], function ($, Component) {
    'use strict';
    return Component.extend({
        defaults: {
            url: "",
            id:  ""
        },
        
        initialize: function () {
            this._super();
            
            $.ajax(this.url, {
                method: 'get',
                data:   {
                    id: this.id
                }
            });
        }
    })
});