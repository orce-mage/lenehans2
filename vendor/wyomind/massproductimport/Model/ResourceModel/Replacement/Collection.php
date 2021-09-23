<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Replacement;

/**
 * Class Collection
 * @package Wyomind\MassProductImport\Model\ResourceModel\MappingValues
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    public $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Wyomind\MassProductImport\Model\Replacement', 'Wyomind\MassProductImport\Model\ResourceModel\Replacement');
    }

    /**
     * @param $mappingId
     * @return $this
     */
    public function getCollectionByRuleId($mappingId)
    {
        $this->_reset();
        $this->getSelect()->where("rule_id ='" . $mappingId . "'")->order('position  ASC');
      
        return $this;
    }
}
