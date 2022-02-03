define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imports: {
                urlKey: '${ $.provider }:data.url_key'
            }
        },
        initialize: function () {
            this._super();
            this.placeholder = this.get('urlKey');
            return this;
        },
    });
});
