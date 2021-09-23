<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Controller\Map;

use Amasty\StorePickupWithLocatorMSI\Block\Location as LocationBlock;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\DesignLoader;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Layout;

class Update implements ActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var Layout
     */
    private $layoutResult;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DesignLoader
     */
    private $designLoader;

    public function __construct(
        ResultFactory $resultFactory,
        Layout $layoutResult,
        RequestInterface $request,
        DesignLoader $designLoader
    ) {
        $this->resultFactory = $resultFactory;
        $this->layoutResult = $layoutResult;
        $this->request = $request;
        $this->designLoader = $designLoader;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $layout = $this->layoutResult->getLayout();
        $block = $this->getLocationsBlock($layout);
        $jsonLocations = '';

        if ($block) {
            $block->setDisablePickupButton(true);
            $jsonLocations = $block->getJsonLocations();
        }

        /** @var Json $jsonResponse */
        $jsonResponse = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResponse->setData($jsonLocations);

        return $jsonResponse;
    }

    /**
     * @param LayoutInterface $layout
     * @return LocationBlock|false
     */
    public function getLocationsBlock(LayoutInterface $layout)
    {
        $this->designLoader->load();
        $layout->getUpdate()->load(strtolower($this->request->getFullActionName()));
        $layout
            ->generateXml()
            ->generateElements();

        return $layout->getBlock('storepickupmsi_locations');
    }
}
