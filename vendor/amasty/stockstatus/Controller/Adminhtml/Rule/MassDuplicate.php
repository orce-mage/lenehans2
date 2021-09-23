<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Controller\Adminhtml\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Magento\Framework\Phrase;

class MassDuplicate extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Stockstatus::ruleduplicate';

    protected function itemAction(RuleInterface $rule): void
    {
        // load with extensions (e.x. with ranges)
        $rule = $this->getRepository()->getById($rule->getId(), true);
        $this->getRepository()->duplicate($rule);
    }

    protected function getSuccessMessage(int $collectionSize = 0): Phrase
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been duplicated.', $collectionSize);
        }

        return __('No records have been duplicated.');
    }
}
