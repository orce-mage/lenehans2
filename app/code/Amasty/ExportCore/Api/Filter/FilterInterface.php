<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Filter;

use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Magento\Framework\Data\Collection;

interface FilterInterface
{
    public function apply(Collection $collection, FieldFilterInterface $filter);
}
