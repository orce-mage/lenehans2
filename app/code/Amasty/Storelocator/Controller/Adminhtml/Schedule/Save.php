<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

/**
 * Class Save
 */
class Save extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                $scheduleId = (int)$this->getRequest()->getParam('id');
                if ($scheduleId) {
                    $model = $this->scheduleModel->load($scheduleId);
                    if ($scheduleId != $model->getId()) {
                        $this->messageManager->addErrorMessage(__('The wrong item is specified.'));
                        $this->_redirect('*/*/');

                        return;
                    }
                }
                if (is_array($data['schedule'])) {
                    $this->scheduleModel->setSchedule($this->serializer->serialize($data['schedule']));
                }
                $this->scheduleModel->setName($this->getRequest()->getParam('name'));
                $this->scheduleModel->save();
                $this->messageManager->addSuccessMessage(__('You saved the schedule.'));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $this->scheduleModel->getId()]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->messageManager->addErrorMessage(
                    __($errorMessage)
                );
                $this->logger->critical($e);
                $this->sessionModel->setPageData($data);
                return;
            }
        }
        $this->_redirect('*/*/');
    }
}
