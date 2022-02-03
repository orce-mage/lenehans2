/*global define*/
define([
    'jquery',
    'uiComponent',
    'mageUtils'
], function ($, Component, utils) {
    return Component.extend({
        defaults: {
            voteUrl: '/faq/index/vote',
            template: 'Amasty_Faq/rating/yesno',
            id: 0,
            positiveRating: 0,
            negativeRating: 0,
            isVoted: false,
            isPositiveVoted: false,
            votingBehavior: 'yesno',
            imports: {
                messageContainer: '${ $.name }.errors:container'
            }
        },

        initialize: function () {
            this._super();
            return this;
        },

        initObservable: function () {
            this._super()
                .observe({
                    isVoted: this.isVoted,
                    positiveRating: this.positiveRating,
                    negativeRating: this.negativeRating
                });

            return this;
        },

        vote: function (requestData, successCallback) {
            if (this.isVoted()) {
                return true;
            }

            $.ajax({
                url: this.voteUrl,
                data: utils.extend(requestData, {id: this.id, votingBehavior: this.votingBehavior, isAjax: true}),
                method: 'post',
                dataType: 'json',
                global: false,
                success: function (response) {
                    if (successCallback) {
                        successCallback(response);
                    }
                }.bind(this),
                error: function (response) {
                    this.onVoteError(response);
                }.bind(this)
            });
        },

        votePositive: function () {
            this.vote({positive: 1}, function() {
                this.isPositiveVoted = true;
                this.positiveRating(this.positiveRating() + 1);
                this.isVoted(true);
            }.bind(this));
        },

        voteNegative: function () {
            this.vote({positive: 0}, function() {
                this.isPositiveVoted = false;
                this.negativeRating(this.negativeRating() + 1);
                this.isVoted(true);
            }.bind(this));
        },

        isNegativeVotedQuestion: function () {
            return this.isVoted() && !this.isPositiveVoted;
        },

        isPositiveVotedQuestion: function () {
            return this.isVoted() && this.isPositiveVoted;
        },

        getPositiveRating: function () {
            return this.positiveRating();
        },

        getTotalRating: function () {
            return this.positiveRating() + Math.abs(this.negativeRating());
        },

        onVoteError: function (response) {
            this.messageContainer.addErrorMessage({ message: response.responseJSON.result.message });

            return this;
        }
    });
});
