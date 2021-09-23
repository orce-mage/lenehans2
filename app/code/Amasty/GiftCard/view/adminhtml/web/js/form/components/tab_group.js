define([
    'underscore'
], function (_) {
    'use strict';

    return function (TabGroup) {
        return TabGroup.extend({
            validate: function (elem) {
                var result  = elem.delegate('validate'),
                    invalid;

                invalid = _.find(result, function (item) {
                    return typeof item !== 'undefined' && !item.valid;
                });

                if (invalid) {
                    elem.activate();
                    invalid.target.focused(true);
                }

                return invalid;
            },
        });
    }
});
