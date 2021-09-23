define([
    'jquery',
    'Magento_Ui/js/form/provider'
], function ($, Provider) {
    'use strict';

    return Provider.extend({
        defaults: {
            pageMessageSelector: '#messages'
        },

        save: function (options) {
            var data = this.get('data');

            $(this.pageMessageSelector).html('');
            this.client.save({ encodedData: JSON.stringify(data) }, options);

            return this;
        }
    });
});
