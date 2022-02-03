require([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    var saveProductUrl = function () {
        var faqProduct = customerData.get('faq_product');
        faqProduct().url = window.location.href;
        customerData.set('faq_product', faqProduct());
    }

    if (typeof customerData.getInitCustomerData === "function") {
        customerData.getInitCustomerData().done(function () {
            saveProductUrl();
        }.bind(this));
    } else {
        saveProductUrl();
    }
});
