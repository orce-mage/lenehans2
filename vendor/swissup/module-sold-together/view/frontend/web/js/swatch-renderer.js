define([
    'jquery',
    'Magento_Swatches/js/swatch-renderer'
], function ($, mageSwatchRenderer) {
    'use strict';

    $.widget('swissup.soldtogetherSwatchRenderer', mageSwatchRenderer, {
        /**
         * @return {Number}
         */
        getSuperProductId: function () {
            return this.options.jsonConfig.productId;
        },

        /**
         * {@inheritdoc}
         */
        updateBaseImage: function (images, context, isInProductView) {
            var $block = this.element.parents('.soldtogether-block');

            if ($block.hasClass('amazon-default') ||
                $block.hasClass('amazon-stripe')
            ) {
                context = $('#soldtogether-image-' + this.getSuperProductId());
            }

            return this._super(images, context, isInProductView);
        },

        /**
         * {@inheritdoc}
         */
        _EnableProductMediaLoader: function ($this) {
            var $block = $this.parents('.soldtogether-block'),
                $item = $this.parents('.product-item-info');

            if ($block.hasClass('amazon-default')) {
                $item = $('#soldtogether-image-' + this.getSuperProductId());
            }

            $item.find('.product-image-photo').addClass(this.options.classes.loader);
        },

        /**
         * {@inheritdoc}
         */
        _DisableProductMediaLoader: function ($this) {
            var $block = $this.parents('.soldtogether-block'),
                $item = $this.parents('.product-item-info');

            if ($block.hasClass('amazon-default')) {
                $item = $('#soldtogether-image-' + this.getSuperProductId());
            }

            $item.find('.product-image-photo').removeClass(this.options.classes.loader);
        }
    });

    return $.swissup.soldtogetherSwatchRenderer;
});
