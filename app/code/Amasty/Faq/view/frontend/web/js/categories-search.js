define([
    'jquery',
    'uiComponent',
    'mage/translate'
], function ($, Component) {
    return Component.extend({
        defaults: {
            categories: [],
            limitCategories: null,
            text: {
                seeResults: $.mage.__('See all results'),
                hideResults: $.mage.__('Hide results')
            }
        },

        initialize: function () {
            this._super();

            this.limitedCategories = this.categories.slice(0, this.limitCategories);
            this.showedCategories(this.limitedCategories);

            return this;
        },

        initObservable: function () {
            this._super()
                .observe({
                    showButtonSeeAll: this.categories.length > this.limitCategories,
                    questionsCount: this.categories.length - this.limitCategories,
                    isShowAllResults: false,
                    showedCategories: []
                });

            return this;
        },

        toggleAllResults: function () {
            var categories;

            this.isShowAllResults(!this.isShowAllResults());
            categories = this.isShowAllResults() ? this.categories : this.limitedCategories;
            this.showedCategories(categories);
        }
    });
});
