define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/ui-select'
], function ($, _, registry, UiSelect) {
    'use strict';

    return UiSelect.extend({
        defaults: {
            previousGroup:     null,
            groupsConfig:      {},
            valuesMap:         {},
            indexesMap:        {},
            filterPlaceholder: 'ns = ${ $.ns }, parentScope = ${ $.parentScope }'
        },

        initialize: function () {
            return this
                ._super()
                .initMapping()
                .updateComponents(this.initialValue, true);
        },

        initMapping: function () {
            _.each(this.groupsConfig, function (groupData, group) {
                _.each(groupData.values, function (value) {
                    this.valuesMap[value] = group;
                }, this);

                _.each(groupData.indexes, function (index) {
                    if (!this.indexesMap[index]) {
                        this.indexesMap[index] = [];
                    }

                    this.indexesMap[index].push(group);
                }, this);
            }, this);

            return this;
        },

        onUpdate: function (currentValue) {
            this.updateComponents(currentValue);

            return this._super();
        },

        updateComponents: function (currentValue, isInitialization) {
            var currentGroup = this.valuesMap[currentValue];

            if (currentGroup !== this.previousGroup) {
                _.each(this.indexesMap, function (groups, index) {
                    var template = this.filterPlaceholder + ', index = ' + index,
                        visible  = groups.indexOf(currentGroup) !== -1,
                        component;

                    switch (index) {
                        case 'values':
                            template = 'ns=' + this.ns +
                                ', dataScope=' + this.parentScope +
                                ', index=' + index;
                            break;
                    }

                    /*eslint-disable max-depth */
                    if (isInitialization) {
                        registry.async(template)(
                            function (currentComponent) {
                                currentComponent.visible(visible);
                            }
                        );
                    } else {
                        component = registry.get(template);

                        if (component) {
                            component.visible(visible);
                        }
                    }
                }, this);

                this.previousGroup = currentGroup;
            }

            return this;
        }
    });
});
