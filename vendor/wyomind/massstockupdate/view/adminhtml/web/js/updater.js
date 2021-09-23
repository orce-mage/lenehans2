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
                    var profile = [u.id.replace("profile_", ""), $(u).attr('name'), $(u).attr('data-cron')];
                    data.push(profile);
                }
            );
            $.ajax({
                url: updater_url,
                type: 'POST',
                showLoader: false,
                data: {data: data},
                success: function (response) {

                    if (typeof response === "object") {
                        response.each(function (r) {
                            $("#profile_" + r.id).replaceWith(r.content)
                        });
                    }
                    require(["wyomind_MassImportAndUpdate_updater"], function (updater) {
                        setTimeout(function () {
                            updater.init();
                        }, 1000);
                    });
                }
            });
        }
    };
});