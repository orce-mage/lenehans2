define([
    'jquery',
    'mage/template',
    'mage/translate',
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget',
    'amastyStockstatusAdvancedSettingsModal'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.advancedOptionSettings', {
        options: {
            frontendInputSelector: '#frontend_input',
            optionsDeleteButtonsSelector: '.col-delete[id^="delete_button_container"]',
            buttonsTemplateSelector: '#stockstatus-settings-buttons',
            stockstatusButtonsSelector: '[data-amstockstatus-js="buttons"]',
            magentoDeleteButtonsSelector: 'button.delete',
            url: ''
        },

        optionsPanel: null,
        optionsDeleteButtonsContainer: null,
        buttonsTemplate: null,

        /**
         *
         * @param {object} options
         * @private
         */
        _create: function (options) {
            var initCallback = _.once(this.init.bind(this));

            if (this.isNeedForceInit()) {
                initCallback();
            }

            $('body').on('processStop', initCallback)
        },

        init: function () {
            if (!this.optionsDeleteButtonsContainer.length) {
                this.initNodes();
            }

            this.buttonsTemplate = mageTemplate(this.options.buttonsTemplateSelector);
            this.renderButtons();
        },

        isNeedForceInit: function()
        {
            this.initNodes();

            return this.optionsDeleteButtonsContainer.length &&
                this.optionsDeleteButtonsContainer.length === window.attributeOption.totalItems
        },

        initNodes: function () {
            this.optionsPanel = $('#' + this.getFrontendInputId());
            this.optionsDeleteButtonsContainer = this.optionsPanel.find(this.options.optionsDeleteButtonsSelector);
        },

        renderButtons: function() {
            this.optionsDeleteButtonsContainer.each(function (number, element) {
                element = $(element);

                if (element.find(this.options.stockstatusButtonsSelector).length === 0) {
                    var optionId = this.getOptionIdByButtonsContainerId(element.attr('id')),
                        buttons = this.createButtonsBlock(optionId);
                    element.find(this.options.magentoDeleteButtonsSelector).replaceWith(buttons);
                    element.find('.amstockstatus-settings').stockstatusSettingsModal({
                        url: this.options.url.replace('__optionId__', optionId),
                        optionId: optionId
                    });
                }
            }.bind(this));
        },

        /**
         *
         * @param {int} optionId
         * @return {jQuery}
         */
        createButtonsBlock: function (optionId) {
            return $(this.buttonsTemplate({
                data: {
                    'optionId' : optionId,
                    'deleteLabel': $.mage.__('Delete'),
                    'settingsLabel': $.mage.__('Amasty Stockstatus Settings')
                }
            }));
        },

        /**
         *
         * @param {string} containerId
         * @return {number} id
         */
        getOptionIdByButtonsContainerId: function (containerId) {
            var rawOptionId = containerId.match(/\d+/);

            return Number(rawOptionId[0]);
        },

        /**
         * @return {string}
         */
        getFrontendInputId: function () {
            var id = '';

            switch ($(this.options.frontendInputSelector).val()) {
                case 'swatch_visual' :
                    id = 'swatch-visual-options-panel';
                    break;
                case 'swatch_text' :
                    id = 'swatch-text-options-panel';
                    break;
                default :
                    id = 'manage-options-panel';
            }

            return id;
        },
    });

    return $.mage.advancedOptionSettings;
});
