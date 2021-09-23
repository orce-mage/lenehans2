<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Controller\Adminhtml\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Model\Source\Status;

class MassEnable extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Stockstatus::ruleenable';

    protected function itemAction(RuleInterface $rule): void
    {
        $rule->setStatus(Status::ACTIVE);
        $this->getRepository()->save($rule);
    }
}
