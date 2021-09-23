define([
    'jquery',
    'Magento_Ui/js/form/element/file-uploader',
    'Amasty_GiftCard/js/actions/create-draggable-element'
], function ($, Uploader, createDraggableElement) {
    'use strict';

    return Uploader.extend({
        defaults: {
            deleteUrl: '',
            textColorField: '',
            codePosY: 0,
            codePosX: 0,
            elementSelector: '[data-amgiftcard-js="code"]',
            imageRendered: false,
            modules: {
                colorField: '${ $.textColorField }',
                balanceField: '${ $.balanceField }',
                dateField: '${ $.dateField }'
            }
        },

        initialize: function () {
            this._super();

            this.colorField = this.colorField();
            this.balanceField = this.balanceField();
            this.dateField = this.dateField();

            return this;
        },

        initObservable: function () {
            return this._super()
                .observe([
                    'codePosX',
                    'codePosY',
                    'imageRendered'
                ]);
        },

        onPreviewLoad: function (file, e) {
            this._super(file, e);

            createDraggableElement(this.elementSelector, this.codePosY, this.codePosX);
            this.imageRendered(true);
            this.setDependentFieldsVisible(true);
        },

        /**
         * Set Dependent Fields Visible
         *
         * @param {boolean} visible
         */
        setDependentFieldsVisible: function (visible) {
            this.colorField.visible(visible);
            this.balanceField.visible(visible);
            this.dateField.visible(visible);
            this.visible(true);
        },

        removeFile: function (file) {
            var deleted = true;

            $.ajax({
                url: this.deleteUrl,
                type: 'GET',
                data: {fileHash: file.name},
                done: function (response) {
                    if (response.error) {
                        deleted = false;
                    }
                }
            });

            if (deleted) {
                this.setDependentFieldsVisible(false);
                this._super();
            }

            return this;
        }
    });
});
