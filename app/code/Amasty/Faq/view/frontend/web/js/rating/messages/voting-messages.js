define([
    'Magento_Ui/js/view/messages',
    'Magento_Ui/js/model/messages'
], function (Component, messageContainer) {
    'use strict';

    return Component.extend({
        defaults: {
            container: {},
        },

        initialize: function (config) {
            var container = new messageContainer();
            this._super(config, container);
            this.container = container;

            return this
        }
    });
});
