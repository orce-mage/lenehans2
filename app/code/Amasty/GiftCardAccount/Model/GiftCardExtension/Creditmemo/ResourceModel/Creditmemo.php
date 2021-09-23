<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\ResourceModel;

use Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Creditmemo extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_creditmemo';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, GiftCardCreditmemoInterface::ENTITY_ID);
    }
}
