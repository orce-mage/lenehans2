define([
    'jquery',
    'jquery-ui-modules/widget',
    'MageBig_WidgetPlus/js/owl.carousel'
], function ($) {
    'use strict';

    $.widget('magebig.owlWidget', {
        options: {
            autoplay: false,
            autoplayHoverPause: true,
            smartSpeed: 750,
            rewind: true,
            navText: ['<i class="mbi mbi-chevron-left"></i>', '<i class="mbi mbi-chevron-right"></i>'],
            animateOut: 'fadeOut',
            rtl: false
        },
        _create: function() {
            var self = this,
                elm = this.element,
                id = '#'+elm.attr('id'),
                owl = elm.find('.owl-carousel'),
                autoplay = this.options.autoplay;

            if (elm.hasClass('lazyload') && autoplay) {
                this.options.autoplay = false;
                self._initOwl(owl);

                document.addEventListener('lazybeforeunveil', function (e) {
                    var aa = $(e.target).filter(id);
                    if (aa.length && autoplay) {
                        owl.trigger('play.owl.autoplay');
                    }
                });
            } else {
                self._initOwl(owl);
            }
        },
        _initOwl: function (owl) {
            var self = this;

            if (owl.length) {
                if (self.options.rtl || $('body').hasClass('layout-rtl')) {
                    self.options.rtl = true;
                }

                if ($(self.element).parents('.container').length) {
                    self.options.responsiveBaseElement = '.container';
                }

                owl.on('initialized.owl.carousel', function (e) {
                    setTimeout(function () {
                        var video = owl.find('.owl-item.active video');
                        if (video.length) {
                            var paused = video[0].paused;
                            if (paused) {
                                video.get(0).play();
                            }
                        }
                    }, 2000);
                });

                owl.owlCarousel(self.options);

                owl.on('translate.owl.carousel', function (e) {
                    var video = owl.find('.owl-item video');
                    if (video.length) {
                        video.each(function () {
                            $(this).get(0).pause();
                        });
                    }
                });

                owl.on('translated.owl.carousel', function (e) {
                    var video = owl.find('.owl-item.active video');
                    if (video.length) {
                        video.get(0).play();
                    }
                });

                owl.on('dragged.owl.carousel', function (e) {
                    owl.trigger('stop.owl.autoplay');
                });
            }
        }
    });

    return $.magebig.owlWidget;
});
