/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    "jquery",
    "mbAis"
], function ($, mbAis) {
    "use strict";

    $.widget('magebig.AJScroll', {
        options: {
            item: '.product.product-item',
            container: '.column.main',
            next: '.next',
            prev: '.previous',
            pagination: '.toolbar .pages',
            delay: 600,
            negativeMargin: 150,
            isRedirectCart: 0
        },

        _create: function () {
            var self = this;
            self.init();

            $('body').on('reloadAjaxScroll', function () {
                $.mbs().destroy();
                $(window).removeData('mbs');
                $('.mb-trigger').remove();
                self.init();
            });
        },

        init: function () {
            var self = this;

            $(function($) {
                var config = {
                    item: self.options.item,
                    container: self.options.container,
                    next: self.options.next,
                    pagination: self.options.pagination,
                    delay: 600,
                    negativeMargin: self.options.negativeMargin
                };

                $(config.container + ' .toolbar:not(:first)').hide();

                var ais = $.mbs(config),
                    loadingText = self.options.spinnerText,
                    spinner = '<div class="mb-spinner"><div class="loading-mask"><div class="loader"></div></div></div>',
                    triggerText = self.options.trigger.text,
                    triggerHtml = '<div class="mb-trigger mb-trigger-next"><button type="button" class="action primary">'+triggerText+'</button></div>',
                    textPrev = self.options.trigger.textPrev,
                    htmlPrev = '<div class="mb-trigger mb-trigger-prev"><button type="button" class="action primary">'+textPrev+'</button></div>',
                    text = self.options.text,
                    html = '<div class="all-loaded text-center"><span>'+text+'</span></div>';

                if (loadingText) {
                    spinner = '<div class="mb-spinner"><div class="loading-text">'+loadingText+'<span>'+loadingText+'</span></div></div>';
                }

                ais.extension(new mbPagingEx());

                if (self.options.memoryActive) {
                    ais.extension(new mbHistoryEx({
                        prev: self.options.prev
                    }));
                }

                ais.extension(new mbTriggerEx({
                    text: triggerText,
                    html: triggerHtml,
                    textPrev: textPrev,
                    htmlPrev: htmlPrev,
                    offset: self.options.trigger.offset
                }));

                ais.extension(new mbLoadingEx({
                    html: spinner
                }));

                ais.extension(new mbEndEx({
                    text: text,
                    html: html
                }));

                ais.on('load', function(event){
                    if (event.ajaxOptions) {
                        event.ajaxOptions.cache = true;
                    }
                });

                ais.on('render', function (items) {
                    $(items).each(function() {
                        var swatches = $(this).find('[data-role^="swatch-option-"]');
                        if (swatches.length) {
                            var priceBox = '<script type="text/x-magento-init">' + swatches.attr('data-price-box') + '</script>';
                            swatches.append(priceBox);
                        }
                    });
                });

                ais.on('rendered', function (items) {
                    if ($("form[data-role='tocart-form']").length && self.options.isRedirectCart == 0) {
                        $("form[data-role='tocart-form']").catalogAddToCart();
                    }

                    $('body').trigger('contentUpdated');
                });
            });
        }
    });
    return $.magebig.AJScroll;
});
