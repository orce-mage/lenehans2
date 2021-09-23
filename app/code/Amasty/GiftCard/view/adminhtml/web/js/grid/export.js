define([
    'jquery',
    'Magento_Ui/js/grid/export'
], function ($, Export) {
    return Export.extend({
        defaults: {
            codePool: ''
        },
        getParams: function () {
            var result = this._super();
            result['pool_id'] = this.codePool;

            return result;
        }
    })
});
