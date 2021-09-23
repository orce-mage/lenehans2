<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Controller\Adminhtml\Import;

use Magento\Framework\Controller\ResultFactory;

/**
 * @codeCoverageIgnore
 */
class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ImportCore::import';

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_ImportCore::import');
        $resultPage->addBreadcrumb(__('Amasty Import'), __('Amasty Import'));
        $resultPage->addBreadcrumb(__('Import'), __('Import'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import'));

        return $resultPage;
    }
}
