define([
    'uiElement',
    'underscore',
    'ko'
], function (Element, _, ko) {
    'use strict';

    return Element.extend({
        defaults: {
            selected: [],
            fields: [],
            searchFields: [],
            allSelectedState: false,
            isSearchActive: false,
            checkedFields: [],
            newCheckedField: [],
            fieldsToRemove: [],
            copySelected: [],
            elemIndex: 0,
            uniqFor: '',
            listens: {
                selected: 'allSelectedStateCheck',
                checkedFields: 'updateSelected'
            },
            modules: {
                parentComponent: '${ $.parentName }'
            },
            searchValue: ''
        },

        initialize: function () {
            this._super();

            this.uniqFor = 'a' + Math.round(Math.random() * 100000);
            this.prepareData();

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'allSelectedState',
                'checkedFields',
                'searchFields',
                'searchValue',
                'isSearchActive',
                'selected',
                'newCheckedField',
                'fieldsToRemove'
            ]);

            return this;
        },

        prepareData: function () {
            _.each(this.checkedFields(), function (field) {
                this.selected.push(field.code);
            });
        },

        isAllSelected: function () {
            if (this.searchFields().length) {
                return this.isAllSearchSelected(this.searchFields());
            }

            return _.size(this.selected()) === _.size(this.fields);
        },

        isAllSearchSelected: function (fields) {
            return fields.every(function (field) {
                return _.contains(this.selected(), field.code);
            }.bind(this));
        },

        updateSelected: function () {
            this.selected(_.pluck(this.checkedFields(), 'code'));
        },

        checkClick: function () {
            if (!this.isAllSelected()) {
                var fields = this.searchFields().length ? this.searchFields() : this.fields;

                fields.forEach(function (field) {
                    this.selected.remove(field.code);
                    this.selected.push(field.code);
                }.bind(this));

                this.copySelected = ko.toJS(this.selected).slice();
                this.allSelectedState(true);

                return true;
            }

            if (this.isSearchActive()) {
                this.searchFields().forEach(function (field) {
                    this.selected.remove(field.code);
                }.bind(this));

                return true;
            }

            this.selected.removeAll();

            return true;
        },

        allSelectedStateCheck: function () {
            this.allSelectedState(this.isAllSelected());
        },

        getLabel: function (label, code) {
            return label ? label + ' (' + code + ')' : code;
        },

        addField: function (parent, field) {
            if (!this.isFieldSelected(field.code)) {
                this.selected.push(field.code);
                this.newCheckedField([field]);
            }

            this.parentComponent().closeModal();
        },

        isFieldSelected: function (code) {
            return _.some(this.checkedFields(), function (field) {
                return !!field && field.code === code;
            });
        },

        addSelectedFields: function () {
            var currentFieldsCodes = _.pluck(ko.toJS(this.checkedFields), 'code'),
                selectedFieldsCodes = this.selected(),
                toRemove = _.difference(currentFieldsCodes, selectedFieldsCodes),
                toAdd = _.difference(selectedFieldsCodes, currentFieldsCodes);

            if (!_.isEmpty(toRemove)) {
                this.fieldsToRemove(toRemove);
            }

            if (!_.isEmpty(toAdd)) {
                _.each(this.fields, function (field) {
                    if (_.contains(toAdd, field.code)) {
                        this.newCheckedField([field]);
                    }
                }.bind(this));
            }
        },

        changeSearch: function () {
            if (this.searchValue().length === 0) {
                this.clearSearch();
            }

            if (this.searchValue().length < 3) {
                return true;
            }

            this.search();
        },

        clearSearch: function () {
            const copySelected = this.selected().slice();

            this.isSearchActive(false);
            this.searchValue('');
            this.searchFields([]);
            this.selected(copySelected);
        },

        searchClick: function () {
            this.search();
        },

        search: function () {
            const copySelected = this.selected().slice();

            this.isSearchActive(true);
            this.searchFields(this.searchFilter(this.searchValue().trim()));
            this.allSelectedStateCheck();
            this.selected(copySelected);
        },

        searchFilter: function (value) {
            return this.fields.filter(function (field) {
                return field.code.indexOf(value) !== -1;
            });
        }
    });
});
