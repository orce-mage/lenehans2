<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Controller\Adminhtml\Rules;

/**
 * Class Edit
 * @package Wyomind\MassProductImport\Controller\Adminhtml\Profiles
 */
class Edit extends \Wyomind\MassProductImport\Controller\Adminhtml\RulesAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Backend::system");
        $resultPage->getConfig()->getTitle()->prepend(__("Mass Product Import & Update"));
        $resultPage->addBreadcrumb(__('Manage Replacement Rules'), __('Manage Replacement Rules'));

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Wyomind\MassProductImport\Model\Rules');


        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                return $this->_resultRedirectFactory->create()->setPath('massproductimport/rules/index');
            }
        }

        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? (__('Modify Replacement Rule : ') . $model->getName()) : __('New Replacement rule'));

        $this->coreRegistry->register('rules', $model);

        return $resultPage;
    }
}
