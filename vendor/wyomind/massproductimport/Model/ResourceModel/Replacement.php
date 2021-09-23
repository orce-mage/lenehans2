<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel;

/**
 * Class MappingValues
 * @package Wyomind\MassProductImport\Model\ResourceModel
 */
class Replacement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('massproductimport_replacement', 'id');
    }

    /**
     * @param $ruleId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteValues($ruleId)
    {
        $connection = $this->getConnection();

        $connection->delete(
            $this->getMainTable(),
            ['rule_id = ?' => $ruleId]
        );
    }
}
