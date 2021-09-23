<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Controller\Adminhtml\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Stockstatus::edit';

    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->updateTitles($resultPage);

        return $resultPage;
    }

    private function updateTitles(Page $page): void
    {
        if ($this->getRequest()->getParam(RuleInterface::ID) === null) {
            $title = __('New Rule')->render();
        } else {
            $title = __('Edit Rule')->render();
        }

        $page->setActiveMenu('Amasty_Stockstatus::rule')
            ->addBreadcrumb($title, $title);
        $page->getConfig()->getTitle()->prepend($title);
    }
}
