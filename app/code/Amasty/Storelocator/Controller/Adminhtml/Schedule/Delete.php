<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

/**
 * Class Delete
 */
class Delete extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    public function execute()
    {
        $scheduleId = (int)$this->getRequest()->getParam('id');
        if ($scheduleId) {
            try {
                $model = $this->scheduleModel->load($scheduleId);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the schedule.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete schedule right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('*/*/edit', ['id' => (int)$this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a schedule to delete.'));
        $this->_redirect('*/*/');
    }
}
