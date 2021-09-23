<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Filesystem\Driver;

/**
 * Doesn't change anything from \Magento\Framework\Filesystem\Driver\Https
 * but extends \Wyomind\MassProductImport\Filesystem\Driver\Http to be able to
 * use the getStatus method
 */
class Https extends \Wyomind\MassStockUpdate\Filesystem\Driver\Https
{
    
}
