/**
 * Copyright © 2018 Wyomind All rights reserved.
 * See LICENSE.txt for license details.
 */

/*
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

require(["jquery", "wyomind_framework_updater"], function ($, updater) {
    $(function () {
        'use strict';
        var initializer = null;
        initializer = setInterval(function () {
            if ($(".data-grid").length > 0) {
                updater.init();
                clearInterval(initializer);
            }
        }, 200);
    });
});