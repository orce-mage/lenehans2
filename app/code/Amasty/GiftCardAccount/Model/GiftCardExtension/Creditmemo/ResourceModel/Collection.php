<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Creditmemo::class,
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\ResourceModel\Creditmemo::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
