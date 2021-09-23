define([
    "jquery",
    "MageBig_WidgetPlus/js/parallax",
    "domReady!"
], function($) {
    "use strict";

    return function(config, element) {
        var parallaxConfig = {
            imageSrc: config.imageSrc,
            mirrorContainer: element,
            zIndex: 0
        };
        setTimeout(function() {
            $(element).parallax(parallaxConfig);
        }, 1000);
    }
});