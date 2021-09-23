define([
    'Magento_Ui/js/form/element/file-uploader',
], function (FileUploader) {

    return FileUploader.extend({
        initialize: function () {
            this._super();
            this.inputName = 'file';
            this.maxFileSize = 2000000;

            return this;
        },

        onFilesChoosed: function (e, data) {
            if (this.allowedExtensions === undefined || this.allowedExtensions === false) {
                this.allowedExtensions = ' '; // Invalidate file type
            }

            this._super(e, data);
        }
    });
});
