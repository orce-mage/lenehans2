<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\ResourceModel;

use Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Quote extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_quote';

    protected $_serializableFields = [
        GiftCardQuoteInterface::GIFT_CARDS => ['[]', []]
    ];

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, GiftCardQuoteInterface::ENTITY_ID);
    }
}
