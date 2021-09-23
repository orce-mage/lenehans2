/**
 * Gift card pricing
 */

define([
    'uiComponent',
    'jquery'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            prices: '',
            currencyCode: '',
            currentPrice: null,
            feeStatus: '',
            feeDisabled: '0',
            feeValue: null,
            feeValueConverted: null,
            productId: null,
            priceTypePercent: 1,
            priceTypeFixed: 2,
            customAmount: '',
            customMinAmount: '',
            customMaxAmount: '',
            customMinAmountCurrency: '',
            customMaxAmountCurrency: '',
            isValueValid: true,
            element: {
                priceLabel: '[data-amcard-js="price"]'
            },
            imports: {
                preconfiguredValues: '${ "giftCard" }:preconfiguredValues'
            }
        },

        initialize: function () {
            this._super();

            this.sortPrices();
            this.prepareCustomAmountRange();

            if (this.prices().length > 1 || this.isOpenAmount) {
                this.setDefaultPrice();
            }

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'prices',
                'currentPrice',
                'isValueValid',
                'customAmount'
            ]);

            return this;
        },

        sortPrices: function () {
            this.prices().sort(function (a, b) {
                return a.value - b.value;
            });
        },

        /**
         * Change product price value
         *
         * @param {Object} item
         * @param {Object} event
         */
        changeProductPrice: function (item, event) {
            var buttons = $(this.element.priceLabel),
                value = parseFloat(item.convertValue);

            this.customAmount('');

            this.applyPrice(value);
        },

        /**
         * Apply product price
         *
         * @param {float} value
         */
        applyPrice: function (value) {
            if (this.feeStatus !== this.feeDisabled) {
                value = this.applyingFee(value);
            }

            this.updatePrice(value);
        },

        setDefaultPrice: function () {
            var priceBoxes = $('[data-product-id="' + this.productId + '"]'),
                config = {
                'priceConfig': {
                    'prices': {
                        'finalPrice': {
                            'amount': 0
                        }
                    }
                }
            };

            priceBoxes.priceBox(config);
        },

        /**
         * Update product price
         *
         * @param {float} value
         */
        updatePrice: function (value) {
            var changes = {
                'giftcard': {
                    'finalPrice': {
                        'amount': value
                    }
                }
            };

            $('#product-price-' + this.productId).trigger('updatePrice', changes);
        },

        /**
         * Apply product fee
         *
         * @param {float} value
         */
        applyingFee: function (value) {
            if (this.feeType == this.priceTypePercent) {
                value += value * this.parseFee(this.feeValue) / 100;
            } else if (this.feeType == this.priceTypeFixed) {
                value += this.parseFee(this.feeValueConverted);
            }

            return value;
        },

        parseFee: function (feeValue) {
            var fee = parseFloat(feeValue);

            if (isNaN(fee)) {
                fee = 0;
            }

            return fee;
        },

        getAmountRange: function () {
            switch (this.customAmountRangeState) {
                case 1:
                    return 'Min: ' + this.customMinAmountCurrency;
                case 2:
                    return 'Max: ' + this.customMaxAmountCurrency;
                case 3:
                    return this.customMinAmountCurrency + ' - ' + this.customMaxAmountCurrency;
                default:
                    return '';
            }
        },

        noRestrictions: function () {
            return !parseFloat(this.customMinAmount) && !parseFloat(this.customMaxAmount);
        },

        prepareCustomAmountRange: function () {
            if (this.noRestrictions()) {
                return this.customAmountRangeState = 0;
            }

            var customMinAmount = parseFloat(this.customMinAmount),
                customMaxAmount = parseFloat(this.customMaxAmount);

            if (customMinAmount && !customMaxAmount) {
                return this.customAmountRangeState = 1;
            }

            if (!customMinAmount && customMaxAmount) {
                return this.customAmountRangeState = 2;
            }

            return this.customAmountRangeState = 3;
        },

        initCustomValidate: function (customAmount) {
            var customMinAmount = parseFloat(this.customMinAmount),
                customMaxAmount = parseFloat(this.customMaxAmount),
                validate = false;

            switch (this.customAmountRangeState) {
                case 0:
                    validate = true;

                    break;
                case 1:
                    if (customMinAmount <= customAmount()) {
                        validate = true;
                    }

                    break;
                case 2:
                    if (customMaxAmount >= customAmount()) {
                        validate = true;
                    }

                    break;
                case 3:
                    if (customMaxAmount >= customAmount() && customMinAmount <= customAmount()) {
                        validate = true;
                    }

                    break;
            }

            this.isValueValid(validate);
        },

        addCustomAmount: function (customAmount) {
            if (!this.isValueValid() || !customAmount()) {
                return;
            }

            $(this.element.priceLabel).removeClass('-active');
            customAmount = parseFloat(customAmount().replace(/,/g, '.'));

            if (typeof customAmount !== 'number') {
                return;
            }

            this.currentPrice('');
            this.applyPrice(customAmount);
        },

        getPriceValue: function (name) {
            var price = +this.preconfiguredValues[name];

            if (price) {
                this.applyPrice(price);
                return price;
            }

            return '';
        },

        getCardPriceValue: function (name) {
            var price = this.getPriceValue(name);

            if (price) {
                price = this.prices().filter(function (el) {
                    return el.convertValue === price
                })[0]; // IE compatibility

                if (price !== undefined) {
                    this.currentPrice(price.value);
                }
            }
        },

        getCustomPriceValue: function (name) {
            var price = this.getPriceValue(name);

            if (price) {
                this.customAmount(price);
            }
        }
    });
});
