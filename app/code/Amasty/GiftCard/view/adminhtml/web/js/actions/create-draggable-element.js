define([
    'jquery'
], function ($) {
    'use strict';

    return function (selector, positionY, positionX) {
        var img = document.querySelector('[data-amgiftcard-js="image"]'),
            indentBase = 50,
            verticalRatio = img.naturalHeight / img.height,
            horizontalRatio = img.naturalWidth / img.width,
            dragCodeBlock = $(selector);

        if (positionY() / verticalRatio + indentBase > img.height) {
            positionY(img.height - indentBase);
        }

        if (positionX() / horizontalRatio + indentBase > img.width) {
            positionX(img.width - indentBase);
        }

        dragCodeBlock.css('top', parseInt(positionY() / verticalRatio) + 'px');
        dragCodeBlock.css('left', parseInt(positionX() / horizontalRatio) + 'px');

        $(dragCodeBlock).draggable({
            containment: '[data-amgiftcard-js="uploader"]',
            scroll: false,
            stop: function (event, ui) {
                positionX(ui.position.left * horizontalRatio);
                positionY(ui.position.top * verticalRatio);
            }
        });
    };
});
