var config = {
    map: {
        "*": {
            'magnificpopup': 'MageBig_MbLib/js/jquery.magnific-popup',
            'awArMagnificPopup': 'MageBig_MbLib/js/jquery.magnific-popup',
            'mageplaza/core/jquery/popup': 'MageBig_MbLib/js/jquery.magnific-popup',
            'magnific': 'MageBig_MbLib/js/jquery.magnific-popup',
            'nanoscroller': 'MageBig_MbLib/js/jquery.nanoscroller'
        }
    },
    shim: {
        'magnificpopup': {
            deps: ['jquery']
        },
        'nanoscroller': {
            deps: ['jquery']
        }
    }
};
