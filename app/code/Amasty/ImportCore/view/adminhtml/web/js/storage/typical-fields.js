define([
    'jquery',
    'underscore',
    'mageUtils'
], function ($, _, utils) {
    'use strict';

    return {
        cachedData: {},
        currentData: null,
        params: {},
        options: {},

        update: function (params, options) {
            this.addParams(params);

            if (options) {
                this.options = options;
            }

            if (!_.has(this.params, 'entity_code') || !Object.keys(this.options).length) {
                return;
            }

            var deferred = $.Deferred(),
                currentKey = this.getCurrentKey();

            this.set(deferred);

            if (this.cachedData[currentKey]) {
                return deferred.resolve(this.cachedData[currentKey]);
            }

            this.get(deferred, this.params.fieldsUrl);
        },

        addParams: function (params) {
            _.each(params, function (value, key) {
                this.params[key] = value;
            }, this);
        },

        get: function (deferred) {
            var formData = new FormData();

            formData.append('form_key', $('[name="form_key"]').val());
            _.each(this.params, function (value, key) {
                formData.append(key, value);
            });

            $.ajax({
                showLoader: true,
                url: this.options.fieldsUrl,
                processData: false,
                contentType: false,
                data: formData,
                type: 'POST',
                dataType: 'json'
            }).done(function (response) {
                if (!response.error) {
                    deferred.resolve(response);
                }
            });
        },

        getCurrentKey: function () {
            var currentKey = '';

            _.each(this.params, function (value) {
                currentKey += (value || '') + '_';
            });

            return currentKey;
        },

        set: function (deferred) {
            var currentKey = this.getCurrentKey(),
                dataProvider = this.options.dataProvider,
                dataScope = this.options.dataScope;

            $.when(deferred).done(function (response) {
                if (this.currentData) {
                    this.prepareToRemoveEmptyFields(response, this.currentData);
                }

                this.cachedData[currentKey] = $.extend(true, {}, response);
                var repairedObject = {};

                this.currentData = this.cachedData[currentKey];
                this.repairObject(dataScope, repairedObject, response);
                dataProvider.isBehaviorChanged = true;
                this.setBehaviorData(dataProvider, dataProvider, repairedObject, dataProvider);
            }.bind(this));
        },

        /**
         *  Set data to provider data.
         *
         * @param {Object} context
         * @param {Object} oldData
         * @param {Object} newData
         * @param {Provider} current
         * @param {String} parentPath
         */
        setBehaviorData: function (context, oldData, newData, current, parentPath) {
            _.each(newData, function (val, key) {
                if (oldData === undefined || _.isArray(val)) {
                    context.set(utils.fullPath(parentPath, key), val);
                } else if (_.isObject(val)) {
                    this.setBehaviorData(context, oldData[key], val, current[key], utils.fullPath(parentPath, key));
                } else if (val != oldData[key] && oldData[key] == current[key]) { // eslint-disable-line eqeqeq
                    context.set(utils.fullPath(parentPath, key), val);
                }
            }, this);
        },

        repairObject: function (dataScope, repairedObject, data) {
            var scopeArray = dataScope.split('.'),
                key = scopeArray.shift();

            repairedObject[key] = {};

            if (scopeArray.length) {
                return this.repairObject(scopeArray.join('.'), repairedObject[key], data);
            }

            repairedObject[key] = data;
        },

        prepareToRemoveEmptyFields: function (newData, oldData) {
            var key;

            for (key in oldData) {
                if (_.isObject(newData[key]) && !_.isArray(newData[key])) {
                    this.prepareToRemoveEmptyFields(newData[key], oldData[key]);
                } else if (!newData[key]) {
                    newData[key] = {};
                    this.createEmptyValue(newData[key]);
                }
            }
        },

        createEmptyValue: function (data) {
            data.enabled = 0;
            data.fields = [];
        }
    };
});
