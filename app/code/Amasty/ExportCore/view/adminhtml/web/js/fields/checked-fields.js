define([
    'uiCollection',
    'jquery',
    'ko',
    'underscore',
    'uiLayout',
    'mageUtils'
], function (Collection, $, ko, _, layout, utils) {
    'use strict';

    return Collection.extend({
        defaults: {
            checkedFields: [],
            staticFields: [],
            selected: [],
            fieldsContainerSelect: '[data-amexportcore-js="fields"]',
            fieldsSelect: '[data-amexportcore-js="field"]',
            inputCode: '[data-amexportcore-js="code"]',
            positions: [],
            selectFieldsPath: null,
            isShowStatic: false,
            isShowFields: false,
            elemIndex: 0,
            staticIndex: 0,
            isShowDeleteBtn: false,
            isSorted: false,
            lastSortableData: [],
            sortOrders: {},
            templates: {
                staticField: 'Amasty_ExportCore/fields/static-field',
                field: 'Amasty_ExportCore/fields/field'
            },
            listens: {
                newCheckedField: 'addCheckedFields',
                fieldsToRemove: 'removeFields',
                elems: 'toggleBtnDelete'
            },
            exports: {
                checkedFields: '${ $.selectFieldsPath }:checkedFields',
                newCheckedField: '${ $.selectFieldsPath }:newCheckedField',
                isShowDeleteBtn: '${ $.deleteBtnPath }:visible'
            },
            imports: {
                fields: '${ $.selectFieldsPath }:fields',
                selected: '${ $.selectFieldsPath }:selected',
                newCheckedField: '${ $.selectFieldsPath }:newCheckedField',
                fieldsToRemove: '${ $.selectFieldsPath }:fieldsToRemove',
                checkedFields: '${ $.provider }:${ $.dataScope }',
                staticFields: '${ $.provider }:${ $.parentScope }.static_fields'
            },
            modules: {
                selectFields: '${ $.selectFieldsPath }',
                deleteBtn: '${ $.deleteBtnPath }',
                dataProvider: '${ $.provider }'
            }
        },

        initObservable: function () {
            this._super().observe([
                'checkedFields',
                'selected',
                'staticFields',
                'newCheckedField',
                'fieldsToRemove',
                'isShowStatic',
                'isShowFields',
                'isShowDeleteBtn'
            ]);

            return this;
        },

        toggleBtnDelete: function () {
            this.isShowDeleteBtn(!!this.elems().length);
        },

        removeFields: function () {
            if (this.fieldsToRemove().length) {
                this.elems.each(function (elem) {
                    if (_.contains(this.fieldsToRemove(), elem.code())) {
                        elem.remove();
                    }
                }.bind(this));

                this.fieldsToRemove([]);
            }
        },

        removeAllItems: function () {
            this.elems.each(function (elem) {
                elem.source.remove(elem.dataScope);
                elem.destroy();
            });

            this.isShowFields(false);
            this.isShowStatic(false);
        },

        renderDefaultFields: function () {
            if (this.isDefaultRendered) {
                return;
            }

            _.each(this.checkedFields(), function (item) {
                this.initFields(item);
            }.bind(this));

            _.each(this.staticFields(), function (item) {
                this.initStaticField(item);
            }.bind(this));

            this.isDefaultRendered = true;
        },

        initSortable: function (element) {
            var $sortableElement = $(element);

            $sortableElement.sortable({
                cursor: 'move',
                axis: 'y',
                items: 'tr.amexportcore-row.-dnd',
                distance: 5,
                forcePlaceholderSize: true,
                opacity: 0.7,
                placeholder: 'sortable-placeholder',
                update: this.sortableUpdate.bind(this, $sortableElement)
            });
        },

        sortableUpdate: function ($sortableElement) {
            var self = this;

            _.each($sortableElement.children(), function (elem, index) {
                self.sortOrders[$(elem).find(self.inputCode).val()] = index;
            });

            this.elems.each(function (elem) {
                elem.sortOrder(self.sortOrders[elem.code()]);
            });

            this.isSorted = true;
        },

        reRenderAndRemove: function (code) {
            this.lastSortableData = JSON.parse(
                JSON.stringify(this.getProviderData(this.dataScope, this.dataProvider()))
            );

            this.elems.each(function (elem) {
                if (!elem.isStatic) {
                    elem.source.remove(elem.dataScope);
                    elem.destroy();
                }
            });

            if (_.isObject(this.lastSortableData) && !_.isArray(this.lastSortableData)) {
                this.lastSortableData = Object.values(this.lastSortableData);
            }

            this.lastSortableData = this.lastSortableData.filter(function (item) {
                if (_.isNull(item)) {
                    return false;
                }

                return item.code !== code;
            });

            this.lastSortableData = this.lastSortableData.sort(function (item1, item2) {
                return this.sortOrders[item1.code] - this.sortOrders[item2.code];
            }.bind(this));

            this.elemIndex = 0;
            this.isSorted = false;
            this.newCheckedField(this.lastSortableData);
        },

        getProviderData: function (dataScope, data) {
            var scopeArray = dataScope.split('.'),
                key = scopeArray.shift();

            if (scopeArray.length) {
                return this.getProviderData(scopeArray.join('.'), data[key]);
            }

            return data[key];
        },

        getNameField: function () {
            return this.name + '.field-' + this.elemIndex;
        },

        getStaticFieldName: function () {
            return this.name + '.static_field-' + this.staticIndex;
        },

        initFields: function (item) {
            item = this.createField(item, this.elemIndex, this.dataScope, this.getNameField());
            layout([item]);
            this.insertChild(item.name, this.elemIndex);
            this.elemIndex += 1;
        },

        createField: function (data, index, dataScope, name) {
            return utils.extend(data, {
                'name': name,
                'component': 'Amasty_ExportCore/js/fields/field',
                'provider': this.provider,
                'dataScope': dataScope + '.' + index,
                'sortOrder': index,
                'template': data.isStatic ? this.templates.staticField : this.templates.field
            });
        },

        initStaticField: function (item) {
            item = this.createField(
                { isStatic: true }, this.staticIndex, this.getStaticDataScope(), this.getStaticFieldName()
            );
            layout([item]);
            this.insertChild(item.name);
            this.staticIndex += 1;
            this.isShowStatic(true);
        },

        getStaticDataScope: function () {
            var path = this.dataScope.split('.');

            return path.slice(0, path.length - 1).join('.') + '.static_fields';
        },

        addCheckedFields: function () {
            if (this.newCheckedField().length) {
                this.newCheckedField().forEach(function (item) {
                    if (!_.isUndefined(item) && item) {
                        this.initFields(item);
                    }
                }.bind(this));

                this.isShowFields(true);
                this.newCheckedField([]);
            }
        },

        getCheckedLength: function () {
            return Object.keys(this.checkedFields()).length;
        },

        checkFieldsState: function () {
            if (!this.getCheckedLength()) {
                this.isShowFields(false);
            }

            if (this.elems().length === this.getCheckedLength()) {
                this.isShowStatic(false);
            }
        }
    });
});
