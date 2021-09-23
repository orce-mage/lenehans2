define([
    'uiCollection'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            links: {
                input_value: '${ $.provider }:${ $.dataScope }.input_value',
                from_input_value: '${ $.provider }:${ $.dataScope }.from_input_value',
                to_input_value: '${ $.provider }:${ $.dataScope }.to_input_value'
            }
        },

        initObservable: function () {
            this._super().observe([
                'input_value',
                'from_input_value',
                'to_input_value'
            ]);

            return this;
        }
    });
});
