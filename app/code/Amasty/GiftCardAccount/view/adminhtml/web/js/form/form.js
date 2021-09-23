define([
    'jquery',
    'Magento_Ui/js/form/form',
    'uiRegistry',
    'mageUtils',
    'Amasty_GiftCardAccount/js/action/notification'
], function ($, Form, registry, utils, notification) {
    'use strict';

    return Form.extend({
        defaults: {
            modules: {
                modal: 'amgcard_account_listing.amgcard_account_listing.generate_in_bulk',
                gridSource: 'amgcard_account_listing.amgcard_account_listing_data_source'
            }
        },

        initialize: function () {
            this._super();
            this.modal().modal.on('modalclosed', this.reset.bind(this));

            return this;
        },

        generate: function () {
            this.validate();

            if (!this.source.get('params.invalid')) {
                this.submitForm();
            } else {
                this.focusInvalid();
            }
        },

        submitForm: function () {
            var options = {
                ajaxSaveType: this.ajaxSaveType,
                response: {
                    data: this.responseData,
                    status: this.responseStatus
                },
                attributes: {
                    id: this.namespace
                }
            };

            $('body').trigger('processStart');
            utils.ajaxSubmit({
                url: this.source.submit_url,
                data: this.source.data
            }, options).then(this.afterGeneration.bind(this));
            $('body').trigger('processStop');

            return this;
        },

        afterGeneration: function (response) {
            notification.add(response.message, response.isError);

            if (!response.isError) {
                this.gridSource().params.random = Math.random();
                this.gridSource().reload();
                this.modal().closeModal();
            }
        }
    });
});
