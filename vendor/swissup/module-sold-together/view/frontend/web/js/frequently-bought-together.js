define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/price-utils',
    './helper',
    'mage/translate'
], function ($, _, utils, helper) {
    'use strict';

    /**
     * Get contained to messages
     *
     * @param  {HTMLElement} el
     * @return {jQuery}
     */
    function _getMessagesContainer(el) {
        var $messages;

        $messages = $('.messages', el);

        if (!$messages.length) {
            $messages = $('<div class="messages"></div>');

            if ($(el).hasClass('amazon-stripe')) {
                $('.block-content', el).before($messages);
            } else {
                $('.amazonstyle-checkboxes', el).before($messages);
            }
        }

        return $messages;
    }

    $.widget('swissup.frequentlyBoughtTogether', {
        options: {
            taxDisplay: '0',
            priceFormat: {},
            mainProductPriceBox: '.product-info-price [data-role=priceBox]'
        },

        /**
         * {@inheritdoc}
         */
        _init: function () {
            this.valuesToRestore = false;
            this._initValidator();
            this._addObservers();
            this.updateTotals();
        },

        /**
         * Initialize validator
         */
        _initValidator: function () {
            var self = this;

            if (!$('.details-toggler', this.element).length) {
                return;
            }

            require(['Swissup_SoldTogether/js/validator'], function (validator) {
                self.validator = validator;
            });
        },

        /**
         * Initialize observers
         */
        _addObservers: function () {
            this._on({
                'change .relatedorderamazon-checkbox': 'toggleItem',
                'click .soldtogether-cart-btn': 'addToCartItems',
                // listen events from priceBox widget of Magento_Catalog
                'reloadPrice': 'updateTotals'
            });

            $(this.options.mainProductPriceBox).on('updatePrice', this._onUpdateMainProductPrice.bind(this));

            $(document).on('ajax:addToCart', this.restoreRelatedProductsField.bind(this));
        },

        /**
         * Get selected elements
         */
        getItems: function () {
            var items = $('.amazonstyle-checkboxes .product-item-details, .amazonstyle-images li', this.element);

            return items.filter(function () {
                return $('.checkbox', this).is(':checked');
            });
        },

        /**
         * Update totals near add to cart button
         */
        updateTotals: function () {
            var self = this,
                totalPrice = 0,
                totalExclPrice = 0,
                elTotal = $('.totalprice .price-box .price-container .price-wrapper .price', self.element),
                elIncTax = $('.totalprice .price-box .price-container .price-including-tax .price', self.element),
                elExclTax = $('.totalprice .price-box .price-container .price-excluding-tax .price', self.element);

            this.getItems().each(function () {
                var textPrice,
                    floatPrice;

                if (self.options.taxDisplay === '3') {
                    totalPrice += $('.price-box .price-container .price-including-tax', this).data('price-amount');
                    totalExclPrice += $('.price-box .price-container .price-excluding-tax', this).data('price-amount');
                } else {
                    textPrice = $('.price-box [data-price-type="finalPrice"]', this).text();
                    floatPrice = self.toNumber(textPrice);
                    totalPrice += isNaN(floatPrice) ? 0 : floatPrice;
                }
            });

            if (this.options.taxDisplay === '3') {
                $(elIncTax).html(utils.formatPrice(totalPrice, this.options.priceFormat));
                $(elExclTax).html(utils.formatPrice(totalExclPrice, this.options.priceFormat));
            } else {
                $(elTotal).html(utils.formatPrice(totalPrice, this.options.priceFormat));
            }
        },

        /**
         * Add to cart selected items
         */
        addToCartItems: function () {
            var checkboxes = this.getItems().find('.checkbox:not(.main-product)'),
                submitIds = [],
                submitSuperAttribute = {};

            if (!this.validate()) {
                return;
            }

            this.valuesToRestore = {
                ids: helper.getRelatedIds(),
                superAttribute: helper.getRelatedSupers()
            };

            $('html, body').animate({
                scrollTop: 0
            }, 'slow');

            submitIds = checkboxes.map(function () {
                return $(this).val();
            }).get();

            this.getItems().each(function () {
                var $checkbox = $('.checkbox', this),
                    $productOptions = helper.findProductOptions(this),
                    itemSuper = {};

                if (!$productOptions.length || !$checkbox.is(':checked')) {
                    return;
                }

                $productOptions.each(function () {
                    itemSuper[helper.getAttributeId(this)] = helper.getOptionSelected(this);
                });
                submitSuperAttribute[$checkbox.val()] = itemSuper;

            });

            helper.setRelatedIds(submitIds);
            helper.setRelatedSupers(submitSuperAttribute);

            $('#product-addtocart-button').click();
        },

        /**
         * Validate selected items
         *
         * @return {Boolean}
         */
        validate: function () {
            var $messages,
                isValid = true,
                validator = this.validator;

            if (!validator) {
                return true;
            }

            $messages = _getMessagesContainer(this.element);
            $messages.html('');

            // Check product options and show when they are invalid
            this.getItems().each(function () {
                var $productOptions;

                $productOptions = helper.findProductOptions(this);

                if (!validator.isValidOptions($productOptions)) {
                    isValid = false;
                    $(this).find('.details-toggler').prop('checked', true);
                }
            });

            if (isValid) {
                return true;
            }

            this.validator.showMessage({
                type: 'error',
                text: $.mage.__('Choose options for all selected products.')
            }, $messages);

            $('html, body').animate({
                scrollTop: $messages.offset().top - 100
            }, 'slow');

            return false;
        },

        /**
         * Remove items added in addToCartItems method
         */
        restoreRelatedProductsField: function () {
            if (this.valuesToRestore !== false) {
                helper.setRelatedIds(this.valuesToRestore.ids);
                helper.setRelatedSupers(this.valuesToRestore.superAttribute);
                this.valuesToRestore = false;
            }
        },

        /**
         * Convert string into number
         *
         * @param  {String} string
         * @return {Number}
         */
        toNumber: function (string) {
            var numberPattern,
                number;

            numberPattern = new RegExp('[^0-9\\' + this.options.priceFormat.decimalSymbol + '-]+', 'g'),
            number = string.replace(numberPattern, '');
            number = number.split(this.options.priceFormat.decimalSymbol).join('.');

            return parseFloat(number);
        },

        /**
         * @param  {jQuery.Event} event
         * @return void
         */
        _onUpdateMainProductPrice: function (event) {
            var outerPriceBox = $(event.target).data('mage-priceBox'),
                productId,
                innerPriceBox;

            if (!outerPriceBox) {
                return;
            }

            productId = outerPriceBox.options.productId;
            innerPriceBox = $('[data-role=priceBox][data-product-id=' + productId + ']', this.element)
                .data('mage-priceBox');

            if (innerPriceBox) {
                innerPriceBox.cache = $.extend({}, outerPriceBox.cache);
                innerPriceBox.element.trigger('reloadPrice');
            }
        },

        /**
         * Toggle item when checkbox changed
         *
         * @param  {Event} event
         */
        toggleItem: function (event) {
            var $checkbox = $(event.currentTarget),
                $image = $('#soldtogether-image-' + $checkbox.val());

            if ($checkbox.is(':checked')) {
                $image.removeClass('item-inactive');
                $image.prev('.plus').removeClass('item-inactive');
            } else {
                $image.addClass('item-inactive');
                $image.prev('.plus').addClass('item-inactive');
            }

            this.updateTotals();
        }
    });

    return $.swissup.frequentlyBoughtTogether;
});
