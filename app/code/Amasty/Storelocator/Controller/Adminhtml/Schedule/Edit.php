<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

use Magento\Framework\Controller\ResultFactory;
use Amasty\Storelocator\Controller\Adminhtml\Schedule;

/**
 * Class Edit
 */
class Edit extends Schedule
{
    public function execute()
    {
        $scheduleId = (int)$this->getRequest()->getParam('id', 0);
        if ($scheduleId) {
            try {
                $model = $this->scheduleModel->load($scheduleId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage('The schedule no longer exists.');
                return $this->_redirect('*/*/');
            }
        } else {
            $model = $this->scheduleModel;
        }

        $title = $scheduleId ? __('Edit Location Schedule') : __('New Location Schedule');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->getConfig()->getTitle()->prepend($scheduleId ? $model->getName() : $title);

        return $resultPage;
    }
}
