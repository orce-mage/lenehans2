<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\VirtualField;

interface GeneratorInterface
{
    /**
     * @param array $currentRecord
     * @return mixed
     */
    public function generateValue(array $currentRecord);
}
