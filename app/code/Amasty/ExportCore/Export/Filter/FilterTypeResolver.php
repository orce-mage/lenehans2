<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter;

use Amasty\ExportCore\Export\Filter\Type\Date\Filter as DateFilter;
use Amasty\ExportCore\Export\Filter\Type\Select\Filter as SelectFilter;
use Amasty\ExportCore\Export\Filter\Type\Text\Filter as TextFilter;
use Amasty\ExportCore\Export\Filter\Type\Toggle\Filter as ToggleFilter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class FilterTypeResolver
{
    /**
     * Get eav attribute filter type
     *
     * @param Attribute $attribute
     * @return string
     */
    public function getEavAttributeFilterType($attribute)
    {
        switch ($attribute->getFrontendInput()) {
            case 'date':
                return DateFilter::TYPE_ID;
            case 'select':
            case 'multiselect':
                return SelectFilter::TYPE_ID;
            case 'boolean':
                return ToggleFilter::TYPE_ID;
            default:
                return TextFilter::TYPE_ID;
        }
    }

    /**
     * Get table column filter type
     *
     * @param array $fieldDetails
     * @return string
     */
    public function getTableColumnFilterType(array $fieldDetails)
    {
        switch (strtolower($fieldDetails['DATA_TYPE'])) {
            case 'date':
            case 'datetime':
            case 'timestamp':
                return DateFilter::TYPE_ID;
            default:
                return TextFilter::TYPE_ID;
        }
    }
}
