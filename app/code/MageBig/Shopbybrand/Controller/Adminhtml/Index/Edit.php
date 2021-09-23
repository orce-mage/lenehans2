<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
	protected $resultPageFactory;
	public function __construct(
		Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Registry $registry
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
		parent::__construct($context);
	}
	public function execute()
    {
        $optionId = $this->getRequest()->getParam('option_id');
		$entityId = $this->getRequest()->getParam('entity_id');
		$model = $this->_objectManager->create('MageBig\Shopbybrand\Model\Brand');
		$storeId = (int)$this->getRequest()->getParam('store');
		if ($optionId) {
			$model->setStore($storeId);
			$model->setStoreId($storeId);
			$model->setOptionId($optionId);
			$model->load($entityId);
		}

		$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		$this->_coreRegistry->register('brand', $model);

		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
		$resultPage->addBreadcrumb(
			__('Edit Brand'),
			__('Edit Brand')
		);

		$resultPage->getConfig()->getTitle()->prepend(__('Edit Brand'));
		$resultPage->getConfig()->getTitle()
			->prepend($model->getBrandLabel() ? $model->getBrandLabel() : __('Edit Brand'));

		return $resultPage;
    }

	protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('MageBig_Shopbybrand::shopbybrand');
        $resultPage->addBreadcrumb(__('Edit Brand'), __('Edit Brand'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Brand'));
		return $resultPage;
	}

	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageBig_Shopbybrand::edit');
    }
}
