var config = {
    map: {
        "*": {
            amfancybox: 'Amasty_Storelocator/vendor/fancybox/jquery.fancybox.min',
            amLocator: 'Amasty_Storelocator/js/main',
            chosen: 'Amasty_Storelocator/vendor/chosen/chosen.min'
        }
    },
    shim: {
        'Amasty_Storelocator/vendor/fancybox/jquery.fancybox.min': ['jquery'],
        'Amasty_Storelocator/vendor/chosen/chosen.min': [ 'jquery' ],
        'Amasty_Storelocator/js/main': [ 'jquery-ui-modules/slider' ]
    }
};
