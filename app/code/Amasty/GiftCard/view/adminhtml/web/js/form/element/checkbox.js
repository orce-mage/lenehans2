define([
    'jquery',
    'Magento_Ui/js/form/element/single-checkbox',
    'Amasty_GiftCard/js/actions/create-draggable-element'
], function ($, Checkbox, createDraggableElement) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            positionY: 0,
            positionX: 0,
            elementSelector: '',
            imageValue: [],
            listens: {
                imageRendered: 'toggleDraggableElement',
                value: 'toggleDraggableElement',
                imageValue: 'resetCheckbox'
            }
        },

        initObservable: function () {
            this._super()
                .observe([
                    'positionY',
                    'positionX'
                ]);

            return this;
        },

        /**
         * Toggle draggable element
         */
        toggleDraggableElement: function () {
            if (Number(this.value()) && this.imageRendered) {
                $(this.elementSelector).show();
                createDraggableElement(this.elementSelector, this.positionY, this.positionX);
            } else {
                $(this.elementSelector).hide();
            }
        },

        /**
         * Reset Checkbox
         *
         * @param {array} value
         */
        resetCheckbox: function (value) {
            if (!value.length) {
                this.checked(false);
            }
        }
    });
});
