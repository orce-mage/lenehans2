define([
    'jquery',
    'underscore',
    'Amasty_Faq/js/rating/yes-no-voting',
    'mageUtils',
    'jquery/jquery-storageapi'
], function ($, _, Voting, utils) {
    return Voting.extend({
        defaults: {
            storageKey: 'amfaq-average-rating-storage',
            hideZeroRating: false,
            votedStarNumber: 0,
            votingBehavior: 'average',
            average: 0,
            total: 0
        },

        initialize: function () {
            this._super();

            var votedQuestions = $.localStorage.get(this.storageKey),
                questionId = this.id;

            if (_.isObject(votedQuestions) && !_.isUndefined(votedQuestions[questionId])) {
                this.votedStarNumber(votedQuestions[questionId]);
            }

            this.isVoted(false);
            this.votedStarNumber.subscribe(this.handleVoting.bind(this));

            return this;
        },

        initObservable: function () {
            this._super()
                .observe('votedStarNumber average');

            return this;
        },

        handleVoting: function (starNumber) {
            if (starNumber) {
                var requestData = {},
                    votedQuestions = $.localStorage.get(this.storageKey),
                    questionId = this.id;
                if (_.isObject(votedQuestions) && !_.isUndefined(votedQuestions[questionId])) {
                    utils.extend(requestData, {revote: true, oldVote: votedQuestions[questionId]});
                }
                utils.extend(requestData, {starNumber: starNumber})
                this.vote(requestData, function () {
                    var votedQuestions = $.localStorage.get(this.storageKey),
                        questionId = this.id;

                    if (_.isNull(votedQuestions)) {
                        votedQuestions = {};
                    }

                    this.recalculateAverage(starNumber);
                    votedQuestions[questionId] = starNumber;
                    $.localStorage.set(this.storageKey, votedQuestions);
                    this.isVoted(false);
                }.bind(this));
            }
        },

        recalculateAverage: function (voteValue) {
            var total = this.total,
                average = parseFloat(this.average()),
                newAverage = (average * total + parseInt(voteValue)) / (total + 1),
                votedQuestions = $.localStorage.get(this.storageKey),
                questionId = this.id;

            if (_.isObject(votedQuestions) && !_.isUndefined(votedQuestions[questionId])) {
                var oldValue = votedQuestions[questionId],
                    newAverage = total > 1
                        ? (average * total - parseInt(oldValue) + parseInt(voteValue)) / total
                        : parseInt(voteValue);
            } else {
                this.total++;
            }

            this.average(newAverage);
        },
    });
});
