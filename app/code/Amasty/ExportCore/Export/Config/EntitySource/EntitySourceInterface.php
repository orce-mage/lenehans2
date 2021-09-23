<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\EntitySource;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;

interface EntitySourceInterface
{
    /**
     * @return EntityConfigInterface[]
     */
    public function get();
}
