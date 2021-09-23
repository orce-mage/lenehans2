define(
    [
    'jquery',
    'Magento_Search/form-mini'
    ], function ($) {
        'use strict';

        $.widget(
            'searchanise.quickSearch', $.mage.quickSearch, {
                options: {
                    minSearchLength: 1000,
                },

                /** @inheritdoc */
                _create: function () {
                    // Nothing to disable autocomplete
                }
            }
        );

        return $.searchanise.quickSearch;
    }
);
