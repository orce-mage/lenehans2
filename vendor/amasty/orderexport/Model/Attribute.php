<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model;

class Attribute extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Amasty\Orderexport\Model\ResourceModel\Attribute');
    }
}