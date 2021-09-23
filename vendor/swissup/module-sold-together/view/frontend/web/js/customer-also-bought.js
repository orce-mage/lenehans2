define([
    'jquery',
    './helper',
    './validator',
    'mage/utils/wrapper',
    'mage/translate',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, helper, validator, wrapper) {
    'use strict';

    /**
     * Update super attribute for related products
     *
     * @param  {jQuery} $productItemElement
     */
    function _updateRelatedSuper($productItemElement) {
        var superAttribute = {},
            itemSuper = {},
            $checkbox;

        superAttribute = helper.getRelatedSupers();
        $checkbox = $('.soldtogether-tocart', $productItemElement);

        if ($checkbox.is(':checked')) {
            helper.findProductOptions($productItemElement).each(function () {
                itemSuper[helper.getAttributeId(this)] = helper.getOptionSelected(this);
            });

            superAttribute[$checkbox.val()] = itemSuper;
        } else {
            delete superAttribute[$checkbox.val()];
        }

        helper.setRelatedSupers(superAttribute);
    }

    /**
     * Update selected related products ids
     *
     * @param  {jQuery} $checkbox
     */
    function _updateRelated($checkbox) {
        var ids,
            index;

        ids = helper.getRelatedIds();

        if ($checkbox.is(':checked')) {
            ids.push($checkbox.val());
        } else {
            index = ids.indexOf($checkbox.val());

            if (index !== -1) {
                ids.splice(index, 1);
            }
        }

        helper.setRelatedIds(ids);
    }

    /**
     * Get contained to messages
     *
     * @param  {HTMLElement} el
     * @return {jQuery}
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

    $.widget('swissup.customerAlsoBought', {
        /**
         * {@inheritdoc}
         */
        _init: function () {
            this._on({
                /**
                 * Listen  change evenets (tocart checkbox toggle, product option change)
                 *
                 * @param  {jQuery.Event} event
                 */
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
            var formValidation = $(this.options.tocartForm).data('mageValidation'),
                self = this;

            if (formValidation) {
                // Wrap default form validate to validate product options
                formValidation.validate.form = wrapper.wrap(formValidation.validate.form,
                    function (original) {
                        var $messages,
                        $selectedItems,
                        $productOptions;

                        $messages = _getMessagesContainer(self.element);
                        $messages.html('');
                        $selectedItems = $('.soldtogether-tocart:checked', self.element)
                            .parents('.product-item');
                        $productOptions = helper.findProductOptions($selectedItems);

                        if (validator.isValidOptions($productOptions)) {
                            return original();
                        }

                        validator.showMessage({
                            type: 'error',
                            text: $.mage.__('Choose options for all selected products.')
                        }, $messages);

                        $('html, body').animate({
                            scrollTop: $(self.element).offset().top - 40
                        }, 'slow');

                        return false;

                    }
                );
            } else {
                $(this.element).one('click', this._initTocartFormValidation.bind(this));
            }
        }
    });

    return $.swissup.customerAlsoBought;
});
