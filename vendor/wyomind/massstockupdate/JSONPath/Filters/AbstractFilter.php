<?php

/**
 * JSONPath implementation for PHP.
 *
 */
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare (strict_types=1);
namespace Wyomind\MassStockUpdate\JSONPath\Filters;

use ArrayAccess;
use Wyomind\MassStockUpdate\JSONPath\JSONPath;
use Wyomind\MassStockUpdate\JSONPath\JSONPathToken;
abstract class AbstractFilter
{
    /**
     * @var  bool
     */
    protected $magicIsAllowed = false;
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind, $options = false)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->magicIsAllowed = (bool) ($options & JSONPath::ALLOW_MAGIC);
    }
    /**
     * @param array|ArrayAccess $collection
     */
    public abstract function filter($collection) : array;
}