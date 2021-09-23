<?php

namespace MageBig\AjaxSearch\Model\Source;

class ProductFields
{
    const NAME = 'name';

    const SKU = 'sku';

    const IMAGE = 'image';

    const REVIEWS_RATING = 'reviews_rating';

    const PRICE = 'price';

    const URL = 'url';

    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = [
            ['value' => self::NAME, 'label' => __('Product Name')],
            ['value' => self::SKU, 'label' => __('SKU')],
            ['value' => self::IMAGE, 'label' => __('Product Image')],
            ['value' => self::REVIEWS_RATING, 'label' => __('Reviews Rating')],
            ['value' => self::PRICE, 'label' => __('Price')],
        ];

        return $this->options;
    }
}
