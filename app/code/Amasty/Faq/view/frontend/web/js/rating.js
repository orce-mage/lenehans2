define([
    'jquery',
    'uiComponent',
    'uiLayout',
    'mageUtils',
], function ($, Component, layout, utils) {
    return Component.extend({
        defaults: {
            dataUrl: '/faq/index/rating',
            voteUrl: '/faq/index/vote',
            itemsTemplate: 'Amasty_Faq/rating/yesno',
            votingBehavior: 'yesno',
            items: [],
        },

        initialize: function () {
            this._super().getRatings();
        },

        initObservable: function () {
            this._super();

            return this;
        },

        getRatings: function () {
            $.ajax({
                url: this.dataUrl,
                data: {items: this.items, isAjax: true},
                method: 'post',
                global: false,
                dataType: 'json',
                success: function (response) {
                    this.createItems(response);
                }.bind(this)
            });
        },

        createItems: function (items) {
            for (var item in items) {
                if (items.hasOwnProperty(item)) {
                    layout([this.createComponent(items[item])]);
                }
            }
        },

        createComponent: function (item) {
            var rendererTemplate,
                rendererComponent,
                templateData;

            templateData = {
                parentName: this.name,
                name: 'faq-rating-item-' + item.id
            };
            rendererTemplate = {
                template: this.itemsTemplate,
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                displayArea: 'frontend',
                voteUrl: this.voteUrl,
                hideZeroRating: this.hideZeroRating,
                component: 'Amasty_Faq/js/rating/yes-no-voting',
                children: {
                    errors: {
                        component: "Amasty_Faq/js/rating/messages/voting-messages",
                        displayArea: "messages"
                    }
                }
            };

            if (this.votingBehavior === 'average') {
                rendererTemplate.component = 'Amasty_Faq/js/rating/average';
                rendererTemplate.average = parseFloat(item.average);
                rendererTemplate.total = parseInt(item.total);
            }

            rendererComponent = utils.template(rendererTemplate, templateData);
            utils.extend(rendererComponent, {
                id: item.id,
                positiveRating: parseInt(item.positiveRating),
                negativeRating: parseInt(item.negativeRating),
                isVoted: item.isVoted,
                isPositiveVoted: item.isPositiveVoted,
                votingBehavior: item.votingBehavior
            });

            return rendererComponent;
        },
    });
});
