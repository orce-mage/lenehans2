<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace MageBig\WidgetPlus\Block;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Catalog Products List widget block
 * Class ProductsList.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTab extends \MageBig\WidgetPlus\Block\Product
{
    const CACHE_TAGS = 'WIDGETPLUS_PRODUCT_TAB';

    public function initializeProductTab($value)
    {
        $limit = (int)$this->getData('limit');
        if (is_numeric($value)) {
            $type = 'category';
        } else {
            $type = 'product';
        }
        $params = [];

        if ($this->getData('period')) {
            $params['period'] = $this->getData('period');
        }
        if ($this->getData('category_ids')) {
            $params['category_ids'] = explode(',', $this->getData('category_ids'));
        }
        if ($this->getData('product_ids')) {
            $params['product_ids'] = explode(',', $this->getData('product_ids'));
        }
        if ($this->getCustomerId()) {
            $params['customer_id'] = $this->getCustomerId();
        }

        $collection = $this->_collectionFactory->create()->getProducts($type, $value, $params, $limit);

        return $collection;
    }

    public function getTabs()
    {
        $tabs = [];
        $type = $this->getData('widget_tab');
        $labels = explode('//', $this->getData('labels'));

        switch ($type) {
            case 'categories':
                $categoryIds = explode(',', $this->getData('category_ids'));
                foreach ($categoryIds as $index => $categoryId) {
                    $categoryModel = $this->categoryModel->load($categoryId, ['name']);
                    if ($categoryModel->getId()) {
                        $tabs[] = [
                            'id' => 'category-'.$categoryModel->getId(),
                            'label' => isset($labels[$index]) && $labels[$index] ? trim($labels[$index]) : $categoryModel->getName(),
                            'value' => $categoryModel->getId()
                        ];
                    }
                }
                break;
            case 'collections':
                $collectionNames = $this->getData('collections');
                if (!is_array($collectionNames)) {
                    $collectionNames = explode(',', $this->getData('collections'));
                }
                foreach ($collectionNames as $index => $collectionName) {
                    $tabs[] = [
                        'id' => 'collection-'.$collectionName,
                        'label' => isset($labels[$index]) && $labels[$index] ? __(trim($labels[$index])) : __($collectionName),
                        'value' => $collectionName
                    ];
                }
                break;
        }

        return $tabs;
    }
}
