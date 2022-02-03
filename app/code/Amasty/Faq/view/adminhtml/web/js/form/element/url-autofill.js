define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imports: {
                urlKey: '${ $.provider }:data.url_key'
            },
            exports: {
                urlKey: '${ $.provider }:data.url_key'
            },

        },
        initialize: function () {
            this._super();
            this.makeUrlKey();
            return this;
        },
        onUpdate: function () {
            this._super();
            this.makeUrlKey();
        },
        makeUrlKey: function () {
            var name;
            name = this.value();
            if ((typeof this.urlKey === 'undefined' || this.urlKey === '') && name !== '') {
                name = name.replace(/\s+/g, '-').toLowerCase().replace(/[^a-z0-9-_]+/g,'');
                if (name.length > 250) {
                    name = name.substr(0,249);
                }
                this.set('urlKey', name);
            }
        }
    });
});
