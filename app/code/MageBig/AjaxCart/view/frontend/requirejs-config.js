var config = {
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'MageBig_AjaxCart/js/catalog-add-to-cart': true
            },
            'Magento_Checkout/js/sidebar': {
                'MageBig_AjaxCart/js/sidebar': true
            }
        }
    }
};
