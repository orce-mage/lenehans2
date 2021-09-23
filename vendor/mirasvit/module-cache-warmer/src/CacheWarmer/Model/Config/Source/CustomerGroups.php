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


use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;
use Magento\Framework\Option\ArrayInterface;

class CustomerGroups implements ArrayInterface
{
    private $customerGroupCollection;

    public function __construct(CustomerGroupCollection $customerGroupCollection)
    {
        $this->customerGroupCollection = $customerGroupCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->customerGroupCollection->toOptionArray();
    }

    /**
     * @return array
     */
    public function getCustomerGroupIds()
    {
        $ids = [];

        foreach ($this->toOptionArray() as $group) {
            $ids[] = $group['value'];
        }

        return $ids;
    }
}
