<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter\Type\Text;

use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ExportCore\Api\Filter\FilterInterface;
use Magento\Framework\Data\Collection;

class Filter implements FilterInterface
{
    const TYPE_ID = 'text';

    public function apply(Collection $collection, FieldFilterInterface $filter)
    {
        $config = $filter->getExtensionAttributes()->getTextFilter();
        if (!$config) {
            return;
        }
        $value = $config->getValue();
        switch ($filter->getCondition()) {
            case 'like':
                $value = '%' . $value . '%';
                break;
            case 'in':
            case 'nin':
                $value = explode(PHP_EOL, $value);
                break;
        }
        $collection->addFieldToFilter(
            $filter->getField(),
            [$filter->getCondition() => $value]
        );
    }
}
