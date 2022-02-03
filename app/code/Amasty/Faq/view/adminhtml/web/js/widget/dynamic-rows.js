define([
    'jquery',
    'underscore',
    'mage/template',
    'domReady!'
], function ($, _, mageTemplate) {
    'use strict';

    $.widget('mage.amFaqWidgetDynamicRows', {
        tableBody: $(),
        template: {},
        elements: null,
        elementsModels: {},

        options: {
            templateSelector: '#dynamic-rows-template',
            uniqId: '',
            template: '',
            rowsData: [],
            isSortable: true,
            rowContainerSelector: 'tr',
            deleteButtonSelector: 'button.action-delete',
            sortableOptions: {
                distance: 8,
                tolerance: 'pointer',
                cancel: 'input, button',
                axis: 'y'
            },
            tableBodySelector: '[data-role="options-container"]',
            elementIdSelector: '.col-row-id'
        },

        /**
         * @constructor
         */
        _create: function () {
            this.template = this.options.template.empty() ? mageTemplate(this.options.templateSelector)
                : mageTemplate(this.options.template);
            this.tableBody = this.element.find(this.options.tableBodySelector);
            this.elements = new Map();
            if (this.options.rowsData) {
                this.options.rowsData.forEach(function (data) {
                    this.addElement(data);
                }.bind(this));
            }
            this.render();
            this.addInsertRowsListener();
        },

        addInsertRowsListener: function() {
            $(document).on('amDynamicRowsAdd' + this.options.uniqId, this.addNewRowsCallback.bind(this));
        },

        /**
         *
         * @param {Event} event
         * @param {Object} eventData
         */
        addNewRowsCallback: function(event, eventData) {
            var rows = eventData.elements || {};

            _.each(rows, function (elem) {
                this.addElement(elem);
            }.bind(this));
            this.render();
        },

        /**
         *
         * @param {Array} data
         * @param {Boolean} render
         * @throws {Error}
         */
        addElement: function (data, render = false) {
            var element = $(this.template(data)), elementId = this.getElementId(element);

            if (this.elements.has(elementId)) {
                return;
            }

            this.elements.set(elementId, element);

            if (render) {
                this.render();
            }
        },

        /**
         *
         * @param {jQuery} element
         * @return {string}
         */
        getElementId: function(element) {
            element = element.is(this.options.rowContainerSelector) ? element
                : element.closest(this.options.rowContainerSelector);
            var elementId = element.find(this.options.elementIdSelector).text();

            if (!elementId) {
                throw new Error($.mage.__('Invalid template and/or element id selector'));
            }

            return elementId;
        },

        /**
         *
         * @param event
         * @listens onclick
         */
        removeElementCallback: function (event) {
            var elementId = this.getElementId($(event.target));

            $(this.elements.get(elementId)).remove();
            this.elements.delete(elementId);
            this.triggerUpdateEvent();
        },

        /**
         *
         * @param {Array} elements
         */
        render: function (elements = []) {
            elements = elements.length ? elements : this.elements;
            this.tableBody.empty();
            elements.forEach(function (element) {
                this.tableBody.append(element);
            }.bind(this));
            this.initSortable();
            this.initRemoveButtons();
            this.triggerUpdateEvent();
        },

        /**
         * @fires amDynamicRowsUpdated
         */
        triggerUpdateEvent: function() {
            var eventData = {}, order = 0;

            this.elements.forEach(function (value, key) {
               eventData[key] = {order: ++order};
            }.bind(this));
            $(document).trigger('amDynamicRowsUpdated' + this.options.uniqId, {'elements': eventData});
        },

        /**
         * @listens update
         */
        onUpdateCallback: function () {
            var sortedElements = new Map();

            this.tableBody.find(this.options.rowContainerSelector).each(function (i, elem) {
                var elemId = this.getElementId($(elem));
                sortedElements.set(elemId, this.elements.get(elemId));
            }.bind(this));
            this.elements = sortedElements;
            this.triggerUpdateEvent();
        },

        /**
         * init sortable feature
         */
        initSortable: function () {
            if (this.options.isSortable) {
                this.tableBody.sortable(
                    $.extend(
                        this.options.sortableOptions,
                        {update: this.onUpdateCallback.bind(this)}
                    )
                );
            }
        },

        initRemoveButtons: function () {
            this.tableBody.find(this.options.deleteButtonSelector).on('click', this.removeElementCallback.bind(this));
        }
    });

    return $.mage.amFaqWidgetDynamicRows;
});
