<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Account extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_account';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, GiftCardAccountInterface::ACCOUNT_ID);
    }
}
