/*
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

require(["domReady", "jquery", "wyomind_MassImportAndUpdate_cron", "wyomind_MassImportAndUpdate_mapping", "wyomind_MassImportAndUpdate_toolbox"], function (domReady, jQuery, cron, mapping, toolbox) {
    'use strict';
    domReady(function () {

        function Object2Array(obj) {
            return Object.keys(obj).map(function (x) {
                return obj[x];
            });
        }

        /* ========= Cron tasks  ================== */

        jQuery(document).on('change', '.cron-box', function () {
            jQuery(this).parent().toggleClass('selected');
            cron.updateSetting();
        });

        cron.loadSetting();

        /* ======== Blackbox ======================= */

        toolbox.initialize();

        /* ======== Mapping ======================= */

        mapping.initialize();


    });
});

