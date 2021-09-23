/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/*
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery"], function ($) {
    "use strict";
    return {
        init: function () {
            var data = new Array();
            $('.updater').each(
                function (i, u) {
                    var profile = {
                        id: $(u).attr('data-id'),
                        module: $(u).attr('data-module'),
                        cron: $(u).attr('data-cron'),
                        field: $(u).attr('data-field')
                    };
                    data.push(profile);
                }
            );

            if (typeof updater_url != "undefined") {
                $.ajax({
                    url: updater_url,
                    type: 'POST',
                    showLoader: false,
                    data: {data: data},
                    async: true,
                    success: function (response) {
                        if (typeof response === "object") {
                            $(response).each(function(index, entry) {
                                let elt = $(".updater[data-id='" + entry.id + "']");

                                if (elt.parent("TD").length > 0) {
                                    // "Old" grid
                                    elt.parent("TD").html(entry.content);
                                } else {
                                    // UI grid
                                    elt.parent("DIV").html(entry.content);
                                }
                            });
                        }
                        require(["wyomind_framework_updater"], function (updater) {
                            setTimeout(function () {
                                updater.init();
                            }, 1000);
                        });
                    },
                });
            } else {
                require(["wyomind_framework_updater"], function (updater) {
                    setTimeout(function () {
                        updater.init();
                    }, 1000);
                });
            }
        }
    };
});