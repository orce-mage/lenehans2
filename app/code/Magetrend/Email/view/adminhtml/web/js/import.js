define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert'
], function ($, confirmation, alert) {
    'use strict';

    $.widget('mage.emailImportForm', {

        /**
         * Object options
         */
        options: {

        },

        /**
         * Initialize script
         *
         * @private
         */
        _create: function() {
            this._initForm();
        },

        _initForm: function () {
            $('#mteditor_import').click(function (e) {
                e.preventDefault();
                $('input[name="mt_template_files"]').trigger('click');
            });

            $('input[name="mt_template_files"]').on('change',  this, this._onFileChange);
        },

        _onFileChange: function (event) {
            event.data._changeButtonText('Analyzing...');
            var data = $('#mt_template_import').get(0);
            var formData = new FormData(data);
            var main = event.data;
            jQuery.ajax({
                url: event.data.options.uploadAction,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                showLoader: false,
                success: function (response) {
                    if (response.error) {
                        main._showAlert(response.msg);
                        main._changeButtonText('Import');
                    }

                    if (response.success) {
                        var confirmMessage = 'We have found '+ response.data.new + ' new templates and ' +
                            +response.data.update +  ' templates to update. Continue import?';

                        confirmation({
                            title: 'MT Email Template Import',
                            content: confirmMessage,
                            buttons: [
                                {
                                    text: $.mage.__('Cancel'),
                                    class: 'action-secondary action-dismiss',
                                    click: function () {
                                        window.location.reload();
                                    }
                                },
                                {
                                    text: $.mage.__('Continue'),
                                    class: 'action-primary action-accept',
                                    click: function () {
                                        main._importTemplate();
                                        this.closeModal(true);
                                    }
                                }
                            ]
                        });
                    }
                },

                error: function (response) {
                    main._changeButtonText('Import');
                }

            });
        },

        _importTemplate: function () {
            this._changeButtonText('Importing...');
            var formKey = $('#mt_template_import').find('input[name="form_key"]').val();
            var main = this;
            jQuery.ajax({
                url: this.options.importAction,
                type: "POST",
                data: {
                    form_key: formKey
                },
                dataType: 'json',
                showLoader: false,

                success: function (response) {
                    if (response.error) {
                        main._showAlert(response.msg);
                        main._changeButtonText('Import');
                    }

                    if (response.success) {
                        confirmation({
                            title: 'MT Email Template Import',
                            content: 'Templates was imported successful',
                            buttons: [
                                {
                                    text: $.mage.__('OK'),
                                    class: 'action-primary action-accept',
                                    click: function () {
                                        window.location.reload();
                                    }
                                }
                            ]
                        });

                    }
                },

                error: function (response) {
                    main._showAlert(response);
                    main._changeButtonText('Import');
                }

            }).done(function () {
                main._changeButtonText('Import');
            });
        },

        _changeButtonText: function (msg) {
            $('#mteditor_import').find('span').text(msg);
        },

        _showAlert: function (msg) {
            alert({
                title: $.mage.__('MT Email Template Import'),
                content: $.mage.__(msg),
                buttons: [
                    {
                        text: $.mage.__('Ok'),
                        class: 'action-primary action-accept',
                        click: function () {
                            window.location.reload();
                        }
                    }
                ]
            });
        }

    });

    return $.mage.emailImportForm;
});
