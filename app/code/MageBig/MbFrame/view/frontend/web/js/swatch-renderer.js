define([
    'jquery',
    'mage/utils/wrapper',
    'mage/apply/main'
], function ($, wrapper, mage) {
    'use strict';

    return function(targetModule){

        var create = targetModule.prototype._create,
            updatePrice = targetModule.prototype._UpdatePrice,
            renderSwatchOptions = targetModule.prototype._RenderSwatchOptions,
            updateBaseImage = targetModule.prototype.updateBaseImage;

        targetModule.prototype._create = wrapper.wrap(create, function(original){
            var options = this.options,
                gallery = $(options.mediaGallerySelector, '.column.main'),
                productData = this._determineProductData(),
                $main = productData.isInProductView ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');

            if (productData.isInProductView) {
                gallery.data('gallery') ?
                    this._onGalleryLoaded(gallery) :
                    gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));

                if ($main.find('.product.media .discount-percent').length) {
                    $main.find('.product-info-price .normal-price').addClass('special-price');
                }

                var item = this.element.parents('.product-item-info');
                if (item.find('.discount-percent').length) {
                    item.find('.normal-price').addClass('special-price');
                }
            } else {
                var mainImg,
                    $photo = $main.find('.product-image-photo');

                if ($photo.data('src')) {
                    mainImg = $photo.data('src');
                } else {
                    mainImg = $photo.attr('src');
                }

                options.mediaGalleryInitial = [{
                    'img': mainImg
                }];

                if ($main.find('.discount-percent').length) {
                    $main.find('.normal-price').addClass('special-price');
                }
            }

            this.productForm = this.element.parents(this.options.selectorProductTile).find('form:first');
            this.inProductList = this.productForm.length > 0;

            return this;
        });

        targetModule.prototype._UpdatePrice = wrapper.wrap(updatePrice, function(original){
            var result = this._getNewPrices(),
                product_list_info = this.element.parents('.product-item-info'),
                product_view_info = this.element.parents('.catalog-product-view'),
                discount_elm = product_list_info.find('.discount-percent'),
                discount_view_elm = product_view_info.find('.product.media .discount-percent');

            if (typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                var discount_percent = (result.finalPrice.amount-result.oldPrice.amount)*100/result.oldPrice.amount,
                    discount_text = discount_percent.toFixed(0)+'%';

                if (product_list_info.length) {
                    if (discount_elm.length) {
                        discount_elm.show();
                        discount_elm.text(discount_text);
                    } else {
                        product_list_info.find('.product-item-photo').append('<span class="discount-percent">'+discount_text+'</span>');
                    }
                }

                if (product_view_info.length) {
                    if (discount_view_elm.length) {
                        discount_view_elm.show();
                        discount_view_elm.text(discount_text);
                    } else {
                        product_view_info.find('.product.media').append('<span class="discount-percent">'+discount_text+'</span>');
                    }
                }

            } else {
                if (discount_elm.length) {
                    discount_elm.hide();
                }

                if (discount_view_elm.length) {
                    discount_view_elm.hide();
                }
            }

            mage.apply();

            return original();
        });

        targetModule.prototype._RenderSwatchOptions = wrapper.wrap(renderSwatchOptions, function(original, config, controlId){
            var optionConfig = this.options.jsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                sizeConfig = this.options.jsonSwatchImageSizeConfig,
                moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
                html = '';

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            $.each(config.options, function (index) {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    width,
                    height,
                    attr,
                    swatchImageWidth,
                    swatchImageHeight;

                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '"><span>' + moreText + '</span></a>';
                }

                id = this.id;
                type = parseInt(optionConfig[id].type, 10);
                value = optionConfig[id].hasOwnProperty('value') ?
                    $('<i></i>').text(optionConfig[id].value).html() : '';
                thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                width = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.width : 110;
                height = _.has(sizeConfig, 'swatchThumb') ? sizeConfig.swatchThumb.height : 90;
                label = this.label ? $('<i></i>').text(this.label).html() : '';
                attr =
                    ' id="' + controlId + '-item-' + id + '"' +
                    ' index="' + index + '"' +
                    ' aria-checked="false"' +
                    ' aria-describedby="' + controlId + '"' +
                    ' tabindex="0"' +
                    ' data-option-type="' + type + '"' +
                    ' data-option-id="' + id + '"' +
                    ' data-option-label="' + label + '"' +
                    ' aria-label="' + label + '"' +
                    ' role="option"' +
                    ' data-thumb-width="' + width + '"' +
                    ' data-thumb-height="' + height + '"';

                attr += thumb !== '' ? ' data-option-tooltip-thumb="' + thumb + '"' : '';
                attr += value !== '' ? ' data-option-tooltip-value="' + value + '"' : '';

                swatchImageWidth = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.width : 30;
                swatchImageHeight = _.has(sizeConfig, 'swatchImage') ? sizeConfig.swatchImage.height : 20;

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' data-option-empty="true"';
                }

                if (type === 0) {
                    // Text
                    html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                        '</div>';
                } else if (type === 1) {
                    // Color
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        ' style="background: ' + value +
                        ' no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 2) {
                    // Image
                    var ratio = swatchImageHeight/swatchImageWidth,
                        percent = parseFloat(ratio.toFixed(5))*100;
                    html += '<div class="' + optionClass + ' image" ' + attr +
                        ' style="width:' +
                        swatchImageWidth + 'px; height:' + swatchImageHeight + 'px">' +
                        '<span class="img-native-wrap" style="padding-bottom: ' + percent + '%;">' +
                        '<img loading="lazy" class="img-fluid" alt="" width="' + swatchImageWidth + '" height="' + swatchImageHeight + '" src="' + value + '">' +
                        '</span>' +
                        '</div>';
                } else if (type === 3) {
                    // Clear
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
                } else {
                    // Default
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                }
            });

            return html;
        });

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isInProductView
         */
        targetModule.prototype.updateBaseImage = wrapper.wrap(updateBaseImage, function (original, images, context, isInProductView) {
            var justAnImage = images[0],
                initialImages = this.options.mediaGalleryInitial,
                imagesToUpdate,
                gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                isInitial;

            if (isInProductView) {
                if (_.isUndefined(gallery)) {
                    context.find(this.options.mediaGallerySelector).on('gallery:loaded', function () {
                        this.updateBaseImage(images, context, isInProductView);
                    }.bind(this));

                    return;
                }

                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                isInitial = _.isEqual(imagesToUpdate, initialImages);

                if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                    imagesToUpdate = imagesToUpdate.concat(initialImages);
                }

                imagesToUpdate = this._setImageIndex(imagesToUpdate);

                if (imagesToUpdate.length > 1) {
                    context.find(this.options.mediaGallerySelector).addClass('imgs');
                } else {
                    context.find(this.options.mediaGallerySelector).removeClass('imgs');
                }

                gallery.updateData(imagesToUpdate);
                this._addFotoramaVideoEvents(isInitial);

            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
        });

        return targetModule;
    };
});
