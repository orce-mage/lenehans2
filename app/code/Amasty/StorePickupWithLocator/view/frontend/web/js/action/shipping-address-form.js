define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/address-list',
    'Magento_Ui/js/lib/knockout/extender/bound-nodes',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'uiRegistry',
    'consoleLogger'
], function ($, _, ko, quote, addressList, boundNodes, domObserver, registry, consoleLogger) {
    'use strict';

    return {
        options: {
            protectedFields: [], // Array of fields protected from hiding. Contain UI indexes of fields
            shippingSectionRegistrySelector: 'index = shippingAddress',
            shippingFieldsetRegistrySelector: 'index = shipping-address-fieldset',
            addressListRegistrySelector: 'index = address-list',
            shippingFormSelector: '#co-shipping-form',
            addressComponentsForToggle: [],
            addressComponentsToggled: []
        },
        currentState: null,
        isCallbackRegistered: false,
        observerName: 'shippingFormVisibilityByPickup',
        shippingSectionCache: null,

        /**
         * Toggle shipping address form or list depending on Store Pickup shipping method
         *
         * @param {Boolean} state - false for hide shipping fieldset. true for show
         * @returns {void}
         */
        toggle: function (state) {
            if (this.currentState !== state) {
                this.currentState = state;
                this.bindCallbacks();

                if (this.shippingSectionCache === null) {
                    registry.get(this.options.shippingSectionRegistrySelector, this.toggleShippingSection);
                } else {
                    this.toggleShippingSection(this.shippingSectionCache);
                }
            }
        },

        /**
         * @param {Object} shippingSection
         * @returns {void}
         */
        toggleShippingSection: function (shippingSection) {
            this.shippingSectionCache = shippingSection;

            if (addressList().length === 0) {
                this.toggleShippingAddressForm();
            } else {
                shippingSection.isNewAddressAdded(!this.currentState);
                registry.get(this.options.addressListRegistrySelector, this.toggleShippingAddressList);
            }
        },

        /**
         * Bind all callback functions
         * @returns {void}
         */
        bindCallbacks: _.once(function () {
            _.bindAll(
                this,
                'toggleShippingSection',
                'toggleShippingAddressForm',
                'toggleShippingAddressList',
                '_registerFormDomObserver',
                '_processShippingFormNode',
                '_removeDomObserver'
            );
        }),

        /**
         * Toggle visibility of shipping address form (guest or customer without address).
         * @returns {void}
         */
        toggleShippingAddressForm: function () {
            var fieldsForToggle,
                $form,
                fieldset,
                state = this.currentState;

            try {
                if (this.isCallbackRegistered) {
                    this._removeDomObserver();
                }

                fieldset = boundNodes.get(this.shippingSectionCache);

                if (fieldset.length) {
                    $form = $(fieldset).find(this.options.shippingFormSelector);

                    if ($form.length) {
                        $form.toggle(state);
                    } else {
                        this._registerFormDomObserver(fieldset);
                    }
                } else {
                    boundNodes.add(this.shippingSectionCache, this._registerFormDomObserver, this.observerName);
                    this.isCallbackRegistered = true;
                }
            } catch (e) {
                consoleLogger.error(e);
            }

            fieldsForToggle = this.getShippingItemsForToggle();

            _.each(fieldsForToggle, function (item) {
                try {
                    // eslint-disable-next-line eqeqeq
                    if (state === false && item.visible() !== state) {
                        this.options.addressComponentsToggled.push(item);
                    }

                    item.visible(state);
                } catch (e) {
                    consoleLogger.error(e);
                }
            }.bind(this));
        },

        /**
         * @param {HTMLElement|HTMLElement[]} fieldsetNode
         * @returns {void}
         * @private
         */
        _registerFormDomObserver: function (fieldsetNode) {
            this.isCallbackRegistered = true;

            if (_.isArray(fieldsetNode)) {
                fieldsetNode.forEach(function (node) {
                    this._registerFormDomObserver(node);
                }, this);
            } else {
                domObserver.get(this.options.shippingFormSelector, this._processShippingFormNode, fieldsetNode);
            }
        },

        /**
         * @param {HTMLElement} node
         * @returns {void}
         * @private
         */
        _processShippingFormNode: function (node) {
            $(node).toggle(this.currentState);
            this._removeDomObserver();
        },

        /**
         * @returns {void}
         * @private
         */
        _removeDomObserver: function () {
            boundNodes.off(this.shippingSectionCache, this.observerName);
            domObserver.off(this.options.shippingFormSelector, this._processShippingFormNode);
            this.isCallbackRegistered = false;
        },

        /**
         * Toggle visibility of shipping address list (registered customer).
         * @param {object} addressListSection
         * @returns {void}
         */
        toggleShippingAddressList: function (addressListSection) {
            var shippingListNodes,
                state = this.currentState;

            try {
                if (ko.isWriteableObservable(addressListSection.visible)) {
                    addressListSection.visible(state);
                } else {
                    shippingListNodes = boundNodes.get(addressListSection);
                    $(shippingListNodes).toggle(state);
                }
            } catch (e) {
                consoleLogger.error(e);
            }
        },

        /**
         * Find shipping address form fields for toggle
         * @returns {Array}
         */
        getShippingItemsForToggle: function () {
            var fieldsForToggle;

            if (this.options.addressComponentsToggled.length) {
                fieldsForToggle = this.options.addressComponentsToggled;
                this.options.addressComponentsToggled = [];

                return fieldsForToggle;
            }

            if (!this.options.addressComponentsForToggle.length) {
                this.filterElements(registry.get(this.options.shippingFieldsetRegistrySelector).elems());
            }

            return this.options.addressComponentsForToggle;
        },

        /**
         * Extract all fields form fieldsets
         *
         * @param {Array} elems
         * @returns {void}
         */
        filterElements: function (elems) {
            if (!elems || !elems.length) {
                return;
            }

            _.each(elems, function (element) {
                if (this._isCollection(element)) {
                    this.filterElements(element.elems());

                    return;// continue
                }

                if (!this.options.protectedFields.includes(element.index)
                    && ko.isObservable(element.visible)
                ) {
                    this.options.addressComponentsForToggle.push(element);
                }
            }.bind(this));
        },

        /**
         * Is component are collection
         *
         * @param {Object} element
         * @returns {Boolean}
         * @private
         */
        _isCollection: function (element) {
            return typeof element.initChildCount === 'number';
        }
    };
});
