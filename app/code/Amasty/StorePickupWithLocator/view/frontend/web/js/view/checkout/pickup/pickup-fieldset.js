/**
 * Pickup Fieldset UIComponent for Checkout page
 * Nested from Main Pickup Fieldset UIComponent
 */

define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/totals',
    'Magento_Customer/js/customer-data',
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-fieldset',
    'Amasty_StorePickupWithLocator/js/action/shipping-address-form',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver'
], function ($,
    _,
    totals,
    customerData,
    PickupFieldset,
    shippingAddressFormActions,
    pickup,
    pickupDataResolver) {
    'use strict';

    return PickupFieldset.extend({
        defaults: {
            visible: false,
            isToggleShippingAllowed: true,
            selectedStoreSectionName: 'amasty-selected-pickup-info',
            selectors: {
                checkoutNextButton: '#shipping-method-buttons-container .action.continue',
                shippingFormSelector: '#co-shipping-form'
            },
            template: 'Amasty_StorePickupWithLocator/checkout/pickup/pickup-fieldset',
            listens: {
                '${ $.provider }:amStorepickup.data.validate': 'validate'
            }
        },

        initialize: function () {
            this._super();

            pickup.isPickup.subscribe(this.pickupStateObserver, this);

            totals.getItems().subscribe(function () {
                customerData.reload([ this.selectedStoreSectionName ]);
            });

            pickupDataResolver.pickupData.subscribe(function (data) {
                pickup.isPickupValidOrIsNotPickup(!pickup.isPickup() || !!data.stores.length);
            }, this);

            this.initShippingFormMutationObserver();

            return this;
        },

        validate: function () {
            this._delegate([ 'validate' ]);
        },

        initObservable: function () {
            this._super()
                .observe('visible');

            return this;
        },

        onShippingMethodChange: function () {
            var nextButtonState;

            this._super();

            nextButtonState = !pickup.isPickup() || (this.pickupData().stores && this.pickupData().stores.length);
            this.toggleNextButton(nextButtonState);
            pickup.isPickupValidOrIsNotPickup(nextButtonState);
        },

        /**
         * @param {Boolean} isActive
         * @returns {void}
         */
        pickupStateObserver: function (isActive) {
            this.visible(isActive);

            if (!this.isToggleShippingAllowed) {
                return;
            }

            try {
                shippingAddressFormActions.toggle(!isActive);
            } catch (e) {
                console.error(e);
            }
        },

        /**
         * @param {Boolean} state
         * @returns {void}
         */
        toggleNextButton: function (state) {
            var nextButton = this.selectors.checkoutNextButton;

            $.async(nextButton, function () {
                $(nextButton).prop('disabled', !state);
            });
        },

        /**
         * Init shipping form display state mutation observer
         *
         * @returns {void}
         */
        initShippingFormMutationObserver: function () {
            var mutationObserver,
                mutationCallback,
                isToggleShippingAllowed = this.isToggleShippingAllowed;

            mutationCallback = function (mutationsList) {
                _.each(mutationsList, function (mutation) {
                    var isNeedToHideShippingForm = mutation.target.style.display !== 'none'
                        && pickup.isPickup()
                        && isToggleShippingAllowed;

                    if (mutation.type === 'attributes'
                        && mutation.attributeName === 'style'
                        && isNeedToHideShippingForm) {
                        $(mutation.target).toggle(false);
                    }
                });
            };

            mutationObserver = new MutationObserver(mutationCallback);

            $.async(this.selectors.shippingFormSelector, function (element) {
                mutationObserver.observe(element, {
                    attributes: true
                });
            });
        }
    });
});
