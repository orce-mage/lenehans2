/*
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery", "Magento_Ui/js/modal/confirm"], function ($, confirm) {
    "use strict";

    return {
        run: function (url) {
            confirm({
                title: "Run profile",
                content: "Are you sure you want to run this profile?",
                actions: {
                    confirm: function () {
                        $('.col-action select.admin__control-select').val("");
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {url: url},
                            showLoader: false,
                            success: function () {
                                location.reload();
                            }
                        });
                    },
                    cancel: function () {
                        $('.col-action select.admin__control-select').val("");
                    }
                }
            });
        },
        delete: function (url) {
            confirm({
                title: "Delete this profile",
                content: "Are you sure you want to delete this profile ?",
                actions: {
                    confirm: function () {
                        document.location.href = url;
                    },
                    cancel: function () {
                        $('.col-action select.admin__control-select').val("");
                    }
                }
            });
        },
        report: function (url, id, name, date) {
            $('.col-action select.admin__control-select').val("");
            $('#profile-report').modal({
                'type': 'slide',
                'title': 'Last import report for profile <b>' + name + '</b> [ID:' + id + '] from <b>' + date + '</b>',
                'modalClass': 'mage-new-category-dialog form-inline',
                buttons: []
            });
            $('#profile-report').html("");
            $('#profile-report').modal('openModal');
            $.ajax({
                url: url,
                data: {id: id},
                type: 'POST',
                showLoader: true,
                success: function (data) {
                    $('#profile-report').html("<hr style='border:1px solid #e3e3e3'/><br/>" + data);
                },
                error: function (data) {
                    $('#profile-report').html("<hr style='border:1px solid #e3e3e3'/><br/>" + data.responseText);
                }
            });
        }
    };
});
