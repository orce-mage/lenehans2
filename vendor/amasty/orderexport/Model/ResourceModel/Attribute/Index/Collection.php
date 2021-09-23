<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model\ResourceModel\Attribute\Index;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Amasty\Orderexport\Model\Attribute\Index', 'Amasty\Orderexport\Model\ResourceModel\Attribute\Index');
    }
}
