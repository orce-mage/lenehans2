define([
    'Magento_Ui/js/form/components/button',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (Button, $, alert, $t) {
    'use strict';

    return Button.extend({
        defaults: {
            template: 'Amasty_GiftCard/form/generate-button',
            codeTmpl: '',
            csvFieldUid: '',
            qty: '',
            generateUrl: '',
            externalCodesProvider: '',
            imports: {
                codeTmpl: '${ $.parentName }.template:value',
                csvFieldUid: '${ $.parentName }.csv:uid',
                qty: '${ $.parentName }.qty:value'
            },
            modules: {
                codesDataProvider: '${ $.externalCodesProvider }',
                source: '${ $.dataProvider }',
                codesForm: '${ $.parentName }'
            }
        },

        action: function () {
            var file = this.codesForm().getChild('csv').file;

            if (!this.code_pool_id) {
                this.showError('Please, save Code Pool before generating codes.');
                return;
            }

            if (!this.validateForm()) return; //field validation

            if (!file && !this.qty) {
                this.showError('Please enter codes quantity to generate or import CSV file with codes.');
                return;
            }

            if (!this.codeTmpl && !file) {
                this.showError('Please enter codes generation template.');
                return;
            }

            var formData = new FormData();
            formData.append('csv', file);
            formData.append('template', this.codeTmpl);
            formData.append('qty', this.qty);
            formData.append('pool_id', this.code_pool_id);
            formData.append('form_key', $('[name="form_key"]').val());

            this.sendGenerationRequest(formData);
        },

        validateForm: function () {
            var isValid = true;

            for (let el of this.codesForm().elems()) {
                if (!el.validation) continue;
                if (el.validate().valid === false) {
                    isValid = false;
                    break;
                }
            }

            return isValid;
        },

        sendGenerationRequest: function (formData) {
            $.ajax({
                showLoader: true,
                url: this.generateUrl,
                processData: false,
                contentType: false,
                data: formData,
                type: "POST",
                dataType: 'json',
            }).done(function (response) {
                if (response.error) {
                    this.showError(response.message);
                } else {
                    this.codesDataProvider().reload();
                }
            }.bind(this));
        },

        showError: function (error) {
            alert({content: $t(error)});
        },

        validate: function () { //workaround for mg 2.2 tab_group::validate
            return {valid: true, target: this};
        }
    });
});
