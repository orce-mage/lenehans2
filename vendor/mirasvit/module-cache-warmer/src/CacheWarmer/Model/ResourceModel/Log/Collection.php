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



namespace Mirasvit\CacheWarmer\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirasvit\CacheWarmer\Api\Data\LogInterface;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Mirasvit\CacheWarmer\Model\Log::class,
            \Mirasvit\CacheWarmer\Model\ResourceModel\Log::class
        );

        $this->_idFieldName = LogInterface::ID;
    }
}
