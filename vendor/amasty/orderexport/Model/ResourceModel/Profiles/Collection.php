<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Model\ResourceModel\Profiles;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Orderexport\Model\Profiles', 'Amasty\Orderexport\Model\ResourceModel\Profiles');
        $this->_setIdFieldName('entity_id');
    }

    public function toExportButtonOptionArray()
    {
        $res = [];

        foreach ($this as $item) {
            $res[] = [
                'value' => $item->getData($this->getIdFieldName()),
                'label' => $item->getData('name'),
                'url' => 'amasty_orderexport/profiles/run/id/'.$item->getData($this->getIdFieldName()).'/download/1/',
            ];
        }
        return $res;
    }
}
