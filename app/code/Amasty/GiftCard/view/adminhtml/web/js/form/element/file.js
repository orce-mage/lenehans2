define([
    'Magento_Ui/js/form/element/media',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function (File, validator, $t) {
    'use strict';

    return File.extend({
        defaults: {
            maxFileSize: 2500000,
            allowedExtension: '.csv'
        },

        initialize: function () {
            this._super()
                .addFileValidation();

            return this;
        },

        addFileValidation: function () {
            validator.addRule(
                'validate-file-extension',
                function (file) {
                    if (!file) return true;
                    var ext = file.name.substring(file.name.lastIndexOf('.'));

                    return ext == this.allowedExtension;
                }.bind(this),
                $t('Wrong file extension. Please use only .csv files.')
            );

            validator.addRule(
                'validate-file-size',
                function (file) {
                    if (!file) return true;

                    return file.size <= this.maxFileSize;
                }.bind(this),
                $t('The file size is too big.')
            );
        },

        saveFileAndValidate: function (file) {
            this.file = file;

            return this.validate();
        },

        validate: function () {
            var result = validator(this.validation, this.file),
                message = !this.disabled() && this.visible() ? result.message : '',
                isValid = this.disabled() || !this.visible() || result.passed;

            this.error(message);
            this.error.valueHasMutated();
            this.bubble('error', message);

            if (this.source && !isValid) {
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        }
    });
});
