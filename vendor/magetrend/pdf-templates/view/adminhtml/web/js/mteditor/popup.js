/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
var popup = (function() {
    var isOpen = false;

    var bgLayer = null;

    var popupBox = null;

    var lastTopPosition = 0;

    var config = {};

    var init = function(settings) {
        config = {
            closeSelector:          '#esns_box_close',
            backgroundSelector:     '#esns_background_layer',
            boxSelector:            '#esns_box_layer',
            layerClose:             true,
            autoPosition:           true,
            disableClose:           false
        };
        $.extend(config, settings );

        setup();
    };

    var setup = function() {

        bgLayer = $(config.backgroundSelector);
        popupBox = $(config.boxSelector);

        if (config.autoPosition) {
            $(document).scroll(function() {
                eventScroll();
            });

            $(window).resize(function() {
                eventResize();
            });
        }

        $(config.closeSelector).click(function(){
            close();
        });

        if (config.layerClose) {
            $(config.backgroundSelector).click(function(e) {
                if ('#'+e.target.id == config.backgroundSelector) {
                    close();
                }
            });
        }
    };

    var  content = function(options, beforeOpen, callback, beforeClose) {
        config.disableClose = options.disableClose;
        if (config.disableClose) {
            $(config.closeSelector).hide();
        } else {
            $(config.closeSelector).show();
        }



        $('#esns_box_content').html('').append($('#content_popup').clone().html());
        $('#esns_box_content .popup-content').html($(options.contentSelector).html());
        beforeOpen();
        popup.open();
        $('#esns_box_content *[data-action="0"]').click(function(){
            beforeClose();
            if (!options.disableCloseAfterSubmit) {
                popup.close();
            }

        });
        $('#esns_box_content a[data-action="1"]').click(function(e){
            callback(e);
            if (!options.disableCloseAfterSubmit) {
                popup.close();
            }
        });
    };

    var confirm = function(options, callbackYes, callbackNo) {
        $('#esns_box_content').html('').append($('#confirm_popup').clone().html());
        $('#esns_box_content .popup-msg').html(options.msg);
        popup.open();
        $('#esns_box_content a[data-action="0"]').click(function(){
            callbackNo();
            if (!options.disableAutoClose) {
                popup.close();
            }

        });
        $('#esns_box_content a[data-action="1"]').click(function(){
            callbackYes();
            if (!options.disableAutoClose) {
                popup.close();
            }
        });
    };

    var open = function() {
        if(!isOpen) {
            bgLayer.fadeIn();
            bgLayer.css('height', $(document).height()+'px');
            popupBox.css('margin-top', getTopPosition()+'px')
                .css('max-height', ($(window).height() - 40)+'px')
                .css('overflow-y', 'auto');
            isOpen = true;
        }
    };

    var rePossition = function () {
        bgLayer.css('height', $(document).height()+'px');
        popupBox.css('margin-top', getTopPosition()+'px')
            .css('max-height', ($(window).height() - 40)+'px')
            .css('overflow-y', 'auto');
    };

    var close = function(forceClose) {
        if (!forceClose && config.disableClose == true) {
            return;
        }
        if (isOpen) {
            bgLayer.fadeOut();
            isOpen = false;
        }
    };

    var getTopPosition = function() {
        var scrollTop = jQuery(document).scrollTop();
        var windowH = jQuery(window).height();
        var boxH = popupBox.height();
        var boxTop = 0;
        if (windowH <= boxH) {
            boxTop = scrollTop;
        } else {
            boxTop = scrollTop + ((windowH - boxH ) /2);
        }
        return boxTop;
    };

    var eventScroll = function()
    {
        var windowH = $(window).height();
        var boxH = popupBox.height();
        var scrollTop = $(document).scrollTop();
        var diff = Math.abs(lastTopPosition - scrollTop);
        if (windowH <= boxH) {
            return;
        }

        if (diff > 150
            || scrollTop == 0
            || scrollTop + $(window).height() == $(document).height()
        ) {
            lastTopPosition = scrollTop;
            popupBox.css('margin-top', getTopPosition()+'px');
        }
    };

    var eventResize = function() {
        var windowH = $(window).height();
        var boxH = popupBox.height();
        if (windowH <= boxH) {
            return;
        }
        popupBox.css('margin-top', getTopPosition()+'px');
    };



    return {
        init:           init,
        config:         config,
        confirm:        confirm,
        content:        content,
        open:           open,
        close:          close,
        eventResize:    eventResize,
        rePossition: rePossition
    };
})(jQuery);