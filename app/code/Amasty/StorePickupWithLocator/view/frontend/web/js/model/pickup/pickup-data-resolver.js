define([
    'ko',
    'underscore',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function (ko, _, customerData) {
    'use strict';

    const selectedPickupInfoKey = 'amasty-selected-pickup-info',
        locationsDataKey = 'amasty-storepickup-data',
        storeKey = 'am_pickup_store',
        dateKey = 'am_pickup_date',
        timeKey = 'am_pickup_time',
        curbsideKey = 'am_pickup_curbside';

    var pickupInfo = customerData.get(selectedPickupInfoKey)(),
        locationsData = customerData.get(locationsDataKey),
        indexedStores = null,
        storeId = ko.observable(pickupInfo[storeKey]),
        dateData = ko.observable(pickupInfo[dateKey]),
        timeData = ko.observable(pickupInfo[timeKey]),
        curbsideData = ko.observable(pickupInfo[curbsideKey]),

        /**
         * Set section (customer data) to model
         * @param {Object} data
         * @returns {void}
         */
        setCustomerDataToModel = function (data) {
            if (storeId() !== data[storeKey]) {
                storeId(data[storeKey]);
            }

            if (dateData() !== data[dateKey]) {
                dateData(data[dateKey]);
            }

            if (timeData() !== data[timeKey]) {
                timeData(data[timeKey]);
            }

            if (curbsideData() !== data[curbsideKey]) {
                curbsideData(data[curbsideKey]);
            }
        },

        /**
         * Get selected store data form section (customer data)
         * @returns {*}
         */
        getPickupData = function () {
            return customerData.get(selectedPickupInfoKey)();
        },

        /**
         * Set model data to section (customer data)
         * @returns {void}
         */
        setModelToCustomerData = function () {
            var data = getPickupData();

            data[storeKey] = storeId();
            data[dateKey] = dateData();
            data[timeKey] = timeData();
            data[curbsideKey] = curbsideData();

            customerData.set(selectedPickupInfoKey, data);
        },

        getStoresData = function () {
            return locationsData().stores;
        },

        updateIndexedStores = function () {
            var stores = getStoresData();

            indexedStores = {};
            _.each(stores, function (storeData) {
                indexedStores[storeData.id] = storeData;
            });
        };

    customerData.get(selectedPickupInfoKey).subscribe(setCustomerDataToModel);
    locationsData.subscribe(updateIndexedStores);

    storeId.subscribe(setModelToCustomerData);
    dateData.subscribe(setModelToCustomerData);
    timeData.subscribe(setModelToCustomerData);
    curbsideData.subscribe(setModelToCustomerData);

    return {
        /**
         * Get section data (customer data) item by key
         *
         * @param {String} key
         * @returns {string}
         */
        getDataByKey: function (key) {
            var data = getPickupData()[key];

            return data || '';
        },

        /**
         * @returns {object} - current location object details
         */
        getCurrentStoreData: function () {
            return this.getStoreById(this.storeId());
        },

        /**
         * @returns {Array} - array of object stores
         */
        getStores: function () {
            return locationsData().stores;
        },

        /**
         * @param {Number} scheduleId
         * @return {Object}
         */
        getTimeIntervalsByScheduleId: function (scheduleId) {
            var intervals = locationsData().schedule_data.intervals;

            if (!scheduleId || !intervals[scheduleId]) {
                return intervals.default;
            }

            return intervals[scheduleId];
        },

        /**
         * @param {Number} scheduleId
         * @return {Object}
         */
        getScheduleByScheduleId: function (scheduleId) {
            var items = locationsData().schedule_data.items;

            if (!scheduleId || !items[scheduleId]) {
                return {};
            }

            return items[scheduleId];
        },

        /**
         *
         * @param {Number} id
         * @returns {Object} - location object details
         */
        getStoreById: function (id) {
            var intStoreId = +id;

            if (!indexedStores || !indexedStores[intStoreId]) {
                this._updateIndexedStores();
            }

            return indexedStores[intStoreId];
        },

        /**
         * Remove location from locale storage
         * @param {Number} id
         * @returns {void}
         */
        removeStore: function (id) {
            var pickupData = this.pickupData();

            pickupData.stores = pickupData.stores.filter(function (element) {
                return element.id !== id;
            });

            this.pickupData(pickupData);
            this.storeId(null);
        },

        /**
         * @param {String} key
         * @param {*} value
         * @returns {void}
         */
        extendCurbsideData: function (key, value) {
            var curbsideDataCopy = this.curbsideData();

            if (!curbsideDataCopy) {
                return;
            }

            curbsideDataCopy[key] = value;
            this.curbsideData(curbsideDataCopy);
        },

        _updateIndexedStores: function () {
            updateIndexedStores();
        },

        /**
         * Selected Pickup Store ID
         */
        storeId: storeId,

        /**
         * Selected Date to pickup
         */
        dateData: dateData,

        /**
         * Selected Time to pickup
         */
        timeData: timeData,

        /**
         * Store pickup data Section Observer
         */
        pickupData: locationsData,

        /**
         * Selected curbside state
         */
        curbsideData: curbsideData
    };
});
