<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Code\ResourceModel;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Code extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_code';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, CodeInterface::CODE_ID);
    }
}
