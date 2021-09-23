<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\EntitySource;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;

interface EntitySourceInterface
{
    /**
     * @return EntityConfigInterface[]
     */
    public function get();
}
