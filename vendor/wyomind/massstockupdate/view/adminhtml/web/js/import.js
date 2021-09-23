/*
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


define(["jquery",
    "mage/mage",
    "Magento_Ui/js/modal/modal"], function ($) {
    'use strict';
    return {
        import: function () {
            $('#file-import').modal({
                'type': 'slide',
                'title': 'Import a Profile',
                'modalClass': 'mage-new-category-dialog form-inline',
                buttons: [{
                    text: 'Import',
                    'class': 'action-primary',
                    click: function () {
                        this.importProfile();
                    }.bind(this)
                }]
            });
            $('#file-import').modal('openModal');
        },

        importProfile: function () {
            $("#import-form").find("#file-error").remove();
            var input = $("#import-form").find("input#file");
            var file = input.val();

            // file empty ?
            if (file === "") {
                $("<label>", {

                    "class": "mage-error",
                    "id": "file-error",
                    "text": "This is a required field"
                }).appendTo(input.parent());
                return;
            }

            // valid file ?
            if (file.indexOf(".conf") < 0) {
                $("<label>", {
                    "class": "mage-error",
                    "id": "file-error",
                    "text": "Invalid file type. Please use only a .conf file"
                }).appendTo(input.parent());
                return;
            }

            // file not empty + valid file
            $("#import-form").submit();
        }
    }
})