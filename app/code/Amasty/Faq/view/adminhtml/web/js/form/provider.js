define([
    'Magento_Ui/js/form/provider',
    'underscore'
], function (Provider, _) {
    'use strict';

    return Provider.extend({
        save: function (options) {
            var data = this.get('data');

            data = _.omit(data, 'links');
            this.client.save(data, options);

            return this;
        }
    });
});
