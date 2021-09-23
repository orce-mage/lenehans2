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

class WarmStrategy implements ArrayInterface
{
    const STRATEGY_POPULARITY = 'popularity';
    const STRATEGY_PAGE_TYPE  = 'page_type';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            self::STRATEGY_POPULARITY => __('Popularity'),
            self::STRATEGY_PAGE_TYPE  => __('Page Type'),
        ];
    }
}