<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractCodePool
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_GiftCard::giftcard_code');
        $resultPage->addBreadcrumb(__('Gift Card Code Pools'), __('Gift Card Code Pools'));
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Card Code Pools'));

        return $resultPage;
    }
}
