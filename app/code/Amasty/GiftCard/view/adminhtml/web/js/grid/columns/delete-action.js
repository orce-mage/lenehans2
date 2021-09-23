define([
    'Magento_Ui/js/grid/columns/actions',
    'jquery'
], function (Actions, $) {
    'use strict';

    return Actions.extend({
        defaults: {
            deleteUrl: '',
            modules: {
                codesDataProvider: '${ $.provider }'
            }
        },

        deleteCode: function (actionName, codeId) {
            $.ajax({
                showLoader: true,
                url: this.deleteUrl,
                data: {
                    code_id: codeId
                },
                type: 'GET',
                dataType: 'json'
            }).done(function (response) {
                this.codesDataProvider().reload();
            }.bind(this));
        }
    });
});