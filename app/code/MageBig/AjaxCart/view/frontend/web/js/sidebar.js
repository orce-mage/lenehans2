/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'underscore',
    'magnificpopup',
    'mage/decorate',
    'mage/cookies'
], function ($, customerData, _) {
    'use strict';

    return function (widget) {

        $.widget('mage.sidebar', $.mage.sidebar, {

            /**
             * Update sidebar block.
             */
            update: function () {
                $(this.options.targetElement).trigger('contentUpdated');
            },

            /**
             * @private
             */
            _initContent: function () {
                var self = this,
                    events = {};

                this.element.decorate('list', this.options.isRecursive);

                /**
                 * @param {jQuery.Event} event
                 */
                events['click ' + this.options.button.close] = function (event) {
                    event.stopPropagation();
                    $(self.options.targetElement).dropdownDialog('close');
                };
                events['click ' + this.options.button.checkout] = $.proxy(function () {
                    var cart = customerData.get('cart'),
                        customer = customerData.get('customer'),
                        element = $(this.options.button.checkout);
                    if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                        // set URL for redirect on successful login/registration. It's postprocessed on backend.
                        $.cookie('login_redirect', this.options.url.checkout);

                        if (this.options.url.isRedirectRequired) {
                            element.prop('disabled', true);
                            location.href = this.options.url.loginUrl;
                        } else {
                            if ($.magnificPopup.instance.isOpen) {
                                $.magnificPopup.close();
                            }
                            setTimeout(function() {
                                if ($('.authorization-link a').length) {
                                    $('.authorization-link a')[0].click();
                                }
                            }, 350);
                        }

                        return false;
                    }
                    element.prop('disabled', true);
                    location.href = this.options.url.checkout;
                }, this);

                /**
                 * @param {jQuery.Event} event
                 */
                events['click ' + this.options.button.remove] = function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    self._removeItem($(event.currentTarget));
                };

                /**
                 * @param {jQuery.Event} event
                 */
                events['keyup ' + this.options.item.qty] = function (event) {
                    self._showItemButton($(event.target));
                };

                /**
                 * @param {jQuery.Event} event
                 */
                events['change ' + this.options.item.qty] = function (event) {
                    self._showItemButton($(event.target));
                };

                /**
                 * @param {jQuery.Event} event
                 */
                events['click ' + ':button.cart-btn-qty'] = function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    self._increaseQty($(event.currentTarget));
                };

                /**
                 * @param {jQuery.Event} event
                 */
                events['click ' + this.options.item.button] = function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    self._updateItemQty($(event.currentTarget));
                };

                /**
                 * @param {jQuery.Event} event
                 */
                events['focusout ' + this.options.item.qty] = function (event) {
                    self._validateQty($(event.currentTarget));
                };

                this._on(this.element, events);
            },

            /**
             * @param {HTMLElement} elem
             * @private
             */
            _showItemButton: function (elem) {
                var itemId = elem.data('cart-item'),
                    itemQty = elem.data('item-qty');

                if (this._isValidQty(itemQty, elem.val())) {
                    $('#update-cart-item-' + itemId).show();
                } else if (elem.val() == 0) { //eslint-disable-line eqeqeq
                    this._hideItemButton(elem);
                } else {
                    this._hideItemButton(elem);
                }
            },

            /**
             * @param {HTMLElement} elem
             * @private
             */
            _increaseQty: function (elem) {
                var itemId = elem.data('cart-item'),
                    input = $('#cart-item-' + itemId + '-qty'),
                    newVal,
                    defaultValue = 1,
                    inrement = 1,
                    $button = elem,
                    oldValue = input.val();

                if (!oldValue || oldValue < inrement) {
                    oldValue = 0;
                }

                if ($button.hasClass('plus')) {
                    newVal = parseFloat(oldValue) + inrement;
                } else {
                    if (oldValue > defaultValue && parseFloat(oldValue) - inrement > 0) {
                        newVal = parseFloat(oldValue) - inrement;
                    } else {
                        newVal = defaultValue;
                    }
                }
                input.val(newVal);
                input.trigger('change');
            },

            /**
             * @param {HTMLElement} elem
             * @private
             */
            _hideItemButton: function (elem) {
                var itemId = elem.data('cart-item');

                $('#update-cart-item-' + itemId).hide();
            },

            /**
             * Update content after update qty
             *
             * @param {HTMLElement} elem
             */
            _updateItemQtyAfter: function (elem) {
                var productData = this._getProductById(Number(elem.data('cart-item')));

                if (!_.isUndefined(productData)) {
                    $(document).trigger('ajax:updateCartItemQty');

                    if (window.location.href === this.shoppingCartUrl) {
                        window.location.reload();
                    }
                }
                this._hideItemButton(elem);
            },

            /**
             * Update content after item remove
             *
             * @param {Object} elem
             * @private
             */
            _removeItemAfter: function (elem) {
                var productData = this._getProductById(Number(elem.data('cart-item')));

                if (!_.isUndefined(productData)) {
                    $(document).trigger('ajax:removeFromCart', {
                        productIds: [productData['product_id']],
                        productInfo: [
                            {
                                'id': productData['product_id']
                            }
                        ]
                    });

                    if (window.location.href.indexOf(this.shoppingCartUrl) === 0) {
                        window.location.reload();
                    }
                }
            },

            /**
             * Retrieves product data by Id.
             *
             * @param {Number} productId - product Id
             * @returns {Object|undefined}
             * @private
             */
            _getProductById: function (productId) {
                return _.find(customerData.get('cart')().items, function (item) {
                    return productId === Number(item['item_id']);
                });
            },

            /**
             * @param {String} url - ajax url
             * @param {Object} data - post data for ajax call
             * @param {Object} elem - element that initiated the event
             * @param {Function} callback - callback method to execute after AJAX success
             */
            _ajax: function (url, data, elem, callback) {
                $.extend(data, {
                    'form_key': $.mage.cookies.get('form_key')
                });
                elem.parents('.minicart-wrapper').trigger('contentLoading');
                $.ajax({
                    url: url,
                    data: data,
                    type: 'post',
                    dataType: 'json',
                    context: this,

                    /** @inheritdoc */
                    beforeSend: function () {
                        elem.attr('disabled', 'disabled');
                    },

                    /** @inheritdoc */
                    complete: function () {
                    }
                })
                    .done(function (response) {
                        var msg;

                        if (response.success) {
                            callback.call(this, elem, response);
                        } else {
                            msg = response['error_message'];

                            if (msg) {
                                alert(msg);
                            }
                        }
                    })
                    .fail(function (error) {
                        elem.attr('disabled', null);
                        console.log(JSON.stringify(error));
                    });
            }
        });

        return $.mage.sidebar;
    }
});
