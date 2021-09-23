define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'mage/validation/validation',
    'magnificpopup'
], function ($, customerData) {
    'use strict';

    $.widget('magebig.ajaxWishlist', {
        options: {
            enabled: null,
            ajaxWishlistUrl: null,
            wishlistBtn: '[data-action="add-to-wishlist"]'
        },

        _create: function () {
            var wl = customerData.get('wishlist');
            this.initEvents();

            if (wl().counter === undefined) {
                this.reloadWl();
            }
        },

        initEvents: function () {
            var self = this;

            $('body').on('click', self.options.wishlistBtn, function (e) {
                e.preventDefault();
                e.stopPropagation();

                if (!self.options.isLogedIn) {
                    var login = $('.authorization-link > a');
                    if ($(self.options.wishlistBtn).parents('.quickview-wrap').length) {
                        if ($.magnificPopup.instance.isOpen) {
                            $.magnificPopup.close();
                        }
                        setTimeout(function() {
                            login[0].click();
                        }, 350);
                    } else {
                        login[0].click();
                    }

                } else {
                    var next = 1;
                    var action = $(this).parents('.product-item').find('[data-role="tocart-form"]');
                    var btnView = $(this).parents('.product-item').find('.btn-quickview');

                    action.find('[name*="super"]').each(function (index, item) {
                        var $item = $(item);
                        if ($item.val() === '') {
                            next = 0;
                        }
                    });
                    if ((next === 0 || (action.length && action.attr('action').indexOf('options=cart') !== -1)) && btnView.length) {
                        btnView.addClass('has-trigger');
                        btnView.trigger('click');
                        return;
                    } else {
                        var params = $(this).data('post').data;
                        params['isWishlist'] = true;
                        var productForm = $(this).parents('#product_addtocart_form');
                        if (productForm.length) {
                            if (productForm.validation('isValid')) {
                                self.showWishlistPopup(params);
                            }
                        } else {
                            self.showWishlistPopup(params);
                        }
                    }
                }
            });
        },

        showWishlistPopup: function (params) {
            var self = this;
            if ($.magnificPopup.instance.isOpen) {
                $.magnificPopup.close();
            }
            $.ajax({
                url: self.options.ajaxWishlistUrl,
                data: params,
                type: 'post',
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

                        self.reloadWl();
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
        },

        reloadWl: function () {
            var sections = ['wishlist'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);
        }
    });

    return $.magebig.ajaxWishlist;
});
