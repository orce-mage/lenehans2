<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Model\System\Config\Source;

/**
 * Class ProductIdentifier
 * @package Wyomind\MassStockUpdate\Model\System\Config\Source
 */
class ProductIdentifier
{
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_dataHelper->getProductIdentifiers();
    }
}