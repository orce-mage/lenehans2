<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit
 */
class Edit extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    public function execute()
    {
        $locationId = (int)$this->getRequest()->getParam('id', 0);
        if ($locationId) {
            try {
                $model = $this->locationModel->load($locationId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage('The schedule no longer exists.');
                return $this->_redirect('*/*/');
            }
        } else {
            $model = $this->locationModel;
        }

        $this->coreRegistry->register('current_amasty_storelocator_location', $model);

        $title = $locationId ? __('Edit Location') : __('New Location');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->getConfig()->getTitle()->prepend($locationId ? $model->getName() : $title);

        return $resultPage;
    }

    public function _prepareForEdit(\Amasty\Storelocator\Model\Location $model)
    {
        /** @var \Amasty\Storelocator\Model\ResourceModel\Location $locationResource */
        $locationResource = $model->getResource();
        $model = $locationResource->setAttributesData($model);

        if ($model->getSchedule()) {
            $model->setSchedule($this->serializer->unserialize($model->getSchedule()));
        }
        $model->getActions()->setJsFormObject('rule_actions_fieldset');
        return true;
    }
}
