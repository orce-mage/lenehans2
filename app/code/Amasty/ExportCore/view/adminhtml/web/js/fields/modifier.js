define([
    'uiCollection',
    'underscore',
    'uiLayout',
    'mageUtils'
], function (Collection, _, layout, utils) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Amasty_ExportCore/fields/modifier',
            links: {
                select_value: '${ $.provider }:${ $.dataScope }.select_value',
                eavEntityType: '${ $.provider }:${ $.dataScope }.eavEntityType',
                optionSource: '${ $.provider }:${ $.dataScope }.optionSource'
            },
            listens: {
                select_value: 'getModifierTypeSelected',
                selectedOption: 'createModifierField'
            },
            selectedType: null,
            selectedOption: {},
            options: [],
            selectedActions: [],
            modifierValue: {}
        },

        initialize: function () {
            this._super();

            this.renderFields();

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'eavEntityType',
                'select_value',
                'code',
                'selectedType',
                'selectedOption',
                'optionSource'
            ]);

            return this;
        },

        renderFields: function () {
            var name = this.name + '.value',
                value = this.modifierValue || {},
                componentData = utils.extend(value, {
                    'parentName': this.name,
                    'provider': this.provider,
                    'dataScope': this.dataScope + '.value',
                    'prefer': 'toggle',
                    'parentScope': this.dataScope,
                    'source': this.source,
                    'options': this.options,
                    'name': name,
                    'component': this.childComponent,
                    'template': this.childTemplate
                });

            layout([componentData]);
            this.insertChild(name);
        },

        getModifierTypeSelected: function (value) {
            var option;

            this.options.forEach(function (optgroup) {
                option = this.findValue(optgroup, value);

                if (option) {
                    this.selectedType(optgroup.type);
                    this.selectedOption(option);
                }
            }.bind(this));
        },

        createModifierField: function (option) {
            _.each(this.elems(), function (element) {
                element.destroy();
            }, this);

            this.childTemplate = this.modifierConfig[option.value].childTemplate || null;
            this.childComponent = this.modifierConfig[option.value].childComponent || null;
            this.eavEntityType(this.selectedOption().eavEntityType);
            this.optionSource(this.selectedOption().optionSource);

            if (this.childComponent && this.childTemplate) {
                this.renderFields();
            }
        },

        findValue: function (optgroup, value) {
            return optgroup.value.find(function (item) {
                return item.value === value;
            });
        },

        remove: function () {
            this.source.remove(this.dataScope);
            this.destroy();
        },

        setDefaultValue: function () {
            this.select_value(this.selectValue);
        }
    });
});
