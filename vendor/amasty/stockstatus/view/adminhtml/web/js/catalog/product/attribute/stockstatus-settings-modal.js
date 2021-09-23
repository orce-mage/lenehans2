/** @global FORM_KEY **/
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/template',
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget',
    'mage/translate',
], function ($, alert, mageTemplate) {
    $.widget('mage.stockstatusSettingsModal', {
        options: {
            url: '',
            optionId: 0,
            settingsFormSelector: '#stockstatus_advanced_setting_form',
            loaderTemplateSelector: '#stockstatus-loader-html',
            storeSwitcherStoreSelector: '#preview_selected_store',
            storeSwitcherFormSelector: '#preview_form'
        },

        modal: null,
        loaderTemplate: null,

        /**
         *
         * @param {object} options
         * @private
         */
        _create: function (options) {
            this.initDOMBindings();
            this.loaderTemplate = mageTemplate(this.options.loaderTemplateSelector)
        },

        initDOMBindings: function () {
            this.element.on('click', this.handleClick.bind(this));
        },

        /**
         *
         * @param {Event} event
         */
        handleClick: function (event) {
            this.initModal();
            this.doAjax();
        },

        showSpinnerInModal: function () {
            $(this.modal).html(this.loaderTemplate());
        },

        /**
         *
         * @param {object|null} requestData
         * @param {object|null} config
         * @param {function|null} callback
         */
        doAjax: function (requestData, config, callback) {
            if (requestData instanceof FormData) {
                requestData.append('form_key', FORM_KEY)
            } else {
                requestData = Object.assign({form_key: FORM_KEY}, requestData);
            }

            callback = callback || this.updateModal.bind(this);
            config = Object.assign({
                url: this.options.url,
                beforeSend: this.showSpinnerInModal.bind(this),
                data: requestData,
                dataType: "html",
                success: callback
            }, config);

            $.ajax(config);
        },

        /**
         *
         * @param {string} data
         */
        updateModal: function (data) {
            var modal = $(this.modal),
            modalContent = $(data);
            this.addStoreSwitcherBehavior(modalContent.find(this.options.storeSwitcherFormSelector));
            this.addMainFormBehavior(modalContent.find(this.options.settingsFormSelector));
            modal.html(modalContent);
            modal.trigger('contentUpdated');

        },

        /**
         *
         * @param {jQuery} form
         */
        addMainFormBehavior: function (form) {
            form.on('submit', function (e) {
                var form = $(e.target),
                    url = form.attr('action'),
                    formData = new FormData(e.target);

                form.serializeArray().forEach(function (formElement) {
                    formData.append(formElement.name, formElement.value);
                });

                this.doAjax(formData, {
                    url: url,
                    mimeType: "multipart/form-data",
                    cache: false,
                    processData: false,
                    contentType: false,
                    error: this.processSubmitError.bind(this)
                }, null);

                e.stopPropagation();
                e.preventDefault();
            }.bind(this));
        },

        /**
         * Process form submit request fails
         *
         * @param {jqXHR} errorObject
         */
        processSubmitError: function (errorObject) {
            var messages = [];

            switch (errorObject.status) {
                case 413:
                    messages.push($.mage.__('The size of the image is too big'));
                    break;
                default:
                    messages.push($.mage.__('Undefined error'));
                    break;
            }

            this.doAjax({'error_messages': messages}, null, null);
        },

        /**
         *
         * @param {jQuery} storeSwitcherForm
         */
        addStoreSwitcherBehavior: function (storeSwitcherForm) {
            storeSwitcherForm.on('submit', function (e) {
                var form = $(e.target),
                    requestUrl = form.attr('action'),
                    storeId = form.find(this.options.storeSwitcherStoreSelector).val();

                this.doAjax({'store': storeId}, {url: requestUrl}, null);
                e.stopPropagation();
                e.preventDefault();
            }.bind(this));
        },

        initModal: function () {
            var self = this;

            this.modal = alert({
                title: $.mage.__('Settings'),
                content: self.loaderTemplate(),
                modalClass: 'amstockstatus-attribute-settings-popup',
                buttons: [
                    {
                        text: $.mage.__('Save'),
                        class: 'action-primary action-accept',
                        click: function () {
                            $(self.options.settingsFormSelector).submit();
                        }
                    },
                    {
                        text: $.mage.__('Cancel'),
                        class: 'action-secondary',
                        click: function () {
                            this.closeModal(true);
                        }
                    }
                ]
            });
        }
    });

    return $.mage.stockstatusSettingsModal;
});
