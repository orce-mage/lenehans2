<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel\Profiles;

/**
 * Class Collection
 * @package Wyomind\MassStockUpdate\Model\ResourceModel\Profiles
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @param $profilesIds
     * @return $this
     */
    public function getList($profilesIds)
    {
        if (!empty($profilesIds)) {
            if (is_string($profilesIds)) {
                $this->getSelect()->where("id IN (" . $profilesIds . ")");
            } else {
                $this->getSelect()->where("id IN (" . implode(',', $profilesIds) . ")");
            }
        }
        return $this;
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Wyomind\MassStockUpdate\Model\Profiles', 'Wyomind\MassStockUpdate\Model\ResourceModel\Profiles');
    }
}
