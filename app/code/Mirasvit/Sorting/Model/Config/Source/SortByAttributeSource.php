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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Model\Config\Source;

use Magento\Catalog\Model\Config;
use Magento\Framework\Option\ArrayInterface;

class SortByAttributeSource implements ArrayInterface
{
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function toOptionArray()
    {
        $result = [];

        $result[] = [
            'label' => __('Position'),
            'value' => 'position',
        ];

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($this->config->getAttributesUsedForSortBy() as $attribute) {
            $result[] = [
                'label' => $attribute->getStoreLabel(),
                'value' => $attribute->getAttributeCode(),
            ];
        }

        return $result;
    }
}
