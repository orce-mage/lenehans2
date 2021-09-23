define([], function () {
    'use strict';

    return function (addressListView) {
        return addressListView.extend({
            initObservable: function () {
                this._super()
                    .observe('visible');

                return this;
            },
        });
    };
});
