<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Controller\Map;

use Amasty\StorePickupWithLocator\Block\Location;
use Magento\Framework\Controller\Result\Json;

class Update extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $jsonEncoder;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\SerializerInterface $jsonEncoder
    ) {
        parent::__construct($context);
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Refresh map via ajax
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $storeListId = $this->getRequest()->getParam('storeListId');
        $mapId = $this->getRequest()->getParam('mapId');

        $this->_view->loadLayout();
        /** @var Location $block */
        $block = $this->_view->getLayout()
            ->getBlock('storepickup_locations')
            ->setMapId($mapId)
            ->setAmlocatorStoreList($storeListId);

        $locations = $block->getLocations();

        /** @var Json $jsonResponse */
        $jsonResponse = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $jsonResponse->setData($this->jsonEncoder->serialize($locations));

        return $jsonResponse;
    }
}
