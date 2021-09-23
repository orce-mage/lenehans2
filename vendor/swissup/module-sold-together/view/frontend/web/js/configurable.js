define([
    'jquery',
    'configurable'
], function ($, mageConfigurable) {
    'use strict';

    $.widget('swissup.soldtogetherConfigurable', mageConfigurable, {

        /**
         * {@inheritdoc}
         */
        _create: function () {
            this._super();
            this._on({
                'change .field.configurable': '_updateOptionElementAttribute'
            });
        },

        /**
         * Update data attribute for option field
         *
         * @param  {jQuery.Event} event
         */
        _updateOptionElementAttribute: function (event) {
            var dropdown = event.target;

            $(event.currentTarget)
                .attr('data-option-selected', dropdown.value)
                .attr('data-attribute-id', dropdown.id.replace(/[a-z]*/, ''));
        },

        /**
         * {@inheritdoc}
         */
        _fillSelect: function (element) {
            this._super(element);

            $(element).parents('.field.configurable')
                .attr('data-option-selected', '')
                .attr('data-attribute-id', '');
        },

        /**
         * {@inheritdoc}
         */
        _changeProductImage: function () {
            var images,
                $block = this.element.parents('.soldtogether-block'),
                $context;

            if ($block.hasClass('amazon-default')) {
                $context = $('#soldtogether-image-' + this.getSuperProductId());
            } else {
                $context = this.element.parents('.product-item');
            }

            images = this.options.spConfig.images[this.simpleProduct];

            if (images) {
                $context.find('.product-image-photo').attr('src', images[0].img);
            }
        },

        /**
         * @return {Number}
         */
        getSuperProductId: function () {
            return this.options.spConfig.productId;
        }
    });

    return $.swissup.soldtogetherConfigurable;
});
