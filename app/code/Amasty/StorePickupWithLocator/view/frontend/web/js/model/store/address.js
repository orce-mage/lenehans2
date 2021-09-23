/**
 * Store Pickup address model
 */
define([], function () {
    'use strict';

    return function (addressData) {
        const storeAddressType = 'store-pickup-address';

        var locationId = addressData.id,
            regionId;

        if (addressData.region && addressData.region['region_id']) {
            regionId = String(addressData.region.region_id);
        }

        return {
            email: addressData.email || '',
            countryId: addressData.country,
            regionId:  regionId,
            regionCode: addressData.region ? addressData.region['region_code'] : null,
            region: addressData.region ? addressData.region.region : null,
            customerId: addressData['customer_id'],
            street: [addressData.address],
            company: addressData.company || '',
            telephone: addressData.phone || '',
            fax: addressData.fax || '',
            postcode: addressData.zip,
            city: addressData.city || '',
            firstname: addressData.firstname || '',
            lastname: addressData.lastname || '',
            middlename: addressData.middlename || '',
            prefix: addressData.prefix || '',
            suffix: addressData.suffix || '',
            vatId: addressData['vat_id'] || '',
            saveInAddressBook: addressData['save_in_address_book'],
            customAttributes: addressData['custom_attributes'],

            /**
             * @returns {Boolean}
             */
            isDefaultShipping: function () {
                return false;
            },

            /**
             * @returns {Boolean}
             */
            isDefaultBilling: function () {
                return false;
            },

            /**
             * Get related store location ID
             * @returns {int}
             */
            getLocationId: function () {
                return locationId;
            },

            /**
             * @return {String}
             */
            getType: function () {
                return storeAddressType;
            },

            /**
             * @return {String}
             */
            getKey: function () {
                return this.getType() + this.getLocationId();
            },

            /**
             * @return {String}
             */
            getCacheKey: function () {
                return this.getKey();
            },

            /**
             * @return {Boolean}
             */
            isEditable: function () {
                return false;
            },

            /**
             * @return {Boolean}
             */
            canUseForBilling: function () {
                return false;
            }
        };
    };
});
