<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Profiles;

/**
 * Class Collection
 * @package Wyomind\MassProductImport\Model\ResourceModel\Profiles
 */
class Collection extends \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\Collection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Wyomind\MassProductImport\Model\Profiles', 'Wyomind\MassProductImport\Model\ResourceModel\Profiles');
    }
}
