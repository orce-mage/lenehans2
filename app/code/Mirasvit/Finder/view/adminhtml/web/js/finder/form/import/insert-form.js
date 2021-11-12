define([
    'Magento_Ui/js/form/components/insert-form',
    'Magento_Ui/js/modal/alert',
], function (Insert, uiAlert) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                finderListing: '${ $.finderListingProvider }',
                finderModal: '${ $.finderModalProvider }'
            }
        },

        onResponse: function (responseData) {
            if (!responseData.error) {
                this.finderModal().closeModal();

                uiAlert({
                    content: responseData.message
                });

                this.finderListing().reload({
                    refresh: true
                });

            }
        }
    });
});
