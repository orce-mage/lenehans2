<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model\ResourceModel\Attribute;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Amasty\Orderexport\Model\Attribute', 'Amasty\Orderexport\Model\ResourceModel\Attribute');
    }

    public function joinProductAttributes()
    {
        $this->getSelect()->joinLeft(
            ['product_attributes' => $this->getTable('eav_attribute')],
            'main_table.attribute_id = product_attributes.attribute_id',
            ['frontend_input']
        );

        return $this;
    }
}
