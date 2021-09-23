define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
    'Magento_Ui/js/lib/validation/validator',
    'uiRegistry',
    'mage/translate'
], function (_, DynamicRows, validator, uiRegistry, $t) {
    return DynamicRows.extend({
        defaults: {
            mappingSettings: {
                enabled: false,
                distinct: false
            },
            update: true,
            map: {
                'id': 'id'
            },
            identificationProperty: 'id',
            identificationDRProperty: 'id',
            rangesComponentName: ''
        },

        initialize: function () {
            this._super();

            validator.addRule(
                'amasty-stockstatus-unique-range',
                function (value, params, currentRangeName) {
                    var currentRange = uiRegistry.get(currentRangeName),
                        retrieveRangesComponentPath = function (currentRangeName) {
                            var pathParts = currentRangeName.split('.');

                            pathParts.splice(pathParts.length - 2, 2);
                            pathParts.push('ranges');

                            return pathParts.join('.');
                        },
                        ifSourcesDuplicate = function (currentRange, range) {
                            return typeof currentRange.getChild('source_code') === 'undefined'
                                || currentRange.getChild('source_code').value() === range.getChild('source_code').value();
                        },
                        result = true;

                    _.each(uiRegistry.get(retrieveRangesComponentPath(currentRangeName)).elems(), function (range) {
                        if (currentRange.recordId !== range.recordId
                            && currentRange.getChild('qty_to').value() === range.getChild('qty_to').value()
                            && currentRange.getChild('qty_from').value() === range.getChild('qty_from').value()
                            && ifSourcesDuplicate(currentRange, range)
                        ) {
                            result = false;
                            return false;
                        }
                    });

                    return result;
                }.bind(this),
                $t('You have already set a status for specified qty range. Please remove an unnecessary one.')
            );
        },

        hide: function () {
            this.visible(false);
        },

        show: function () {
            this.visible(true);
        }
    });
});
