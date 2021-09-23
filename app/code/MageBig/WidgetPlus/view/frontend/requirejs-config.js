var config = {
    map: {
        "*": {
            'owlWidget': 'MageBig_WidgetPlus/js/owl.carousel-set',
            'owlCarousel': 'MageBig_WidgetPlus/js/owl.carousel'
        }
    },
    shim: {
        'owlWidget': {
            deps: ['jquery', 'owlCarousel']
        },
        'owlCarousel': {
            deps: ['jquery']
        }
    }
};
