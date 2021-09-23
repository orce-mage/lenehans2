<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Controller\Map;

/**
 * Controller for generate map by click.
 */
class Open extends \Magento\Framework\App\Action\Action
{
    /**
     * Generate map by click 'choose on map' button.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $jsonResponse = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $this->_view->loadLayout();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $htmlMap = $this->_view->getLayout()->getBlock('storepickup_locations')->toHtml();

            $jsonResponse->setData($htmlMap);
        }

        return $jsonResponse;
    }
}
