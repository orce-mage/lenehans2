<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter\Type\Toggle;

use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ExportCore\Api\Filter\FilterInterface;
use Magento\Framework\Data\Collection;

class Filter implements FilterInterface
{
    const TYPE_ID = 'toggle';

    public function apply(Collection $collection, FieldFilterInterface $filter)
    {
        $config = $filter->getExtensionAttributes()->getToggleFilter();
        if (!$config) {
            return;
        }
        $collection->addFieldToFilter(
            $filter->getField(),
            ['eq' => $config->getValue()]
        );
    }
}
