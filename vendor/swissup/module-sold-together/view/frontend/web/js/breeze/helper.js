/* global _ */
(function () {
    'use strict';

    var $related = $('#related-products-field'),
        $relatedSuper;

    window.soldTogetherHelper = {
        /**
         * @return {Array}
         */
        getRelatedIds: function () {
            return $related.val() ? $related.val().split(',') : [];
        },

        /**
         * @param {Array} ids
         */
        setRelatedIds: function (ids) {
            $related.val(_.uniq(ids).join(','));
        },

        /**
         * @return {Object}
         */
        getRelatedSupers: function () {
            if (!$relatedSuper) {
                $relatedSuper = $('<input type="hidden" name="related_product_super_attribute">');
                $relatedSuper.insertAfter($related);
            }

            return $relatedSuper.val() ? JSON.parse($relatedSuper.val()) : {};
        },

        /**
         * @param {Object} superAttribute
         */
        setRelatedSupers: function (superAttribute) {
            $relatedSuper.val(JSON.stringify(superAttribute));
        },

        /**
         * Get attribute Id from product option element
         *
         * @param  {HTMLElement|jQuery} $optionEl
         * @return {String}
         */
        getAttributeId: function ($optionEl) {
            return $($optionEl).attr('data-attribute-id') || $($optionEl).attr('attribute-id');
        },

        /**
         * Get selected option from product option element
         *
         * @param  {HTMLElement|jQuery} $optionEl
         * @return {String}
         */
        getOptionSelected: function ($optionEl) {
            return $($optionEl).attr('data-option-selected') || $($optionEl).attr('option-selected');
        },

        /**
         * Find product options for element
         *
         * @param  {HTMLElement|jQuery} el
         * @return {jQuery}
         */
        findProductOptions: function (el) {
            return $(el).find('.swatch-attribute, .field.configurable');
        }
    };
})();
