/**
 * Default Module logic
 */

define([
    'uiComponent',
    'jquery',
    'ko',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Magento_Catalog/js/product/remaining-characters'
], function (Component, $, ko, modal, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            giftCardType: null,
            isEGift: false,
            isPhysicalGift: false,
            previewUrl: '',
            maxLength: 300,
            customImageUrl: '',
            isContainerReady: false,
            preconfiguredValues: {},
            element: {
                customImage: '[data-amcard-js="custom-image"]',
                previewContainer: '[data-amcard-js="preview"]',
                fields: '[data-amcard-js="field"]',
                textarea: '[data-amgiftcard-js="textarea"]',
                charCounter: '[data-amcard-js="char-counter"]',
                addToCardButton: '#product-addtocart-button',
                form: '#product_addtocart_form'
            },
            imports: {
                priceValue: '${ "price" }:currentPrice',
                customPriceValue: '${ "price" }:customAmount',
                imageId: '${ "images" }:checkedImageId'
            },
            cardTypes: {
                combined: 3,
                printed: 2,
                virtual: 1
            },
            allowedFields: [],
            tooltip: ''
        },

        initialize: function () {
            this._super();

            $(this.element.form).attr('enctype', 'multipart/form-data'); // for update cart

            this.currentCardType = ko.computed(function () {
                var value = 0;

                if (this.isEGift()) {
                    value += 1;
                }

                if (this.isPhysicalGift()) {
                    value += 2;
                }

                return value || '';
            }.bind(this));
            this.isContainerReady(true);
            this.isPhysicalGift(this.giftCardType == this.cardTypes.printed);

            this.addEvents();

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'isEGift',
                'isPhysicalGift',
                'priceValue',
                'customPriceValue',
                'isContainerReady',
                'imageId'
            ]);

            return this;
        },

        addEvents: function () {
            this.remainingCharacters();
            $(this.element.addToCardButton).on('click', this.validateForm.bind(this));
        },

        remainingCharacters: function () {
            $(this.element.textarea).remainingCharacters({
                maxLength: this.maxLength,
                remainingText: $t('characters remaining'),
                counterSelector: this.element.charCounter,
                noteSelector: this.element.textarea
            });
        },

        getFormData: function () {
            var formData = new FormData(),
                fields = $(this.element.fields),
                price = this.customPriceValue() || this.priceValue(),
                customImage;

            $.each(fields, function (index, item) {
                formData.append($(item).attr('name'), $(item).val());
            });

            if (price) {
                formData.append('am_giftcard_amount', price);
            }

            if (this.imageId()) {
                formData.append('am_giftcard_image', this.imageId());
            } else {
                customImage = $(this.element.customImage);

                if (customImage.prop('files').length > 0) {
                    formData.append(customImage.attr('name'), customImage.prop('files')[0]);
                } else if (this.customImageUrl) {
                    formData.append('am_giftcard_custom_image', this.customImageUrl);
                }
            }

            return formData;
        },

        isShowField: function (name) {
            if (this.allowedFields.indexOf(name) !== -1) {
                return true;
            }

            return false;
        },

        openGiftPreview: function () {
            var options = {
                    type: 'popup',
                    responsive: true,
                    modalClass: 'amgiftcard-modal-container'
                },
                popup = modal(options, this.element.previewContainer),
                formData;

            if (!$(this.element.form).validate().form()) {
                return;
            }

            formData = this.getFormData();

            $('body').trigger('processStart');
            $.ajax({
                data: formData,
                url: this.previewUrl,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (data) {
                    $('body').trigger('processStop');
                    popup.element.html(data);
                    popup.openModal().on('modalclosed', function () {
                        popup.element.html('');
                    });
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $('body').trigger('processStop');
                    console.log(errorThrown);
                }
            });
        },

        validateForm: function (event) {
            if ($('#product_addtocart_form').validate().form() &&
                ($(this.element.textarea).val() === undefined ||
                $(this.element.textarea).val().length <= this.maxLength)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
        },

        getGiftCardType: function (name) {
            var type = +this.preconfiguredValues[name];

            switch (type) {
                case this.cardTypes.virtual:
                    this.isEGift(true);
                    break;
                case this.cardTypes.printed:
                    this.isPhysicalGift(true);
                    break;
                case this.cardTypes.combined:
                    this.isPhysicalGift(true);
                    this.isEGift(true);
                    break;
            }
        },

        getPreconfiguredValue: function (name) {
            if (this.preconfiguredValues[name]) {
                return this.preconfiguredValues[name];
            }
        }
    });
});
