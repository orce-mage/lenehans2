define([
    'mage/utils/wrapper',
], function (wrapper) {
    'use strict';

    /**
     * Restrict select store location address as billing address
     */
    return function (selectBillingAddressAction) {
        return wrapper.wrap(selectBillingAddressAction, function (original, address) {
            if (address.getType() !== 'store-pickup-address') {
                original(address);
            }
        });
    };
});
