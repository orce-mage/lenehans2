/**
 * Pickup Fieldset UIComponent for Paypal express review page
 * Nested from Checkout Pickup Fieldset UIComponent
 */
define([
    'jquery',
    'mage/storage',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Amasty_StorePickupWithLocator/js/view/checkout/pickup/pickup-fieldset',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/model/resource-url-manager',
    'Amasty_StorePickupWithLocator/js/action/messages-resolver',
    'Amasty_StorePickupWithLocator/js/model/shipping-save-processor/data-preparer',
    'mage/translate'
], function (
    $,
    storage,
    registry,
    quote,
    PickupFieldset,
    pickup,
    pickupDataResolver,
    urlManager,
    messagesResolver,
    dataPreparer
) {
    'use strict';

    return PickupFieldset.extend({
        defaults: {
            pickupMethodName: 'amstorepickup_amstorepickup',
            isToggleShippingAllowed: false,
            storeErrorMessage: $.mage.__('Please, choose a store where you would like to pick up your order'),
            commentEmptyErrorMessage: $.mage.__('Please fill in the comment field'),
            commentLimitErrorMessage:
                $.mage.__('Comment length limit is exceeded. Please keep it less than {length} symbols'),
            maxCommentLength: 300,
            selectors: {
                shippingMethodInput: '#shipping-method',
                orderDetails: '#details-reload',
                reviewForm: '#order-review-form',
                orderReviewSubmit: '#review-button',
                waitLoadingContainer: '#review-please-wait',
                placeOrderButton: '#review-buttons-container .action.checkout'
            },
            modules: {
                pickupOptionsComment: '${ $.name }.am_pickup_options.comment'
            }
        },

        initialize: function () {
            var shippingMethodElement;

            this._super();

            // Paypal Express Checkout isn't a UIComponent structure, so here we have to use jquery selection.
            shippingMethodElement = $(this.selectors.shippingMethodInput);

            pickup.isPickup(shippingMethodElement.val() === this.pickupMethodName);
            shippingMethodElement.on('change', this.onShippingMethodChange.bind(this));
            $(this.selectors.orderReviewSubmit).on('click', this.onPlaceOrder.bind(this));

            this.curbsideConfig = window.checkoutConfig.amastyStorePickupConfig.curbsideConfig;

            return this;
        },

        onPlaceOrder: function () {
            var quoteId = quote.getQuoteId(),
                pickupInfo = registry.get('checkoutProvider').get('amstorepickup'),
                storeId = pickupInfo['am_pickup_store'],
                request = {
                    quote_id: quoteId,
                    quote_pickup_data: dataPreparer.prepareData(pickupInfo)
                };

            if (pickup.isPickup() && !pickupDataResolver.storeId()) {
                messagesResolver.addMessage({
                    type: 'error',
                    text: this.storeErrorMessage
                });

                return false;
            }

            if (!this.validatePickupOptions()) {
                return false;
            }

            if (storeId) {
                $(this.selectors.waitLoadingContainer).show();

                storage.post(
                    urlManager.getMethodUrl(quoteId, 'saveSelectedPickupData'),
                    JSON.stringify(request),
                    false
                ).done(function () {
                    $(this.selectors.waitLoadingContainer).hide();
                    $(this.selectors.reviewForm).submit();
                }.bind(this)).fail(function (response) {
                    messagesResolver.addMessage({
                        type: 'error',
                        text: response.responseJSON.message
                    });
                    $(this.selectors.waitLoadingContainer).hide();

                    return false;
                }.bind(this));

                return false;
            }

            return true;
        },

        /**
         * Get pickup options validation state
         * @returns {Boolean}
         */
        validatePickupOptions: function () {
            var curbsideData = pickupDataResolver.curbsideData(),
                curbsideEnabledAndSelected = pickup.isPickup()
                    && this.curbsideConfig
                    && curbsideData
                    && curbsideData.checkbox_state === true;

            if (curbsideEnabledAndSelected
                && !curbsideData.comment
                && this.curbsideConfig.comments_enabled
                && this.curbsideConfig.comment_field_required) {
                messagesResolver.addMessage({
                    type: 'error',
                    text: this.commentEmptyErrorMessage
                });

                this.validate();

                return false;
            }

            if (curbsideEnabledAndSelected
                && !this.pickupOptionsComment().validate().valid) {
                messagesResolver.addMessage({
                    type: 'error',
                    text: this.commentLimitErrorMessage.replace('{length}', this.maxCommentLength)
                });

                this.validate();

                return false;
            }

            return true;
        },

        onShippingMethodChange: function (event) {
            var storeId = null,
                isPickup = event.target.value === this.pickupMethodName,
                placeOrderButtonState,
                isStoresExist = !!(this.pickupData().stores && this.pickupData().stores.length);

            pickup.isPickup(isPickup);

            placeOrderButtonState = !isPickup || isStoresExist;
            $(this.selectors.placeOrderButton).prop('disabled', !placeOrderButtonState);

            if (isPickup) {
                storeId = pickupDataResolver.storeId();
            }

            $.ajax({
                url: urlManager.getPathUrl('amstorepickup/paypal/saveShippingAddress'),
                data: { location_id: storeId },
                type: 'post',
                success: function (response) {
                    if (response) {
                        $(this.selectors.orderDetails).html(response);
                    }
                }.bind(this)
            });
        }
    });
});
