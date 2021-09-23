<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api;

use Amasty\ExportCore\Api\ExportProcessInterface;

interface CollectionModifierInterface
{
    public function apply(\Magento\Framework\Data\Collection $collection)
        : \Amasty\ExportCore\Api\CollectionModifierInterface;
}
