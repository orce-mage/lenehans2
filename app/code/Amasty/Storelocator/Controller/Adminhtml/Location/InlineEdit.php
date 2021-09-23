<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

use Magento\Backend\App\Action\Context;
use Amasty\Storelocator\Model\LocationFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class InlineEdit
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * @var LocationFactory
     */
    private $locationFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * inlineEdit action constructor.
     *
     * @param Context $context
     * @param LocationFactory $locationFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        LocationFactory $locationFactory,
        Filter $filter
    ) {
        parent::__construct($context);
        $this->locationFactory = $locationFactory;
        $this->filter = $filter;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        foreach ($postItems as $locationId => $locationData) {
            /** @var \Amasty\Storelocator\Model\Location $location */
            $location = $this->locationFactory->create();
            $location->load($locationId);
            $location->setData('inlineEdit', true);
            try {
                $location->addData($locationData);
                $location->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorMessage($location, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorMessage($location, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorMessage(
                    $location,
                    __('Something went wrong while saving the location.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add location id to error message
     *
     * @param \Amasty\Storelocator\Model\Location $location
     * @param string $errorText
     * @return string
     */
    private function getErrorMessage($location, $errorText)
    {
        return '[Location ID: ' . $location->getId() . '] ' . $errorText;
    }
}
