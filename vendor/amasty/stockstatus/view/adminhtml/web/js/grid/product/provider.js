define([
    'jquery',
    'Magento_Ui/js/grid/provider'
], function ($, provider) {
    'use strict';

    return provider.extend({
        reload: function (options) {
            var stockstatusRuleCondition = $('[data-form-part="amasty_stockstatus_rule_form"]').serialize();

            if (typeof this.params.filters === 'undefined') {
                this.params.filters = {};
            }

            $.each(this.stores, function (index, storeId) {
                stockstatusRuleCondition += '&stores[]=' + storeId;
            })

            this.params.filters.stockstatus_rule_condition = stockstatusRuleCondition;

            this._super({'refresh': true});
        }
    });
});
