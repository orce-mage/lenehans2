<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Edit
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Edit extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

    /**
     * @var string
     */
    public $name = "Mass Stock Update";


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Backend::system_convert");
        $resultPage->addBreadcrumb(__($this->name), __($this->name));
        $resultPage->addBreadcrumb(__('Manage Profiles'), __('Manage Profiles'));

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');


        if ($id) {
            $model->load($id);
            $this->_coreRegistry->register('current_entity_id', $model->getId());
            if (!$model->getId()) {
                $this->messageManager->addError(__('This profileno longer exists.'));
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/index');
            }
        }

        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? (__('Modify profile : ') . $model->getName()) : __('New profile'));

        $this->_coreRegistry->register('profile', $model);

        return $resultPage;
    }
}
