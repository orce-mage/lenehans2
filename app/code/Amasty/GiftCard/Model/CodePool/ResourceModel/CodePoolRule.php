<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool\ResourceModel;

use Amasty\GiftCard\Api\Data\CodePoolRuleInterface;
use Magento\Rule\Model\ResourceModel\AbstractResource;

class CodePoolRule extends AbstractResource
{
    const TABLE_NAME = 'amasty_giftcard_code_pool_rule';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, CodePoolRuleInterface::RULE_ID);
    }
}
