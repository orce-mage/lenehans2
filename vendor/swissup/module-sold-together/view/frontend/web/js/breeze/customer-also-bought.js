/* global breeze soldTogetherHelper */
(function () {
    'use strict';

    /**
     * Update super attribute for related products
     *
     * @param  {Cash} $productItemElement
     */
    function _updateRelatedSuper($productItemElement) {
        var superAttribute = {},
            itemSuper = {},
            $checkbox;

        superAttribute = soldTogetherHelper.getRelatedSupers();
        $checkbox = $('.soldtogether-tocart', $productItemElement);

        if ($checkbox.is(':checked')) {
            soldTogetherHelper.findProductOptions($productItemElement).each(function () {
                itemSuper[soldTogetherHelper.getAttributeId(this)] = soldTogetherHelper.getOptionSelected(this);
            });

            superAttribute[$checkbox.val()] = itemSuper;
        } else {
            delete superAttribute[$checkbox.val()];
        }

        soldTogetherHelper.setRelatedSupers(superAttribute);
    }

    /**
     * Update selected related products ids
     *
     * @param  {Cash} $checkbox
     */
    function _updateRelated($checkbox) {
        var ids,
            index;

        ids = soldTogetherHelper.getRelatedIds();

        if ($checkbox.is(':checked')) {
            ids.push($checkbox.val());
        } else {
            index = ids.indexOf($checkbox.val());

            if (index !== -1) {
                ids.splice(index, 1);
            }
        }

        soldTogetherHelper.setRelatedIds(ids);
    }

    /**
     * Get contained to messages
     *
     * @param  {HTMLElement} el
     * @return {Cash}
     */
    function _getMessagesContainer(el) {
        var $messages;

        $messages = $('.messages', el);

        if (!$messages.length) {
            $messages = $('<div class="messages"></div>');
            $('.block-content', el).prepend($messages);
        }

        return $messages;
    }

    breeze.widget('customerAlsoBought', {
        component: 'Swissup_SoldTogether/js/customer-also-bought',

        /** [create description] */
        create: function () {
            this._on({
                /** [description] */
                'change .product-item-info': function (event) {
                    _updateRelated($(event.currentTarget).find('.soldtogether-tocart'));
                    _updateRelatedSuper($(event.currentTarget));
                }
            });

            this._initTocartFormValidation();
        },

        /**
         * Initialize additional validation on add to cart form when it is ready
         */
        _initTocartFormValidation: function () {
            var validator = window.soldTogetherValidator,
                self = this;

            $(this.options.tocartForm).on('validateAfter', function (event, data) {
                if (!data.result.valid) {
                    return;
                }

                data.result.valid = validator.isValidOptions(
                    soldTogetherHelper.findProductOptions(
                        $('.soldtogether-tocart:checked', self.element).parents('.product-item')
                    )
                );

                if (!data.result.valid) {
                    validator.showMessage({
                        type: 'error',
                        text: $.mage.__('Choose options for all selected products.')
                    }, _getMessagesContainer(self.element).html(''));

                    window.scrollTo(0, $(self.element).offset().top - 40);
                }
            });
        }
    });
})();
