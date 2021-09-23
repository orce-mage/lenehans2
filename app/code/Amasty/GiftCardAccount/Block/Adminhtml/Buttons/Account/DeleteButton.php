<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Adminhtml\Buttons\Account;

use Amasty\GiftCard\Block\Adminhtml\Buttons\AbstractDeleteButton;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

class DeleteButton extends AbstractDeleteButton
{
    /**
     * @return string
     */
    protected function getIdField(): string
    {
        return GiftCardAccountInterface::ACCOUNT_ID;
    }
}
