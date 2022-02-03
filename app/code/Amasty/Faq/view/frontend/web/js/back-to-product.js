/*global window*/
define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/storage'
], function ($, Component, customerData) {
    return Component.extend({
        defaults: {
            getDataUrl: '',
            template: 'Amasty_Faq/backto'
        },
        initialize: function (config) {
            this.faqProd = customerData.get('faq_product');

            this._super();

            if (typeof customerData.getInitCustomerData === "function") {
                customerData.getInitCustomerData().done(function () {
                    this.applyShowButton();
                }.bind(this));
            } else {
                this.applyShowButton();
            }
        },
        applyShowButton: function () {
            if (this.faqProd().url) {
                this.showButton(true);
            }
        },
        initObservable: function () {
            this._super()
                .observe({
                    showButton: false
                });

            this.faqProd.subscribe(function(product){
                this.showButton(!!product.url);
            }.bind(this));

            return this;
        },
        redirectToProduct: function () {
            window.location = this.faqProd().url;
        }
    });
});
