<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Controller\Adminhtml\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
	protected $resultPageFactory;
	public function __construct(Context $context, PageFactory $resultPageFactory)
	{
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}
	public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageBig_Shopbybrand::shopbybrand');
        $resultPage->addBreadcrumb(__('Manage Brands'), __('Manage Brands'));
        $resultPage->addBreadcrumb(__('Manage Brands'), __('Manage Brands'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Brands'));

        return $resultPage;
    }
	/**
     * Is the user allowed to view the menu grid.
     *
     * @return bool
     */
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageBig_Shopbybrand::index');
    }
}
