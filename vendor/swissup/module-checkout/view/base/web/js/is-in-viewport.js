define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param  {jQuery} el
     * @return {Boolean}
     */
    return function (el) {
        var rect = el.getBoundingClientRect(),
            viewport = {
                width: $(window).width(),
                height: $(window).height()
            };

        return rect.top >= 20 &&
            rect.left   >= 0 &&
            rect.bottom <= viewport.height - 20 &&
            rect.right  <= viewport.width;
    };
});
