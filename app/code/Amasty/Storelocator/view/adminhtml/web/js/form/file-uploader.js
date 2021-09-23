define([
    'jquery',
    'Magento_Ui/js/form/element/file-uploader'
], function ($, fileUploader) {
    return fileUploader.extend({
        getImageClass: function (file) {
            if ($('input[name="base_img"]').val() == file.name) {
                return 'file-uploader-preview image-uploader-preview am-preview-image am-base-img';
            }

            return 'file-uploader-preview image-uploader-preview am-preview-image';
        },

        makeBase: function(file) {
            $('.am-preview-image').each(function () {
                $(this).removeClass('am-base-img');
            });
            $(event.target.closest('.am-preview-image')).addClass('am-base-img');
            $('input[name="base_img"]').val(file.name).change();
        },
    })
});
