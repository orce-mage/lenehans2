<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

/**
 * Class MassAction
 */
class MassAction extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    public function execute()
    {
        /** @var \Magento\Ui\Component\MassAction\Filter $filter */
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /**
         * @var $collection \Amasty\Storelocator\Model\ResourceModel\Location\Collection
         */
        $collection = $this->filter->getCollection($this->locationCollection);

        $collectionSize = $collection->getSize();
        $action = $this->getRequest()->getParam('action');
        if ($collectionSize && in_array($action, ['activate', 'inactivate', 'delete'])) {
            try {
                $collection->walk($action);
                if ($action == 'delete') {
                    $this->messageManager->addSuccessMessage(__('You deleted the location(s).'));
                } else {
                    $this->messageManager->addSuccessMessage(__('You changed the location(s).'));
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete location(s) right now. Please review the log and try again.').$e->getMessage()
                );
                $this->logger->critical($e);
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a location(s) to delete.'));
        $this->_redirect('*/*/');
    }
}
