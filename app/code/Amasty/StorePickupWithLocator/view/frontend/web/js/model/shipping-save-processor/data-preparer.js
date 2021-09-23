define([
    'underscore'
], function (_) {
    'use strict';

    var defaultKeysMap = {
        am_pickup_date: 'date',
        am_pickup_options: {
            curbside_comment: 'curbside_pickup_comment',
            curbside_state: 'is_curbside_pickup'
        },
        am_pickup_store: 'store_id',
        am_pickup_time: [
            'time_from',
            'time_to'
        ]
    };

    return {

        /**
         * Prepare pickup data to send, align to data interface on backend
         *
         * @param {Object} pickupInfo
         * @param {?Object} keysMap
         * @return {Object}
         */
        prepareData: function (pickupInfo, keysMap) {
            var prepared = {},
                mappedKey;

            if (_.isEmpty(keysMap)) {
                keysMap = defaultKeysMap;
            }

            _.each(pickupInfo, function (value, key) {
                mappedKey = keysMap[key] || key;

                if (_.isObject(mappedKey)) {
                    if (key === 'am_pickup_time') {
                        _.extend(prepared, this.prepareIntervalValue(value, mappedKey));
                    } else {
                        _.extend(prepared, this.prepareData(value, mappedKey));
                    }
                } else {
                    prepared[mappedKey] = value;
                }
            }.bind(this));

            return prepared;
        },

        /**
         * @param {String} intervalValue
         * @param {Object} mappedKey
         * @return {Object}
         */
        prepareIntervalValue: function (intervalValue, mappedKey) {
            return _.isEmpty(intervalValue)
                ? {}
                : _.object(mappedKey, intervalValue.split('|'));
        }
    };
});
