<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Block\Adminhtml\Buttons\CodePool;

use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Block\Adminhtml\Buttons\AbstractDeleteButton;

class DeleteButton extends AbstractDeleteButton
{
    /**
     * @return string
     */
    protected function getIdField(): string
    {
        return CodePoolInterface::CODE_POOL_ID;
    }
}
