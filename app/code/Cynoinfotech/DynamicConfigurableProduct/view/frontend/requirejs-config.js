/**
 * CynoInfotech Team
 * Cynoinfotech_DynamicConfigurableProduct
 */
 
if(typeof dynamicConfigurableProductEnable != 'undefined' && dynamicConfigurableProductEnable =='1') {

	var config = {
		config: {
			mixins: {
				'Magento_ConfigurableProduct/js/configurable': {
					'Cynoinfotech_DynamicConfigurableProduct/js/dynamic_configurable': true
				},
				'Magento_Swatches/js/swatch-renderer': {
					'Cynoinfotech_DynamicConfigurableProduct/js/dynamic_swatch_renderer': true
				}
			}
		}
	};
}