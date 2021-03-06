
define([
    'Magento_Ui/js/grid/massactions',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'jquery',
    'mage/translate',
    'uiRegistry'
], function (Massactions, uiAlert, _, $, $t, registry) {
    'use strict';

    return Massactions.extend({
        defaults: {
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            },
            listens: {
                massaction: 'onAction'
            }
        },

        onAction: function (data) {
            if (data.action === 'delete') {

                //reset selection
                var selectionColumn = registry.get('mst_finder_finder_product_listing.mst_finder_finder_product_listing.mst_finder_finder_import_product_columns.ids');
                selectionColumn.deselectPage();

                this.source.reload({
                    refresh: true
                });
            }
        },

        /**
         * Default action callback. Send selections data via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType, selections;

            if (action.isAjax) {
                itemsType = data.excludeMode ? 'excluded' : 'selected';
                selections = {};

                selections[itemsType] = data[itemsType];

                if (!selections[itemsType].length) {
                    selections[itemsType] = false;
                }

                _.extend(selections, data.params || {});

                this.request(action.url, selections).done(function (response) {
                    if (!response.error) {
                        uiAlert({
                            content: response.message
                        });

                        this.trigger('massaction', {
                            action: action.type,
                            data: selections
                        });
                    }
                }.bind(this));
            } else {
                this._super();
            }
        },

        /**
         * Send mass action ajax request
         *
         * @param {String} href
         * @param {Object} data
         */
        request: function (href, data) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href,
                data: data
            });

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });
                    }
                })
                .fail(function () {
                    uiAlert({
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        }
    });
});
