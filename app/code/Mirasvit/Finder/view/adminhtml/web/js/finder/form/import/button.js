define([
    'Magento_Ui/js/form/components/button',
    'underscore'
], function (Button, _) {
    'use strict';

    return Button.extend({
        defaults: {
            finder_id: null
        },

        applyAction: function (action) {
            if (action.params && action.params[0]) {
                action.params[0]['finder_id'] = this.finder_id;
            } else {
                action.params = [{
                    'finder_id': this.finder_id
                }];
            }

            this._super();
        }
    });
});
