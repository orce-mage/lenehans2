define([
    'uiCollection',
    'underscore',
    'uiLayout',
    'mageUtils'
], function (Collection, _, layout, utils) {
    return Collection.extend({
        defaults: {
            visible: true,
            imports: {
                'fieldValue' : '${ $.parentName}.field:value'
            },
            listens: {
                fieldValue: 'processField'
            },
            modules: {
                recordComponent: '${ $.parentName }'
            }
        },
        initObservable: function () {
            this._super().observe(['visible', 'fieldValue']);

            return this;
        },
        processField: function () {
            if (!_.isUndefined(this.elems()[0])) {
                var dataScope = this.elems()[0].dataScope;
                this.elems()[0].destroy();
                this.source.set(dataScope, null);
            }
            var fieldData = this.recordComponent().parentComponent().filterConfig[this.fieldValue()] || {};
            var componentData = fieldData.config,
                name = this.name + '.value';
            this.elems([]);
            componentData = utils.extend(componentData, {
                'parentName': this.name,
                'provider': this.provider,
                'dataScope': this.dataScope + '.value',
                'parentScope': this.dataScope,
                'source': this.source,
                'name': name
            });
            layout([componentData]);
            this.insertChild(name);
        }
    });
});
