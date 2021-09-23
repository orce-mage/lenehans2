define([
    'underscore',
    'uiRegistry'
], function (_, uiRegistry) {
    return function (rangesComponentName) {
        _.each(uiRegistry.get(rangesComponentName).elems(), function (range) {
            var stockstatusField = range.getChild('status_id');
            if (typeof stockstatusField !== 'undefined' && typeof range.getChild('qty_from') !== 'undefined' && typeof range.getChild('qty_to') !== 'undefined') {
                delete stockstatusField.validation['required-entry']; // required validation on save action
                stockstatusField.validate();
                stockstatusField.validation['required-entry'] = true;
            }
        });
    }
});
