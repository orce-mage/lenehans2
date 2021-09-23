<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Adminhtml\History;


class MassAction extends \Amasty\Orderexport\Controller\Adminhtml\History
{
    public function execute()
    {

        $ids    = $this->getRequest()->getParam('entity_id');
        $action = $this->getRequest()->getParam('action');
        if ($ids && in_array($action, ['delete'])) {
            try {
                if ($action == 'delete') {
                    /**@var $collection \Amasty\Orderexport\Model\ResourceModel\History\Collection */
                    $collection = $this->_objectManager->create('Amasty\Orderexport\Model\ResourceModel\History\Collection');
                    $collection->addFieldToFilter('entity_id', ['in' => $ids]);
                    $collection->walk($action);
                    $message = __('Items were deleted successfully.');
                    $this->messageManager->addSuccess($message);
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete history entry(-ies) right now. Please review the log and try again.') . $e->getMessage()
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('*/*/');

                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find history entry(-ies) to delete.'));
        $this->_redirect('*/*/');
    }
}
