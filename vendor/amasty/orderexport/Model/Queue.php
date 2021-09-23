<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Model;

use Magento\Framework\Model\AbstractModel;

class Queue extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Amasty\Orderexport\Model\ResourceModel\Queue');
        $this->setIdFieldName('entity_id');
    }
}
