define([
    'jquery',
    'mageUtils',
    'Magento_Ui/js/grid/data-storage'
], function ($, utils, DataStorage) {
    return DataStorage.extend({
        defaults: {
            cacheRequests: false,
            requestConfig: {
                method: 'POST'
            }
        }
    })
});