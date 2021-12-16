define([
    'jquery',
    'knockout',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/grid/provider'
], function ($, ko, _, Registry, Provider) {
    return Provider.extend({
        defaults: {
            storageConfig: {
                component: 'Mirasvit_Sorting/js/preview/data-storage'
            }
        },
        
        reload: function (options) {
            var type = Registry.get('sorting_criterion_form.sorting_criterion_form_data_source') ? 'criterion' : 'factor';
            
            if (type === 'criterion') {
                var source = Registry.get('sorting_criterion_form.sorting_criterion_form_data_source');
                this.params.criterion = source.data;
            } else {
                var source = Registry.get('sorting_rankingFactor_form.sorting_rankingFactor_form_data_source');
                this.params.rankingFactor = source.data;
            }
            
            return this._super(options);
        }
    })
});