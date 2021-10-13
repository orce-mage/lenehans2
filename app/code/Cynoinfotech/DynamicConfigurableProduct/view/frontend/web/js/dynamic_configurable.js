/**
 * CynoInfotech Team
 * Cynoinfotech_DynamicConfigurableProduct
 */
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';
    return function(targetModule){

        var reloadPrice = targetModule.prototype._reloadPrice;
		
        targetModule.prototype.configurableSku = $('div.product-info-main .sku .value').html();
		targetModule.prototype.configurableName = $('div.product-info-main .page-title .base').html();
		targetModule.prototype.configurableShortDesc = $('div.product-info-main .overview .value').html();
		targetModule.prototype.configurableDesc = $('div.info .description .value').html();

        var reloadPriceWrapper = wrapper.wrap(reloadPrice, function(original){
            //do extra stuff
            var simpleSku = this.configurableSku;
			var simpleName = this.configurableName;
			var simpleShortDesc = this.configurableShortDesc;
			var simpleDesc = this.configurableDesc;			
			//console.log(this.options.spConfig);
            if(this.simpleProduct){
                simpleSku = this.options.spConfig.skus[this.simpleProduct];
				simpleName = this.options.spConfig.names[this.simpleProduct];
				simpleShortDesc = this.options.spConfig.shortdescriptions[this.simpleProduct];
				simpleDesc = this.options.spConfig.descriptions[this.simpleProduct];
            }

            $('div.product-info-main .sku .value').html(simpleSku);
			$('div.product-info-main .page-title .base').html(simpleName);
			$('div.product-info-main .overview .value').html(simpleShortDesc);
			$('div.info .description .value').html(simpleDesc);

            //return original value
            return original();
        });

        targetModule.prototype._reloadPrice = reloadPriceWrapper;
        return targetModule;
    };
});