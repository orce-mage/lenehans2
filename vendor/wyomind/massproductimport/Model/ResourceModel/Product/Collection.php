<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    public function getSkuAndIdentifierCollection($identifierCode)
    {
        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->getSelect()->columns(['entity_id', 'sku']);
        $this->addAttributeToSelect($identifierCode);
        return $this;
    }
}
