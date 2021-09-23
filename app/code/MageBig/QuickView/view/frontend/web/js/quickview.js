(function(factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", 'magnificpopup'], factory);
    } else {
        factory(jQuery);
    }
}(function($) {
    "use strict";
    $.widget('custom.MageBigQuickView', {
        options: {
            itemClass: '.products.grid .item.product-item, .products.list .item.product-item',
            quickviewLabel: 'Quick View',
            handlerClass: 'btn-quickview',
            baseUrl: '/',
            autoAddButtons: true,
            target: '.product-item-info'
        },
        _create: function() {
            this._buildQuickView(this.options);
        },
        _addButton: function(config) {
            if (config.autoAddButtons) {
                $(config.itemClass).each(function() {
                    var $elem = $(this);
                    if ($elem.find('.' + config.handlerClass).length == 0) {
                        var productId = $elem.find('.price-final_price').data('product-id');
                        var url = config.baseUrl + 'quickview/view/index/id/' + productId;
                        var html = '<div class="qs-btn-container"><a class="' + config.handlerClass + '" rel="nofollow" href="' + url + '"><span>';
                        html += config.quickviewLabel;
                        html += '</span></a></div>';
                        $elem.find(config.target).prepend(html);
                    }
                });
            }
        },
        _buildQuickView: function(config) {
            this._addButton(config);
            var $qv_wrap = $('body'),
                $isProductPage = $qv_wrap.hasClass('catalog-product-view');
            $qv_wrap.magnificPopup({
                delegate: '.' + config.handlerClass,
                type: 'ajax',
                tLoading: '',
                overflowY: 'auto',
                removalDelay: 300,
                mainClass: 'mfp-zoom-in',
                ajax: {
                    settings: {
                        method: 'POST',
                        cache: false
                    },
                    cursor: 'mfp-ajax-cur'
                },
                callbacks: {
                    open: function() {
                        if (!$isProductPage) {
                            $('body').addClass('catalog-product-view');
                        }

                        if( this.fixedContentPos ) {
                            if(this._hasScrollBar(this.wH)){
                                var s = this._getScrollbarSize();
                                if(s) {
                                    $('.sticky-menu.active').css('padding-right', s);
                                    $('#go-top').css('margin-right', s);
                                }
                            }
                        }

                        var btnView = this.currItem.el;
                        var action = btnView.parents('.product-item').find('[data-role="tocart-form"]');
                        var paramUrl = [];

                        action.find('[name*="super"]').each(function (index, item) {
                            var $item = $(item);
                            if ($item.val() !== '') {
                                paramUrl.push($item.attr('data-attr-name') + '=' + $item.val());
                            }
                        });
                        this.paramUrl = null;
                        if (paramUrl.length) {
                            this.paramUrl = $.parseQuery(paramUrl.join('&'));
                        }
                    },
                    close: function() {
                        if (!$isProductPage) {
                            $('body').removeClass('catalog-product-view');
                        }

                        $('.sticky-menu.active').css('padding-right', '');
                        $('#go-top').css('margin-right', '');

                        this.paramUrl = null;
                    },
                    ajaxContentAdded: function() {
                        this.content.trigger('contentUpdated');

                        if (this.paramUrl) {
                            var content = this.content;
                            var params = this.paramUrl;
                            setTimeout(function () {
                                for (var x in params) {
                                    if (params[x] !== '') {
                                        var xLabel = '[id*="option-label-' + x + '"]' + '[option-id*="' + params[x] + '"]';
                                        content.find(xLabel).trigger('click');
                                    }
                                }
                            }, 1000);
                        }

                        if (this.currItem.el.hasClass('has-trigger')) {
                            var form = this.content.find('#product_addtocart_form');
                            setTimeout(function(){
                                form.validation({
                                    radioCheckboxClosest: '.nested'
                                });
                                form.validation('isValid');
                            }, 1100);
                        }

                        this.currItem.el.removeClass('has-trigger');
                        this.paramUrl = null;
                    }
                }
            });
        }
    });
    return $.custom.MageBigQuickView;
}));
