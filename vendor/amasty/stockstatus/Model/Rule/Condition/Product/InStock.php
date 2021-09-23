<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Rule\Condition\Product;

use Amasty\Stockstatus\Model\ResourceModel\Inventory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\Store\Model\StoreManagerInterface;

class InStock extends ProductCondition
{
    /**
     * @param ProductCollection $productCollection
     * @return InStock
     */
    public function collectValidatedAttributes($productCollection)
    {
        if ($this->hasData('inventory')) {
            /** @var Inventory $inventoryModel */
            $inventoryModel = $this->getData('inventory');
            /** @var StoreManagerInterface $storeManager */
            $storeManager = $this->getData('storeManager');
            $inventoryModel->addStockStatusToCollection(
                $productCollection,
                $storeManager->getStore($productCollection->getStoreId())->getWebsite()->getCode()
            );
        }

        return $this;
    }

    /**
     * Render element HTML
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . __(
                'In Stock %1 %2',
                $this->getOperatorElementHtml(),
                $this->getValueElement()->getHtml()
            )
            . $this->getRemoveLinkHtml();
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        return [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Yes')]
        ];
    }

    /**
     * Value element type getter
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Specify allowed comparison operators
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $this->setOperatorOption(['==' => __('is')]);

        return $this;
    }

    /**
     * @return string
     */
    public function getAttribute()
    {
        return Inventory::CUSTOM_IN_STOCK_COLUMN;
    }
}
