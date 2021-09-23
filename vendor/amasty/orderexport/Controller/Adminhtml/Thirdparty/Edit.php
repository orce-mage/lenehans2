<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Controller\Adminhtml\Thirdparty;

class Edit extends \Amasty\Orderexport\Controller\Adminhtml\Thirdparty
{
    public function execute()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Amasty\Orderexport\Model\Thirdparty');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('amasty_orderexport/*');

                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_amasty_orderexport', $model);

        // set title and breadcrumbs
        $title      = $id ? __('Edit 3rd Party Link') : __('New 3rd Party Link');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('System'), __('System'))
                   ->addBreadcrumb(__('Manage 3rd Party Link'), __('Manage 3rd Party Link'));

        if (!empty($title)) {
            $resultPage->addBreadcrumb($title, $title);
        }

        $resultPage->getConfig()->getTitle()->prepend(__('3rd Party Link'));
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New 3rd Party Link'));

        $this->_view->renderLayout();
    }
}
