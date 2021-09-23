define([
    'jquery',
    'mage/translate',
    'mage/validation/validation',
    'magnificpopup'
], function ($) {
    'use strict';

    $.widget('magebig.ajaxCompare', {

        options: {
            enabled: null,
            ajaxCompareUrl: null,
            compareBtnSelector: '.action.tocompare'
        },

        _create: function () {
            if (this.options.enabled == true) {
                this.initEvents();
            }
        },

        initEvents: function () {
            var self = this;
            $('body').on('click', this.options.compareBtnSelector, function (e) {
                e.preventDefault();
                e.stopPropagation();
                var params = $(this).data('post').data;
                self.addCompare(params);
            });
        },

        addCompare: function (params) {
            var self = this;
            $.ajax({
                url: self.options.ajaxCompareUrl,
                data: params,
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $('body').trigger('processStart');
                },
                success: function (res) {
                    $('body').trigger('processStop');
                    if (res.html_popup) {
                        $.magnificPopup.open({
                            items: {
                                src: res.html_popup,
                                type: 'inline'
                            },
                            overflowY: 'auto',
                            removalDelay: 300,
                            mainClass: 'mfp-zoom-in',
                            callbacks: {
                                open: function() {
                                    if( this.fixedContentPos ) {
                                        if(this._hasScrollBar(this.wH)){
                                            var s = this._getScrollbarSize();
                                            if(s) {
                                                $('.sticky-menu.active').css('padding-right', s);
                                                $('#go-top').css('margin-right', s);
                                            }
                                        }
                                    }
                                },
                                close: function() {
                                    $('.sticky-menu.active').css('padding-right', '');
                                    $('#go-top').css('margin-right', '');
                                }
                            }
                        });
                    } else {
                        $('body').trigger('processStop');
                        alert('No response from server');
                    }
                },
                error: function (res) {
                    $('body').trigger('processStop');
                    alert('Error in sending ajax request');
                }
            });
        }
    });

    return $.magebig.ajaxCompare;
});