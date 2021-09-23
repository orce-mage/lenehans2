<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Model\System\Config\Source;

/**
 * Class FieldDelimiter
 * @package Wyomind\MassStockUpdate\Model\System\Config\Source
 */
class FieldDelimiter
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
        $data = [];
        foreach ($this->_dataHelper->getFieldDelimiters() as $key => $value) {
            $data[] = ['value' => $key, 'label' => $value];
        }
        return $data;
    }
}