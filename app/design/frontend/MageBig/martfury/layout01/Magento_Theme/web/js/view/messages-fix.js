define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function(targetModule){

        var initialize = targetModule.prototype.initialize;

        targetModule.prototype.initialize = wrapper.wrap(initialize, function(original){
            $(window).on('beforeunload', function(){
                $.cookieStorage.set('mage-messages', '');
            });

            var mess = $('.page.messages').find('> .messages .message');
            if (mess.length) {
                mess.append('<span class="close-message"><i class="mbi mbi-cross"></i></span>');
                this.showHideMess();
            }

            return original();
        });

        targetModule.prototype.showHideMess = function () {
            var $elm = $('.page .messages .message');
            setTimeout(function () {
                $elm.addClass('active');
            }, 100);
            setTimeout(function () {
                $elm.removeClass('active');
                $.cookieStorage.set('mage-messages', '');
            }, 15000);
            $('.close-message').on('click', function () {
                $elm.removeClass('active');
                $.cookieStorage.set('mage-messages', '');
            });
        };

        targetModule.prototype.prepareMessageForHtml = function (message) {
            return message;
        }

        return targetModule;
    };
});
