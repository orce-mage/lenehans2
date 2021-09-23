/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.catalogAddToCart', $.mage.catalogAddToCart, {
            /**
             * @private
             */
            _bindSubmit: function () {
                var self = this;

                if (this.element.data('catalog-addtocart-initialized')) {
                    return;
                }

                this.element.data('catalog-addtocart-initialized', 1);
                this.element.on('submit', function (e) {
                    e.preventDefault();
                    var next = 1;
                    var action = $(this).attr('action');
                    var btnView = $(this).parents('.product-item').find('.btn-quickview');

                    $(this).find('[name*="super"]').each(function (index, item) {
                        var $item = $(item);
                        if ($item.val() === '') {
                            next = 0;
                        }
                    });
                    if ((next === 0 || action.indexOf('options=cart') !== -1) && btnView.length) {
                        btnView.addClass('has-trigger');
                        btnView.trigger('click');
                        return;
                    } else {
                        self.submitForm($(this));
                    }
                });
            },

            /**
             * Handler for the form 'submit' event
             *
             * @param {jQuery} form
             */
            submitForm: function (form) {
                var addToCartButton, self = this;

                if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                    self.element.off('submit');
                    // disable 'Add to Cart' button
                    addToCartButton = $(form).find(this.options.addToCartButtonSelector);
                    addToCartButton.prop('disabled', true);
                    addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                    form.submit();
                } else {
                    self.ajaxSubmit(form);
                }
            }
        });

        return $.mage.catalogAddToCart;
    }
});
