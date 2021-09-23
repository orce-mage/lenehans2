/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    config: {
        mixins: {
            'Magento_Theme/js/view/messages': {
                'Magento_Theme/js/view/messages-fix': true
            },
            'mage/validation': {
                'Magento_Theme/js/view/validation-mixin': true
            },
            'Magento_Ui/js/form/element/abstract': {
                'Magento_Theme/js/view/validation-ui': true
            }
        }
    },
    map: {
        '*': {
            'lazysizes': 'Magento_Theme/js/lazysizes-umd'
        }
    }
};
