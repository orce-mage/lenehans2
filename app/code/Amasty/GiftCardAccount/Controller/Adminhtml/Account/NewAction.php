<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Adminhtml\Account;

use Amasty\GiftCardAccount\Controller\Adminhtml\AbstractAccount;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends AbstractAccount
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_GiftCard::giftcard_account');
        $resultPage->addBreadcrumb(__('New Gift Code Account'), __('New Gift Code Account'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Gift Code Account'));

        return $resultPage;
    }
}
