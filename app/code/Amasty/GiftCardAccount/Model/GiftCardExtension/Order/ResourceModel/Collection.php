<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Order\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order::class,
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\ResourceModel\Order::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this->getItems() as $item) {
            $this->getResource()->unserializeFields($item);
            $item->setDataChanges(false);
        }
        return $this;
    }
}
