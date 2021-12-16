define([
    'jquery',
    'knockout',
    'underscore',
    'uiElement'
], function ($, ko, _, Element) {
    return Element.extend({
        defaults: {
            template: 'Mirasvit_Sorting/criterion/form/conditionCluster/cluster',

            imports: {
                conditions:                '${ $.provider }:data.conditions',
                sortBySource:              '${ $.provider }:sortBySource',
                sortByAttributeSource:     '${ $.provider }:sortByAttributeSource',
                sortByRankingFactorSource: '${ $.provider }:sortByRankingFactorSource',
                sortDirectionSource:       '${ $.provider }:sortDirectionSource'
            },

            exports: {
                conditions: '${ $.provider }:data.conditions'
            }
        },

        initialize: function () {
            this._super();

            _.bindAll(
                this,
                'addNode',
                'removeNode',
                'addFrame'
            );

            _.each(this.conditions, function (data) {
                var frame = this.newFrame(data);

                this.cluster.push(frame);
            }.bind(this));
        },

        initObservable: function () {
            this._super();

            this.cluster = ko.observableArray();

            return this;
        },

        newFrame: function (items) {
            var node = {
                items: ko.observableArray()
            };

            if (items) {
                _.each(items, function (item) {
                    node.items.push(
                        this.newNode(node, item)
                    );
                }.bind(this))
            }

            node.items.subscribe(this.sync.bind(this));

            return node;
        },

        addFrame: function () {
            var frame = this.newFrame();

            frame.items.push(
                this.newNode(frame)
            );

            this.cluster.push(frame);
        },

        newNode: function (node, data) {
            var item = {
                node:     node,
                expanded: ko.observable(0),

                sortBy:        ko.observable(data ? data.sortBy : 'ranking_factor'),
                attribute:     ko.observable(data ? data.attribute : ''),
                rankingFactor: ko.observable(data ? data.rankingFactor : ''),
                direction:     ko.observable(data ? data.direction : 'asc'),
                weight:        ko.observable(data ? data.weight : 50)
            };

            if (data) {
                _.each(data.conditions, function (data) {
                    this.addNode(item, data)
                }.bind(this))
            }

            item.sortBy.subscribe(this.sync.bind(this));
            item.attribute.subscribe(this.sync.bind(this));
            item.rankingFactor.subscribe(this.sync.bind(this));
            item.direction.subscribe(this.sync.bind(this));
            item.weight.subscribe(this.sync.bind(this));

            return item;
        },

        addNode: function (node, data) {
            node.items.push(this.newNode(node));
        },

        removeNode: function (item) {
            var node = item.node;
            node.items.remove(item);

            if (node.items().length === 0) {
                this.cluster.remove(node);
            }
        },

        sync: function () {
            var json = [];

            _.each(this.cluster(), function (frame) {
                var jsonFrame = [];
                var items = [];

                items = frame.items().filter(function (item) {
                    return item.sortBy() == 'attribute';
                });

                if (items.length != 0) {
                    _.each(frame.items(), function (item) {
                        if (item && item.sortBy() !== 'attribute') {
                            item.node.items.remove(item);
                        }
                    });
                }

                _.each(frame.items(), function (node) {
                    jsonFrame.push({
                        sortBy:        node.sortBy(),
                        attribute:     node.attribute(),
                        rankingFactor: node.rankingFactor(),
                        direction:     node.direction(),
                        weight:        node.weight()
                    });
                });

                json.push(jsonFrame)
            });

            this.set('conditions', json);
        }
    })
});
