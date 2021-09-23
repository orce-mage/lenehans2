var config = {
    config: {
        mixins: {
            'Magento_Wishlist/js/add-to-wishlist': {
                'MageBig_Ajaxwishlist/js/add-to-wishlist': true
            }
        }
    },
    map: {
        '*': {
            'magebig/ajaxwishlist'  : 'MageBig_Ajaxwishlist/js/ajax-wishlist'
        }
    }
};
