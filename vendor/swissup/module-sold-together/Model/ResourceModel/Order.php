<?php
namespace Swissup\SoldTogether\Model\ResourceModel;

/**
 * SoldTogether Order mysql resource
 */
class Order extends AbstractResourceModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_soldtogether_order', 'relation_id');
    }
}
