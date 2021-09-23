<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\ResourceModel;

use Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Invoice extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_invoice';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, GiftCardInvoiceInterface::ENTITY_ID);
    }
}
