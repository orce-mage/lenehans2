define([
    'jquery'
], function ($) {
    'use strict';

    var i = 0;
    var validationMixin = {
        listenFormValidateHandler: function (event, validation) {
            i++;
            if (i === 1) {
                var $form = $(validation.currentForm);

                $form.find('input, select, textarea').on('click keypress', function () {
                    $(this).parents('.control').find('.mage-error[generated], .field-error').fadeOut();
                });

                $form.find('.swatch-option').on('click', function () {
                    $(this).parents('.swatch-attribute').find('.mage-error[generated]').fadeOut();
                });
            }

            var firstActive = $(validation.errorList[0].element || []),
                lastActive = $(validation.findLastActive() ||
                    validation.errorList.length && validation.errorList[0].element || []),
                $win = $(window),
                windowHeight = $win.height(),
                parent, successList;

            if (lastActive.is(':hidden')) {
                parent = lastActive.parent();
                $('html, body').stop().animate({
                    scrollTop: parent.offset().top - windowHeight / 2
                });
            }

            // ARIA (removing aria attributes if success)
            successList = validation.successList;

            if (successList.length) {
                $.each(successList, function () {
                    $(this)
                        .removeAttr('aria-describedby')
                        .removeAttr('aria-invalid');
                });
            }

            if (firstActive.length) {
                var firstTop = firstActive.parent();
                if ($win.scrollTop() > firstTop.offset().top) {
                    $('html, body').stop().animate({
                        scrollTop: firstTop.offset().top - windowHeight / 2 + firstTop.height() / 2
                    });
                }
                firstActive.focus();
            }
        }
    };

    return function () {
        $.widget('mage.validation', $.mage.validation, validationMixin);

        return $.mage.validation;
    };
});
