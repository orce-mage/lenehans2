define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore',
    'mageUtils',
    'jquery/jquery-storageapi',
    'loader',
    'domReady!'
], function ($, Component, ko, _, utils) {
    'use strict';

    $.Suggestion = function (data) {
        this.url = data.url;
        this.title = data.title;
        this.num_results = data.num_results;
    };

    $.Product = function (data) {
        this.name = data.name;
        this.sku = data.sku;
        this.image = data.image;
        this.reviews_rating = data.reviews_rating;
        this.price = data.price;
        this.url = data.url;
    };

    return Component.extend({
        defaults: {
            template: 'MageBig_AjaxSearch/autocomplete',
            loadPopup: ko.observable(false),
            result: {
                suggest: {
                    data: ko.observableArray([])
                },
                product: {
                    data: ko.observableArray([]),
                    size: ko.observable(0),
                    url: ko.observable('')
                }
            },
            anyResultCount: false,

            localStorage: $.initNamespaceStorage('ajaxsearch').localStorage,
            searchText: '',
            lifetime: 60,

            minSearchLength: 3
        },

        initialize: function () {
            var self = this;
            this._super();

            this.anyResultCount = ko.computed(function () {
                var sum = self.result.suggest.data().length + self.result.product.data().length;
                return sum > 0;

            }, this);

            if (this.localStorage) {
                this.initStore();
                this.flushStorage();
            }

            this.spinner = $(this.searchFormSelector);
            this.spinner.loader({icon: '#'});

            utils.limit(this, 'loadEvent', this.searchDelay); // execute 'load' after delay

            $(this.inputSelector)
                .unbind('input') // unbind all magento events
                .on('input', $.proxy(self.loadEvent, this)) // bind ajaxsearch load event
                .on('input', $.proxy(self.searchButtonStatus, this)) // bind show/hide search button event
                .on('focus', $.proxy(self.showPopup, this)); // bind show popup event
            $(document).on('click', $.proxy(self.hidePopup, this)); // bind hide popup event

            $(document).ready($.proxy(self.searchButtonStatus, this));
        },

        initStore: function () {
            var now = new Date().getTime(),
                added = this.localStorage.get('added_at'),
                storeCode = this.localStorage.get('store_code'),
                currencyCode = this.localStorage.get('currency_code');

            if (!currencyCode) {
                this.localStorage.set('currency_code', this.currencyCode);
            }

            if (!storeCode) {
                this.localStorage.set('store_code', this.storeCode);
            }

            if (!added) {
                this.localStorage.set('added_at', now);
            }
        },

        flushStorage: function () {
            var added = this.localStorage.get('added_at'),
                now = new Date().getTime(),
                lifetime = this.lifetime * 60 * 1000,
                storeCode = this.localStorage.get('store_code'),
                currencyCode = this.localStorage.get('currency_code');

            if (this.currencyCode !== currencyCode || this.storeCode !== storeCode) {
                this.localStorage.removeAll();
                this.initStore();
            }

            if (now - added > lifetime) {
                this.localStorage.removeAll();
                this.initStore();
            }
        },

        loadEvent: function () {
            var self = this,
                hasStorage,
                searchField = $(self.inputSelector),
                searchText = searchField.val().trim();

            if (searchText.length < self.minSearchLength) {
                return false;
            }

            hasStorage = this.loadFromLocalStorage(searchText);
            if (!hasStorage) {
                this.searchText = searchText;
                this.loadData();
            } else {
                this.loadPopup(true);
            }
        },

        loadData: function () {
            var self = this;

            if (this.xhr) {
                this.xhr.abort();
            }

            this.searchText = this.searchText.trim();

            this.xhr = $.ajax({
                method: "get",
                dataType: "json",
                url: this.url,
                data: {q: this.searchText},
                beforeSend: function () {
                    self.spinnerShow();
                },
                success: $.proxy(function (response) {
                    if (response.result[0].size) {
                        self.saveToLocalStorage(response, self.searchText);
                    }

                    self.showPopup();
                }),
                complete: function () {
                    self.spinnerHide();
                }
            });
        },

        parseData: function (response) {
            this.setSuggested(this.getResponseData(response, 'suggest'));
            this.setProducts(this.getResponseData(response, 'product'));
        },

        getResponseData: function (response, code) {
            var data = [];

            if (_.isUndefined(response.result)) {
                return data;
            }

            $.each(response.result, function (index, obj) {
                if (obj.code === code) {
                    data = obj;
                }
            });

            return data;
        },

        setSuggested: function (suggestedData) {
            var suggested = [];

            if (!_.isUndefined(suggestedData.data)) {
                suggested = $.map(suggestedData.data, function (suggestion) {
                    return new $.Suggestion(suggestion)
                });
            }

            this.result.suggest.data(suggested);
        },

        setProducts: function (productsData) {
            var products = [];

            if (!_.isUndefined(productsData.data)) {
                products = $.map(productsData.data, function (product) {
                    return new $.Product(product)
                });
            }

            this.result.product.data(products);
            this.result.product.size(productsData.size);
            this.result.product.url(productsData.url);
        },

        loadFromLocalStorage: function (queryText) {
            if (!this.localStorage) {
                return;
            }

            var hash = this._hash(queryText);
            var data = this.localStorage.get(hash);

            if (!data) {
                return false;
            }

            this.parseData(data);

            return true;
        },

        saveToLocalStorage: function (data, queryText) {
            if (!this.localStorage) {
                return;
            }

            var hash = this._hash(queryText);
            this.localStorage.set(hash, data);
        },

        _hash: function (object) {
            var string = JSON.stringify(object) + "";

            var hash = 0, i, chr, len;
            if (string.length === 0) {
                return hash;
            }
            for (i = 0, len = string.length; i < len; i++) {
                chr = string.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0;
            }
            return 'mb' + hash;
        },


        showPopup: function (event) {
            var self = this,
                hasStorage,
                searchField = $(self.inputSelector),
                searchText = searchField.val().trim();

            if (searchText.length < self.minSearchLength) {
                return false;
            }

            hasStorage = this.loadFromLocalStorage(searchText);
            if (!hasStorage) {
                self.searchText = searchText;
                self.loadData();
            } else {
                self.loadPopup(true);
            }
        },

        hidePopup: function (event) {
            if ($(this.searchFormSelector).has($(event.target)).length <= 0) {
                this.loadPopup(false);
            }
        },

        searchButtonStatus: function (event) {
            var self = this,
                searchField = $(self.inputSelector),
                searchButton = $(self.searchFormSelector + ' ' + self.searchButtonSelector),
                searchButtonDisabled = (searchField.val().length <= 0);

            searchButton.attr('disabled', searchButtonDisabled);
        },

        spinnerShow: function () {
            this.spinner.loader('show');
        },

        spinnerHide: function () {
            this.spinner.loader('hide');
            this.spinner.find('.loading-mask').hide();
        }
    });
});
