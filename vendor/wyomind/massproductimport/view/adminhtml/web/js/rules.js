/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


define(["jquery",
    "mage/mage",
    "jquery/ui",
    "Magento_Ui/js/modal/modal",
    "Magento_Ui/js/modal/confirm"], function ($) {
    'use strict';
    return {
        row: null,
        initialize: function () {


            document.observe('click', function (elt) {


                if (elt.findElement(".mapping-row .rule")) {
                    var row = elt.findElement(".mapping-row .rule").up("li");
                    this.open(row, this)

                }
                if (elt.findElement("#rule .validate")) {
                    this.validate(this)
                }
                if (elt.findElement("#rule .cancel")) {
                    this.cancel(this)
                }


            }.bind(this));


        }
        ,
        open: function (row, parent) {

            var rule = $(row).find(".mapping-row .rule").attr("data-value");
            parent.row = row;

            $("#overlay-rule").css({display: 'block'})
            $("#rule").draggable();
            $("#rule SELECT").val(rule);


        }
        ,

        validate: function (parent) {
            var val = $("#rule SELECT").val();
            $(parent.row).find(".mapping-row .rule").attr("data-value", val)


            var aggregate = $(parent.row).find('.aggregate')[0];
            var data = jQuery.parseJSON($(aggregate).val())
            data.rule = val;
            aggregate.setValue(Object.toJSON(data));

            require(["wyomind_MassImportAndUpdate_mapping"], function (mapping) {
                mapping.save();
            })
            parent.close(parent);

        },
        cancel: function (parent) {
            $("#rule SELECT").val("");
            parent.validate(parent);

        },
        close: function (parent) {
            var val = $("#rule SELECT").val();

            $("#overlay-rule").css({display: 'none'})
            if (val != null) {
                $(parent.row).find(".mapping-row .rule").addClass("active");
            }
            else {
                $(parent.row).find(".mapping-row .rule").removeClass("active");
            }
            parent.row = null;
        },
        import: function () {
            $('#file-import').modal({
                'type': 'slide',
                'title': 'Import a rule set',
                'modalClass': 'mage-new-category-dialog form-inline',
                buttons: [{
                    text: 'Import',
                    'class': 'action-primary',
                    click: function () {
                        this.importRules();
                    }.bind(this)
                }]
            });
            $('#file-import').modal('openModal');
        }

        ,

        importRules: function () {
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
            if (file.indexOf(".csv") < 0) {
                $("<label>", {
                    "class": "mage-error",
                    "id": "file-error",
                    "text": "Invalid file type. Please use only a tab delimited csv file "
                }).appendTo(input.parent());
                return;
            }

            // file not empty + valid file
            $("#import-form").submit();
        }
    }
})