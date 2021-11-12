<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Ui\Finder\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;

class FilterSortModeSource implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('non-sorted'),
                'value' => FilterInterface::SORT_MODE_IMPORT,
            ],
            [
                'label' => __('A-Z'),
                'value' => FilterInterface::SORT_MODE_ASC_STRING,
            ],
            [
                'label' => __('Z-A'),
                'value' => FilterInterface::SORT_MODE_DESC_STRING,
            ],
            [
                'label' => __('0-9'),
                'value' => FilterInterface::SORT_MODE_ASC_INT,
            ],
            [
                'label' => __('9-0'),
                'value' => FilterInterface::SORT_MODE_DESC_INT,
            ],
        ];
    }
}
