/**
 *  Amasty Stock Status render widget
 */

define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    $.widget('mage.amStockStatus', {
        options: {},
        selectors: {
            infoLink: '[data-amstock-js="info-link"]',
            stockAlert: '[data-amstock-js="alert"]',
            stock: '.stock',
            alertPrice: '.alert.price',
            validatePriceForm: '#form-validate-price',
            validateStockForm: '#form-validate-stock',
            productAddForm: '.product-add-form',
            dropdowns: 'select.super-attribute-select, select.swatch-select',
            swatchAttrOptions: '.swatch-attribute-options:has(.swatch-option)',
            swatchOption: 'div.swatch-option',
            toCartBox: '.box-tocart'
        },
        classes: {
            stockAlert: 'amstockstatus-stockalert',
            preorderObserved: 'ampreorder-observed'
        },
        configurableStatus: null,
        nodes: {
            spanElement: null,
            bodyElement: null,
            stockAlertElement: null,
            infoLink: null,
            priceAlert: null
        },
        defaultInfoLink: false,
        defaultContents: [],
        defaultPriceAlert: '',

        /**
         * @private
         * @returns {void}
         */
        _create: function () {
            this._initNodes();
            this._initialization();
        },

        /**
         * @private
         * @returns {void}
         */
        _initNodes: function () {
            this.nodes.bodyElement = $('body');
            this.nodes.spanElement = $(this.selectors.stock).first();
            this.nodes.infoLink = $(this.selectors.infoLink);
            this.dropdowns = $(this.selectors.dropdowns);
            this.nodes.stockAlertElement = $('<div/>', {
                class: this.classes.stockAlert,
                'data-amstock-js': 'alert',
                title: $t('Subscribe to back in stock notification'),
                rel: 'external'
            });
            this.nodes.priceAlert = $(this.selectors.alertPrice).length
                ? $(this.selectors.alertPrice) : $(this.selectors.validatePriceForm).parent();
            this.defaultPriceAlert = this.nodes.priceAlert.length ? this.nodes.priceAlert.html() : '';

            if (this.nodes.spanElement.length && this.nodes.infoLink.length === 0) {
                this.nodes.infoLink = $(this.options.info_block).first();
                this.nodes.spanElement.after(this.nodes.infoLink.hide());
            } else {
                this.defaultInfoLink = true;
            }
        },

        /**
         * @private
         * @returns {void}
         */
        _initialization: function () {
            var self = this;

            $(document).on('configurable.initialized', function() {
                self.onConfigure();
            });

            this.nodes.bodyElement.on(
                {
                    'click': function () {
                        setTimeout(function() {
                            self.onConfigure();
                        }, 300);
                    }
                },
                self.selectors.swatchOption + ', ' + self.selectors.dropdowns
            );

            this.nodes.bodyElement.on(
                {
                    'change': function () {
                        setTimeout(function() {
                            self.onConfigure();
                        }, 300);
                    }
                },
                self.selectors.dropdowns
            );
        },

        /**
         * Remove stock alert element
         * @private
         * @returns {void}
         */
        _removeStockAlert: function () {
            $(this.selectors.stockAlert).remove();
        },

        /**
         * Reload default content; Show tocart box; Show price alert
         * @private
         * @returns {void}
         */
        _reloadDefaultContent: function () {
            if (this.nodes.spanElement.length && !this.nodes.spanElement.hasClass(this.classes.preorderObserved)) {
                this.nodes.spanElement.html(this.configurableStatus);
            }

            $(this.selectors.toCartBox).show();

            if (this.nodes.priceAlert.length) {
                this.showPriceAlert(this.defaultPriceAlert);
            }
        },

        /**
         * Generate and show stock alert element
         * @param {Object} code
         * @public
         * @returns {void}
         */
        showStockAlert: function (code) {
            this.nodes.stockAlertElement.clone().html(code).appendTo(this.selectors.productAddForm);

            $(this.selectors.validateStockForm).mage('validation');
        },

        /**
         * @public
         * @returns {String}
         */
        getCurrentSelectedKey: function () {
            var result = '',
                optionId;

            this.settingsForKey = $(this.selectors.dropdowns + ', ' + this.selectors.swatchOption + '.selected').not('.slick-cloned');

            if (this.settingsForKey.length) {
                for (var i = 0; i < this.settingsForKey.length; i++) {
                    if (parseInt(this.settingsForKey[i].value) > 0) {
                        result += this.settingsForKey[i].value + ',';
                    }

                    optionId = $(this.settingsForKey[i]).attr('option-id') || $(this.settingsForKey[i]).data('option-id');

                    if (parseInt(optionId) > 0) {
                        result += optionId + ',';
                    }
                }
            }

            return result;
        },

        /**
         * Configure statuses at product page
         * @public
         * @returns {void}
         */
        onConfigure: function () {
            var keyCheck = '',
                selectedKey,
                trimSelectedKey,
                countKeys;

            this.dropdowns = $(this.selectors.dropdowns + ', ' + this.selectors.swatchAttrOptions);
            this._removeStockAlert();

            if (null == this.configurableStatus && this.nodes.spanElement.length) {
                this.configurableStatus = this.nodes.spanElement.html();
            }

            selectedKey = this.getCurrentSelectedKey();
            trimSelectedKey = selectedKey.substr(0, selectedKey.length - 1);
            countKeys = selectedKey.split(',').length - 1;

            // reload main status
            if ('undefined' !== typeof(this.options[trimSelectedKey])) {
                this._reloadContent(trimSelectedKey);
            } else {
                this._reloadDefaultContent();
            }

            // add statuses to dropdown
            if (this.options['display_in_dropdowns']) {
                var settings = this.dropdowns,
                    nextValue;

                for (var i = 0; i < settings.length; i++) {
                    nextValue = i + 1;

                    if (!settings[i].options) {
                        continue;
                    }

                    for (var x = 0; x < settings[i].options.length; x++) {
                        if (!settings[i].options[x].value) continue;

                        if (countKeys === nextValue) {
                            var keyCheckParts = trimSelectedKey.split(',');

                            keyCheckParts[keyCheckParts.length - 1] = settings[i].options[x].value;
                            keyCheck = keyCheckParts.join(',');
                        } else if (countKeys < nextValue) {
                            keyCheck = selectedKey + settings[i].options[x].value;
                        }

                        if ('undefined' !== typeof (this.options[keyCheck]) && this.options[keyCheck]) {
                            var status = this.options[keyCheck]['custom_status_text'],
                                defaultContentKey = settings[i].id + '-' + settings[i].options[x].value;

                            if (status) {
                                status = status.replace(/<(?:.|\n)*?>/gm, ''); // replace html tags

                                if (settings[i].options[x].textContent.indexOf(status) === -1) {
                                    if ('undefined' == typeof (this.defaultContents[defaultContentKey])) {
                                        this.defaultContents[defaultContentKey] = settings[i].options[x].textContent;
                                    }

                                    settings[i].options[x].textContent = this.defaultContents[defaultContentKey] + ' (' + status + ')';
                                }
                            } else if (this.defaultContents[i + '-' + x]) {
                                settings[i].options[x].textContent = this.defaultContents[defaultContentKey];
                            }
                        }
                    }
                }
            }
        },

        /**
         * Reload default stock status after select option
         * @param {String} key
         * @private
         * @returns {void}
         */
        _reloadContent: function (key) {
            if ('undefined' !== typeof(this.options.changeConfigurableStatus)
                && this.options.changeConfigurableStatus
                && this.nodes.spanElement.length
                && !this.nodes.spanElement.hasClass(this.classes.preorderObserved)
            ) {
                if (this.options[key] && this.options[key]['custom_status']) {
                    this.nodes.infoLink.show();
                    this.nodes.spanElement.html(this.options[key]['custom_status']);
                } else {
                    if (this.defaultInfoLink) {
                        this.nodes.infoLink.show();
                    } else {
                        this.nodes.infoLink.hide();
                    }

                    this.nodes.spanElement.html(this.configurableStatus);
                }

                this.updateAvailability(key);
            }

            if ('undefined' !== typeof(this.options[key]) && this.options[key] && this.options[key]['is_in_stock'] === 0) {
                $(this.selectors.toCartBox).each(function (index,elem) {
                    $(elem).hide();
                });

                if (this.options[key]['stockalert']) {
                    this.showStockAlert(this.options[key]['stockalert']);
                }
            } else {
                $(this.selectors.toCartBox).each(function (index,elem) {
                    $(elem).show();
                });
            }

            if ('undefined' !== typeof(this.options[key])
                && this.options[key]
                && this.options[key]['pricealert']
                && this.nodes.priceAlert.length
            ) {
                this.showPriceAlert(this.options[key]['pricealert']);
            }
        },

        /**
         * Show price alert block
         * @param {Object} code
         * @public
         * @returns {void}
         */
        showPriceAlert: function (code) {
            this.nodes.priceAlert.html(code);
        },

        updateAvailability: function (key) {
            if (this.options['should_load_stock']) {
                var availabilityClass = !this.options[key] || this.options[key]['is_in_stock']
                    ? 'available'
                    : 'unavailable';
                this.nodes.spanElement.removeClass('available unavailable');
                this.nodes.spanElement.addClass(availabilityClass);
            }
        }
    });

    return $.mage.amStockStatus;
});
