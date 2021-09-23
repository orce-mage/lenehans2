<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\Image;

use Amasty\GiftCard\Controller\Adminhtml\AbstractImage;
use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractImage
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_GiftCard::giftcard_image');
        $resultPage->addBreadcrumb(__('Gift Card Images'), __('Gift Card Images'));
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Card Images'));

        return $resultPage;
    }
}
