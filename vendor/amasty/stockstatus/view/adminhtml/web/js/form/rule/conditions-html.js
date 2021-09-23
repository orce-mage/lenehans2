define([
    'jquery',
    'Magento_Ui/js/form/components/html'
], function ($, html) {
    'use strict';

    return html.extend({
        initChangeListener: function () {
            var productGrid = this.containers[0].getChild('products_grid'),
                observer = new MutationObserver(subscriber);

            function subscriber(mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'childList') {
                        productGrid.visible(false);
                    }
                });
            }

            observer.observe($('.rule-tree')[0], {
                childList: true,
                subtree: true
            });
        }
    });
});
