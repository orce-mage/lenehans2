<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

/**
 * Class Duplicate
 */
class Duplicate extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('location_id');
        if (!$id) {
            $this->messageManager->addErrorMessage(__('Please select a location to duplicate.'));
            return $this->_redirect('*/*');
        }
        try {
            $model = $this->locationModel->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
            $location = clone $model;
            $location->setStatus(0);
            $location->setId(null);
            $location->save();
            $this->messageManager->addSuccessMessage(__('The location has been duplicated. Please feel free to activate it.'));
            return $this->_redirect('*/*/edit', ['id' => $location->getId()]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the item data. Please review the error log.')
            );
            $this->logger->critical($e);
            $this->_redirect('*/*');
            return;
        }
    }
}
