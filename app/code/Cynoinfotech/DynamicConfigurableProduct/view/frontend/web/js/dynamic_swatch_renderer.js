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
		

        var updatePrice = targetModule.prototype._UpdatePrice;
		
        targetModule.prototype.configurableSku = $('div.product-info-main .sku .value').html();
		targetModule.prototype.configurableName = $('div.product-info-main .page-title .base').html();
		targetModule.prototype.configurableShortDesc = $('div.product-info-main .overview .value').html();
		targetModule.prototype.configurableDesc = $('div.info .description .value').html();		
		
        var updatePriceWrapper = wrapper.wrap(updatePrice, function(original){

        var allSelected = true;	
		console.log('DynamicConfigurableProduct');
					
            for(var i = 0; i<this.options.jsonConfig.attributes.length;i++){
				
				
                if (!$('div.product-info-main .product-options-wrapper .swatch-attribute.' + this.options.jsonConfig.attributes[i].code).attr('option-selected')){
                   // allSelected = false;
                }
            }

            var simpleSku = this.configurableSku;
			var simpleName = this.configurableName;
			var simpleShortDesc = this.configurableShortDesc;
			var simpleDesc = this.configurableDesc;
			
            if (this._CalcProducts().slice().shift()>0){
				
                var products = this._CalcProducts();
				
                simpleSku = this.options.jsonConfig.skus[products.slice().shift()];
				simpleName = this.options.jsonConfig.names[products.slice().shift()];
				simpleShortDesc = this.options.jsonConfig.shortdescriptions[products.slice().shift()];
				simpleDesc = this.options.jsonConfig.descriptions[products.slice().shift()];
				
            }
				
            $('div.product-info-main .sku .value').html(simpleSku);
			$('div.product-info-main .page-title .base').html(simpleName);
			$('div.product-info-main .overview .value').html(simpleShortDesc);
			$('div.info .description .value').html(simpleDesc);

            //return original value
            return original();
        });

        targetModule.prototype._UpdatePrice = updatePriceWrapper;
        return targetModule;
    };
});