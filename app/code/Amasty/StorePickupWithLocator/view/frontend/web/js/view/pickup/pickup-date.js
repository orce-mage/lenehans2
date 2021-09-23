/**
 * Main Pickup Date UIElement
 */
define([
    'ko',
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/date',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-store',
    'mage/calendar'
], function (ko, $, _, Component, pickup, pickupDataResolver) {
    'use strict';

    return Component.extend({
        defaults: {
            elementTmpl: 'Amasty_StorePickupWithLocator/pickup/date',
            options: {
                showsTime: false,
                showWeek: false,
                firstDay: 1,
                sameDayPickupAllow: true,
                showButtonPanel: false,
                minDate: new Date()
            },
            selectedDayByName: '',
            weekDays: ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            currentStoreDateTime: {},
            minPickupDateTime: {},
            required: '${$.required}'
        },

        visibleComputed: ko.pureComputed(function () {
            return Boolean(pickupDataResolver.storeId() && pickup.isPickup());
        }),

        initialize: function () {
            this._super();

            if (pickupDataResolver.storeId()) {
                this.onChangeStore(pickupDataResolver.storeId());
                this.getDataFromCache = true;
            }

            return this;
        },

        initObservable: function () {
            this._super()
                .observe('selectedDayByName isTodaySelected storeScheduleSelected');

            pickup.isPickup.subscribe(this.pickupStateObserver, this);
            pickupDataResolver.storeId.subscribe(this.onChangeStore, this);
            this.visibleComputed.subscribe(this.visible);

            return this;
        },

        initConfig: function () {
            this._super();

            this.visible = this.visibleComputed();

            this.getPickupDateFromCache();

            this.value = this.dateFromCache;
            this.cartProductsDelay = this.cartProductsDelay
                ? this.cartProductsDelay * 3600 // hours to seconds conversion
                : 0;
            this.minPickupDateTime = {};
            this.options.sameDayPickupAllow = !!this.sameDayPickupAllow;
            this.options.sameDayCutoffTime = this.sameDayCutoffTime ? this.sameDayCutoffTime : null;
            this.options.beforeShowDay = this.restrictDates.bind(this);

            return this;
        },

        onValueChange: function (value) {
            var datepickerDate,
                selectedDate;

            this._super();

            // This is direct access to the element because need to push date object(not string) to customer data
            datepickerDate = $('#' + this.uid).datepicker('getDate');
            selectedDate = datepickerDate && typeof datepickerDate.getFullYear == 'function'
                ? datepickerDate
                : value;

            pickupDataResolver.dateData(selectedDate);
            this.getSelectedDay(datepickerDate, value);
            this.source.trigger('amStorepickup.date.change', {
                date: value,
                store: this.selectedStore
            });
        },

        onChangeStore: function () {
            this.selectedStore = pickupDataResolver.getCurrentStoreData();

            if (this.selectedStore) {
                this.initStoreDateTimeOptions(this.selectedStore);
                this.setDateToFirstPickupDate(this.selectedStore);
            } else {
                this.value('');
            }
        },

        /**
         * Init current store time options to Component
         *
         * @param {Object} store
         * @return {void}
         */
        initStoreDateTimeOptions: function (store) {
            var storeDateTime = new Date(store.current_timezone_time * 1000),
                storeDateTimeWithDelay = new Date((store.current_timezone_time + this.cartProductsDelay) * 1000);

            this.currentStoreDateTime = {
                asDateTimeObject: storeDateTime,
                asTimeObject: {
                    hours: storeDateTime.getUTCHours(),
                    minutes: storeDateTime.getMinutes()
                }
            };

            this.currentStoreDateTimeWithDelay = {
                asDateTimeObject: storeDateTimeWithDelay,
                asTimeObject: {
                    hours: storeDateTimeWithDelay.getUTCHours(),
                    minutes: storeDateTimeWithDelay.getMinutes()
                }
            };

            this.minPickupDateTime = JSON.parse(JSON.stringify(this.currentStoreDateTimeWithDelay));
            this.minPickupDateTime.asDateTimeObject = new Date(this.minPickupDateTime.asDateTimeObject);
        },

        /**
         * Set selected day name and is it today
         *
         * @param {Object} datepickerDate
         * @param {*} value
         * @return {void}
         */
        getSelectedDay: function (datepickerDate, value) {
            var storeDateTime,
                selectedDate;

            if (value) {
                storeDateTime = this.currentStoreDateTime.asDateTimeObject;
                selectedDate = datepickerDate && typeof datepickerDate.getFullYear == 'function'
                    ? datepickerDate
                    : this.firstPickupDate;

                this.selectedDayByName(this.weekDays[selectedDate.getDay()]);
                this.isTodaySelected(this.isDateIsStoreToday(selectedDate, storeDateTime));
            }
        },

        /**
         * Set the first store work day to date field
         *
         * @param {Object} store
         * @return {void}
         */
        setDateToFirstPickupDate: function (store) {
            var firstPickupDate = this.getFirstPickupDate(store);

            this.firstPickupDate = firstPickupDate;

            // This is direct access to the element because change of value does not trigger change of datepicker input
            $('#' + this.uid).datepicker('setDate', firstPickupDate);
            this.onValueChange(firstPickupDate);
        },

        /**
         * Get the first store work day
         *
         * @param {Object} store
         * @return {Date | * | Object.Date|null|Date}
         */
        getFirstPickupDate: function (store) {
            var minPickupDate = this.minPickupDateTime.asDateTimeObject,
                index;

            if (!store.schedule_id) {
                this.storeScheduleSelected(false);

                return this._getDefaultFirstPickupDate();
            }

            this.storeScheduleSelected(true);

            if (this.getDataFromCache && this.restrictDates(new Date(this.dateFromCache))[0]) {
                this.getDataFromCache = false;

                return new Date(this.dateFromCache);
            }

            if (!this._isScheduleValid(store.schedule_id)) {
                return null;
            }

            // break loop after the 31st iteration
            index = 0;

            while (!this.restrictDates(minPickupDate)[0] && index < 32) {
                minPickupDate.setDate(minPickupDate.getUTCDate() + 1);
                index++;
            }

            if (index >= 32) {
                return null;
            }

            return minPickupDate;
        },

        /**
         * @return {Date}
         * @private
         */
        _getDefaultFirstPickupDate: function () {
            var minPickupDate = this.minPickupDateTime.asDateTimeObject;

            if (this.getDataFromCache && new Date(this.dateFromCache) > minPickupDate) {
                this.getDataFromCache = false;

                return new Date(this.dateFromCache);
            }

            if (this.sameDayPickupAllow && this.restrictDates(minPickupDate)[0]) {
                return minPickupDate;
            }

            return new Date(minPickupDate.setDate(minPickupDate.getUTCDate() + 1));
        },

        /**
         * Check existence of enabled days in schedule
         *
         * @param {int} scheduleId
         * @return {boolean}
         * @private
         */
        _isScheduleValid: function (scheduleId) {
            var scheduleArray = pickupDataResolver.getScheduleByScheduleId(scheduleId),
                storeScheduleArray = Object.keys(scheduleArray).map(function (key) {
                    return scheduleArray[key];
                });

            return !!storeScheduleArray.some(function (day) {
                return +day[Object.keys(day)[0]];
            });
        },

        /**
         * Check if date is valid
         * Method returns [false, ''] if date is restricted
         * Method returns [true, ''] if date is NOT restricted
         *
         * @param {Date||String} date
         * @return {[boolean, string]}
         */
        restrictDates: function (date) {
            var selectedStore = this.selectedStore,
                storeDateTime,
                isToday,
                minPickupDateWithoutTime,
                dateWithoutTime,
                currentDayName,
                daySchedule,
                scheduleArray;

            if (!selectedStore) {
                return [false, ''];
            }

            storeDateTime = this.currentStoreDateTime.asDateTimeObject;
            isToday = this.isDateIsStoreToday(date, storeDateTime);

            minPickupDateWithoutTime = new Date(
                this.minPickupDateTime.asDateTimeObject.getUTCFullYear(),
                this.minPickupDateTime.asDateTimeObject.getUTCMonth(),
                this.minPickupDateTime.asDateTimeObject.getUTCDate()
            );

            dateWithoutTime = new Date(
                date.getFullYear(),
                date.getMonth(),
                date.getDate()
            );

            if (dateWithoutTime < minPickupDateWithoutTime) {
                return [false, ''];
            }

            if (selectedStore.schedule_id) {
                scheduleArray = pickupDataResolver.getScheduleByScheduleId(selectedStore.schedule_id);
                currentDayName = this.weekDays[date.getDay()];
                daySchedule = scheduleArray[currentDayName];

                // check current day status in Store Schedule object
                if (!+daySchedule[currentDayName + '_status']) {
                    return [false, ''];
                }
            }

            if (isToday && !this._isSameDayAllowed(daySchedule)) {
                return [false, ''];
            }

            return [true, ''];
        },

        /**
         * @param {object|undefined} daySchedule
         * @return {boolean}
         * @private
         */
        _isSameDayAllowed: function (daySchedule) {
            var minPickupTimeObject = this.minPickupDateTime.asTimeObject;

            if (!this.options.sameDayPickupAllow) {
                return false;
            }

            return !this.isStoreClosedAlready(minPickupTimeObject, daySchedule)
                && !this.isPickupCutOff(minPickupTimeObject, daySchedule);
        },

        pickupStateObserver: function (isActive) {
            if (isActive) {
                this.getPickupDateFromCache();
            }
        },

        getPickupDateFromCache: function () {
            this.dateFromCache = pickupDataResolver.getDataByKey('am_pickup_date');
            this.getDataFromCache = !!this.dateFromCache;
        },

        /**
         * Сhecking the date matches the current store date
         *
         * @param {Date} date
         * @param {Date} storeDateTime
         * @returns {Boolean}
         */
        isDateIsStoreToday: function (date, storeDateTime) {
            return date.getFullYear() === storeDateTime.getUTCFullYear()
                && date.getMonth() === storeDateTime.getUTCMonth()
                && date.getDate() === storeDateTime.getUTCDate();
        },

        /**
         * Compare current store time object and store schedule object for check store work status
         *
         * @param {Object} currentStoreTime
         * @param {Object|undefined} schedule
         * @return {boolean}
         */
        isStoreClosedAlready: function (currentStoreTime, schedule) {
            var lastDayTime = {
                hours: 23,
                minutes: 59
            };

            if (!schedule) {
                return false;
            }

            // If store end of break time more than store close time need to compare store time and last time of the day
            if (this.compareTimeObjects(schedule.break_to, schedule.to)) {
                return this.compareTimeObjects(currentStoreTime, lastDayTime);
            }

            return this.compareTimeObjects(currentStoreTime, schedule.to);
        },

        /**
         * Compare current store time object and cut off time settings field for check store work status
         *
         * @param {Object} currentStoreTime
         * @param {Object|undefined} schedule
         * @return {boolean}
         */
        isPickupCutOff: function (currentStoreTime, schedule) {
            var cutOffTime = {
                hours: new Date(this.options.sameDayCutoffTime * 1000).getUTCHours(),
                minutes: new Date(this.options.sameDayCutoffTime * 1000).getMinutes()
            };

            if (this.compareTimeObjects(currentStoreTime, cutOffTime)) {
                return true;
            }

            return schedule && this.compareTimeObjects(schedule.from, cutOffTime);
        },

        /**
         * Is timeOne more or equal timeTwo
         *
         * @param {Object} timeOne
         * @param {Object} timeTwo
         * @return {boolean}
         */
        compareTimeObjects: function (timeOne, timeTwo) {
            return +timeOne.hours > +timeTwo.hours
                || +timeOne.hours === +timeTwo.hours && +timeOne.minutes >= +timeTwo.minutes;
        }
    });
});
