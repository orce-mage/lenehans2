<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Amasty\Orderexport\Model\Queue::class, \Amasty\Orderexport\Model\ResourceModel\Queue::class);
        $this->_setIdFieldName('entity_id');
    }
}
