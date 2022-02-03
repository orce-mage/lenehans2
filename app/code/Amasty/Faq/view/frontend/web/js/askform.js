define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/view/customer',
    'mage/validation'
], function ($, Component, customerData) {
    return Component.extend({
        initialize: function (config) {
            this.faq = customerData.get('faq');
            this._super();
        },
        saveData: function() {
            customerData.set('faq', this.faq());
        },
        initObservable: function () {
            this._super()
                .observe({
                    title: this.faq().title,
                    email: this.faq().email,
                    name: this.faq().name,
                    isEmailVisible: null,
                    isNameVisible: null,
                    isNotificationVisible: null,
                    isNotificationChecked: null
                });

            this.title.subscribe(function (value) {
                this.faq().title = value;
                this.saveData();
            }.bind(this));
            this.email.subscribe(function (value) {
                this.faq().email = value;
                this.saveData();
            }.bind(this));
            this.name.subscribe(function (value) {
                this.faq().name = value;
                this.saveData();
            }.bind(this));
            this.faq.subscribe(function (value) {
                if (this.name() != value.name) {
                    this.name(value.name)
                }
                if (this.email() != value.email) {
                    this.email(value.email)
                }
                if (this.title() != value.title) {
                    this.title(value.title)
                }
            }.bind(this));

            return this;
        },
        toggleEmail: function(element) {
            this.isEmailVisible(element.checked);
            this.isNotificationChecked(element.checked);
        },
        toggleNameAndNotification: function(element) {
            this.isNameVisible(element.checked);
            this.isNotificationVisible(element.checked);
            this.isEmailVisible(element.checked && this.isNotificationChecked());
        },
        formSubmit: function (form) {
            if (!$(form).validation() || !$(form).validation('isValid')) {
                return false;
            }
            $(form).find(':input').attr('readonly', true);
            $(form).find('button').attr('disabled', true);

            return true;
        }
    });
});
