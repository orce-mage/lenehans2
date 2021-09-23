<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter\Type\Date;

use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ExportCore\Api\Filter\FilterInterface;
use Magento\Framework\Data\Collection;

class Filter implements FilterInterface
{
    const TYPE_ID = 'date';

    /**
     * @var ConditionConverter
     */
    private $conditionConverter;

    public function __construct(
        ConditionConverter $conditionConverter
    ) {
        $this->conditionConverter = $conditionConverter;
    }

    public function apply(Collection $collection, FieldFilterInterface $filter)
    {
        $config = $filter->getExtensionAttributes()->getDateFilter();
        if (!$config) {
            return;
        }

        $condition = $this->conditionConverter->convert(
            $filter->getCondition(),
            $config->getValue()
        );
        $collection->addFieldToFilter($filter->getField(), $condition);
    }
}
