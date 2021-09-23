<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Attributes;

/**
 * Class Edit
 */
class Edit extends \Amasty\Storelocator\Controller\Adminhtml\Attributes
{

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $model = $this->attributeFactory->create();

        if ($id) {
            $this->attributeResourceModel->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                return $this->_redirect('amasty_storelocator/*');
            }
        }
        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->coreRegistry->register('current_amasty_storelocator_attribute', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Storelocator::attributes');

        $title = $model->getId() ? __('Edit Attribute') . ' ' . $model->getFrontendLabel() : __('New Attribute');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $resultPage->addBreadcrumb($title, $title);

        return $resultPage;
    }
}
