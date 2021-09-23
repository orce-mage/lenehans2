/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/product/uenc-processor',
    'Magento_Catalog/js/product/list/column-status-validator',
    'mage/apply/main'
], function (Element, uencProcessor, columnStatusValidator, mage) {
    'use strict';

    return Element.extend({
        defaults: {
            label: ''
        },

        /**
         * Prepare data, that will be inserted as data-mage-init attribute into button. With help of this attribute
         * Add To * buttons can understand post data and urls
         *
         * @param {Object} row
         * @returns {String}
         */
        getDataMageInit: function (row) {
            return '{"redirectUrl": { "url" : "'  + uencProcessor(row['add_to_cart_button'].url) + '"}}';
        },

        /**
         * Prepare Data-Post data that will be used in data-mage-init
         *
         * @param {Object} row
         * @return {String}
         */
        getDataPost: function (row) {
            return uencProcessor(row['add_to_cart_button']['post_data']);
        },

        getUenc: function (row) {
            return JSON.parse(this.getDataPost(row)).data.uenc;
        },

        getAction: function (row) {
            return uencProcessor(row['add_to_cart_button'].url);
        },

        getId: function (row) {
            return row['id'];
        },

        getSku: function (row) {
            return row['extension_attributes'].sku;
        },

        getFormKey: function (row) {
            return row['extension_attributes'].form_key;
        },

        getAddToCartMageInit: function () {
            mage.apply();
            // return '{"catalogAddToCart": {}}';
        },

        /**
         * Check if product has required options.
         *
         * @param {Object} row
         * @return {Boolean}
         */
        hasRequiredOptions: function (row) {
            return row['add_to_cart_button']['required_options'];
        },

        /**
         * Depends on this option, "Add to cart" button can be shown or hide
         *
         * @param {Object} row
         * @returns {Boolean}
         */
        isSalable: function (row) {
            return row['is_salable'];
        },

        /**
         * Depends on this option, "Add to cart" button can be shown or hide. Depends on  backend configuration
         *
         * @returns {Boolean}
         */
        isAllowed: function () {
            return columnStatusValidator.isValid(this.source(), 'add_to_cart', 'show_buttons');
        },

        /**
         * Get button label.
         *
         * @return {String}
         */
        getLabel: function () {
            return this.label;
        }
    });
});
