<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter\Type\Select;

use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ExportCore\Api\Filter\FilterInterface;
use Magento\Framework\Data\Collection;

class Filter implements FilterInterface
{
    const TYPE_ID = 'select';

    public function apply(Collection $collection, FieldFilterInterface $filter)
    {
        $config = $filter->getExtensionAttributes()->getSelectFilter();
        if (!$config) {
            return;
        }

        $condition = [$filter->getCondition() => $config->getValue()];
        if ($config->getIsMultiselect()
            && in_array($filter->getCondition(), ['finset', 'nfinset'])
            && !empty($config->getValue())
        ) {
            $condition = [];
            foreach ($config->getValue() as $item) {
                $condition[] = [$filter->getCondition() => $item];
            }
        }

        $collection->addFieldToFilter($filter->getField(), $condition);
    }
}
