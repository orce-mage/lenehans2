<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Order\ResourceModel;

use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_order';

    protected $_serializableFields = [
        GiftCardOrderInterface::GIFT_CARDS => ['[]', []]
    ];

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, GiftCardOrderInterface::ENTITY_ID);
    }
}
