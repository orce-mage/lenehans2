<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel\Product;

/**
 * Class Collection
 * @package Wyomind\MassStockUpdate\Model\ResourceModel\Product
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @param $identifierCode
     * @param array $fields
     * @return $this
     */
    public function getSkuAndIdentifierCollection($identifierCode, $fields = [])
    {

        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->getSelect()->columns(array_merge(['entity_id', 'sku'], $fields));
        $this->addAttributeToSelect($identifierCode);
        return $this;

    }
}
