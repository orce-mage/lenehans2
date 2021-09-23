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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\CacheWarmer\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;

class SourceType implements ArrayInterface
{
    public function toOptionArray()
    {
        $types = [
            SourceInterface::TYPE_CRAWLER => 'Crawler',
            SourceInterface::TYPE_VISITOR => 'Visitors\' actions',
            SourceInterface::TYPE_FILE    => 'File',
            SourceInterface::TYPE_SITEMAP => 'Sitemap'
        ];

        $options = [];

        foreach ($types as $value => $lable) {
            $options[] = [
                'value' => $value,
                'label' => $lable,
            ];
        }

        return $options;
    }
}
