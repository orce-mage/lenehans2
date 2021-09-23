define([
    'jquery',
    'uiCollection',
    'uiLayout',
    'mageUtils',
    'underscore'
], function ($, Collection, layout, utils, _) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Amasty_ExportCore/fields/field',
            links: {
                input_value: '${ $.provider }:${ $.dataScope }.input_value',
                output_value: '${ $.provider }:${ $.dataScope }.output_value',
                code: '${ $.provider }:${ $.dataScope }.code',
                sortOrder: '${ $.provider }:${ $.dataScope }.sortOrder',
                options: '${ $.provider }:${ $.dataScope }.options'
            },
            imports: {
                modifierConfig: '${ $.parentName }:modifierConfig'
            },
            modules: {
                parent: '${ $.parentName }',
                dataProvider: '${ $.provider }'
            },
            modifierIndex: 0,
            selected_actions: []
        },

        initialize: function () {
            this._super();

            this.renderDefaultModifies();

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'input_value',
                'output_value',
                'code',
                'sortOrder'
            ]);

            return this;
        },

        remove: function () {
            if (this.parent().isSorted) {
                this.parent().reRenderAndRemove(this.code());
            } else {
                this.source.remove(this.dataScope);
                this.destroy();
                this.parent().checkFieldsState();
            }
        },

        renderDefaultModifies: function () {
            _.each(this.modifier, function (modifier) {
                if (modifier) {
                    this.addModifier(this.name, modifier.select_value, modifier.value);
                }
            }, this);
        },

        addModifier: function (name, value, modifierValue) {
            var fieldData = this.modifierConfig[value] || {},
                item = utils.extend(fieldData, {
                    'name': name + '.modifier.' + this.modifierIndex,
                    'component': 'Amasty_ExportCore/js/fields/modifier',
                    'provider': this.provider,
                    'selectValue': value || '',
                    'options': this.options,
                    'modifierValue': modifierValue || {},
                    'modifierConfig': this.modifierConfig,
                    'dataScope': this.dataScope + '.modifier.' + this.modifierIndex
                });

            layout([item]);
            this.insertChild(item.name);
            this.modifierIndex += 1;
        }
    });
});
