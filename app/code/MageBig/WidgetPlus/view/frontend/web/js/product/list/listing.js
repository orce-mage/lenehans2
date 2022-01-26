/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'underscore',
    'Magento_Ui/js/grid/listing',
    'jquery',
    'MageBig_WidgetPlus/js/owl.carousel-set',
    'mage/apply/main',
    'jquery-ui-modules/tooltip'
], function (ko, _, Listing, $, owlWidget, mage) {
    'use strict';

    return Listing.extend({
        defaults: {
            additionalClasses: '',
            filteredRows: {},
            limit: 5,
            listens: {
                elems: 'filterRowsFromCache',
                '${ $.provider }:data.items': 'filterRowsFromServer'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.filteredRows = ko.observable();
            this.initProductsLimit();
            this.hideLoader();
        },

        /**
         * Initialize product limit
         * Product limit can be configured through Ui component.
         * Product limit are present in widget form
         *
         * @returns {exports}
         */
        initProductsLimit: function () {
            if (this.source['page_size']) {
                this.limit = this.source['page_size'];
            }

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Listing} Chainable.
         */
        initObservable: function () {
            this._super()
                .track({
                    rows: []
                });

            return this;
        },

        /**
         * Sort and filter rows, that are already in magento storage cache
         *
         * @return void
         */
        filterRowsFromCache: function () {
            this._filterRows(this.rows);
        },

        /**
         * Sort and filter rows, that are come from backend
         *
         * @param {Object} rows
         */
        filterRowsFromServer: function (rows) {
            this._filterRows(rows);
        },

        /**
         * Filter rows by limit and sort them
         *
         * @param {Array} rows
         * @private
         */
        _filterRows: function (rows) {
            this.filteredRows(_.sortBy(rows, 'added_at').reverse().slice(0, this.limit));
        },

        /**
         * Can retrieve product url
         *
         * @param {Object} row
         * @returns {String}
         */
        getUrl: function (row) {
            return row.url;
        },

        /**
         * Get product attribute by code.
         *
         * @param {String} code
         * @return {Object}
         */
        getComponentByCode: function (code) {
            var elems = this.elems() ? this.elems() : ko.getObservable(this, 'elems'),
                component;

            component = _.filter(elems, function (elem) {
                return elem.index === code;
            }, this).pop();

            return component;
        },

        /**
         * Init slider
         */
        sliderInit: function (index) {
            var count = this.filteredRows._latestValue.length;

            if (index === count - 1) {
                setTimeout(function () {
                    // $("form[data-role='tocart-form']").catalogAddToCart();
                    var rtl = false;
                    if ($('body').hasClass('layout-rtl') || $('body').hasClass('rtl')) {
                        rtl = true;
                    }
                    $('.recently-viewed').owlWidget({
                        "autoplay": false,
                        "autoplayTimeout": 5000,
                        "items": 5,
                        "margin": 30,
                        "rewind": true,
                        "nav": true,
                        "navText": ['<i class="mbi mbi-chevron-left"></i>', '<i class="mbi mbi-chevron-right"></i>'],
                        "dots": false,
                        "responsive": {
                            "0": {"items": 2},
                            "576": {"items": 2},
                            "768": {"items": 3},
                            "992": {"items": 4},
                            "1200": {"items": 5},
                            "1600": {"items": 6}
                        },
                        "rtl": rtl
                    });
                    if ($(window).width() > 767) {
                        $('.mb-tooltip').tooltip({
                            show: null,
                            hide: {
                                delay: 250
                            },
                            position: {
                                my: "center bottom-30",
                                at: "center top",
                                using: function (position, feedback) {
                                    $(this).css(position);
                                    $(this).addClass("magebig-tooltip");
                                }
                            },
                            open: function (event, ui) {
                                ui.tooltip.addClass('in');
                            },
                            close: function (event, ui) {
                                ui.tooltip.removeClass('in');
                            }
                        });
                    }
                }, 1000);
            }
        },

        getPercentDiscount: function (row) {
            var regular_price = row['price_info']['regular_price'],
                special_price = row['price_info']['final_price'];

            if (regular_price > special_price) {
                var discount;
                discount = 100 - parseInt(Math.round((special_price / regular_price) * 100));

                return discount > 0 ? '-' + discount + '%' : false;
            }

            return false;
        }
    });
});
