define([
    'jquery',
    'underscore',
    'domReady!'
], function ($, _) {
    'use strict';

    $.widget('mst.finder', {
        options: {
            finderId: 0,
            findUrl:  '',
            resetUrl: '',
            ajaxUrl:  ''
        },

        actionFindSelector:  '[data-action-find]',
        actionResetSelector: '[data-action-reset]',
        filterSelector:      '[data-filter]',
        filterCriteria:      {},

        _create: function () {
            $(this.actionFindSelector, this.element).on('click', function () {
                window.location.href = this.options.findUrl.replace('___finder___', this.buildFilterCriteria());
            }.bind(this));

            $(this.actionResetSelector, this.element).on('click', function () {
                window.location.href = this.options.resetUrl;
            }.bind(this));

            this.onReady(
                this.initialize.bind(this)
            )
        },

        initialize: function () {
            _.each(this.$filters(), function (filter) {
                const $filter = $(filter);

                const filterUrlKey = $filter.finderFilter('urlKey');
                this.filterCriteria[filterUrlKey] = $filter.finderFilter('val');
            }.bind(this));

            _.each(this.$filters(), function (filter) {

                const $filter = $(filter);

                $filter.on('finderfilter_change', function () {
                    const filterUrlKey = $filter.finderFilter('urlKey');

                    this.filterCriteria[filterUrlKey] = $filter.finderFilter('val');

                    const filterIds = [];

                    _.each(this.$filters(), function (f) {
                        const $f = $(f);
                        if ($f.index() > $filter.index()) {
                            filterIds.push($f.finderFilter('filterId'));
                        }
                    });

                    this.findFilterOptions(this.filterCriteria, filterIds, function () {
                        this.updateState();
                    }.bind(this))
                }.bind(this));
            }.bind(this));


            this.updateState();
        },

        updateState: function () {
            let isDisabled = false;
            let canFind    = true;

            _.each(this.$filters(), function (filter) {
                const $filter    = $(filter);
                const val        = $filter.finderFilter('val');
                const isRequired = $filter.finderFilter('isRequired');

                $filter.finderFilter('setIsDisabled', isDisabled);

                if (isRequired && val.length === 0) {
                    canFind    = false;
                    isDisabled = true;
                }
            });
    
            if (canFind) {
                $(this.actionFindSelector, this.element).removeAttr('disabled');
            } else {
                $(this.actionFindSelector, this.element).attr('disabled', true);
            }
        },

        findFilterOptions: function (values, filterIds, callback) {
            $.ajax({
                url:      this.options.ajaxUrl,
                data:     {
                    finderId:  this.options.finderId,
                    finder:    this.buildFilterCriteria(),
                    filterIds: filterIds
                },
                type:     'GET',
                dataType: 'json',
                success:  function (response) {
                    _.each(response['data'], function (options, filterId) {
                        filterId = parseInt(filterId);

                        this.updateFilter(filterId, options);
                    }.bind(this));

                    callback();
                }.bind(this)
            });
        },

        updateFilter: function (filterId, options) {
            this.$filter(filterId).finderFilter('setOptions', options);
        },

        onReady: function (callback) {
            const interval = setInterval(function () {
                let counter = 0;
                _.each(this.$filters(), function (filter) {
                    if (this.filterInstance($(filter))) {
                        counter++;
                    }
                }.bind(this));

                if (counter === this.$filters().length) {
                    clearInterval(interval);

                    callback();
                }
            }.bind(this), 10);
        },

        $filters: function () {
            return $(this.filterSelector, this.element);
        },

        $filter: function (filterId) {
            let $filter = null;

            _.each(this.$filters(), function (filter) {
                if ($(filter).finderFilter('filterId') === filterId) {
                    $filter = $(filter);
                }
            });

            return $filter;
        },

        filterInstance: function ($filter) {
            return $filter.data('mstFinderFilter');
        },

        /**
         * @returns {string}
         */
        buildFilterCriteria: function () {
            const map = _.compact(_.map(this.filterCriteria, function (optionIds, filterUrlKey) {
                if (optionIds.length === 0) {
                    return null
                }

                if (Array.isArray(optionIds[0])) { // for multiselect
                    return filterUrlKey + '=' + optionIds[0].join(';');
                } else {
                    return filterUrlKey + '=' + optionIds.join(';');
                }
            }));
            return map.join('/');
        }
    });

    return $.mst.finder;
});
