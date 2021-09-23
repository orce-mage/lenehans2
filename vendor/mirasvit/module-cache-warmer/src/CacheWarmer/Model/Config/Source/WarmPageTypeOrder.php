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

class WarmPageTypeOrder implements ArrayInterface
{
    const WARM_ORDER_ID         = 'warm_order_id';
    const WARM_ORDER_POPULARITY = 'warm_order_popularity';
    const WARM_ORDER_URI        = 'warm_order_uri';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            self::WARM_ORDER_ID         => __('ID'),
            self::WARM_ORDER_POPULARITY => __('Popularity'),
            self::WARM_ORDER_URI        => __('URI'),
        ];
    }
}