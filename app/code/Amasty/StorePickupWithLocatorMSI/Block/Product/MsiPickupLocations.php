<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Block\Product;

use Amasty\StorePickupWithLocator\Model\ConfigProvider as StorePickupWithLocatorConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class MsiPickupLocations extends \Magento\Framework\View\Element\Template
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StorePickupWithLocatorConfigProvider
     */
    private $pickupLocatorConfigProvider;

    public function __construct(
        Template\Context $context,
        StoreManagerInterface $storeManager,
        StorePickupWithLocatorConfigProvider $pickupLocatorConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->pickupLocatorConfigProvider = $pickupLocatorConfigProvider;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return (int)$this->getRequest()->getParam('id');
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        $this->jsLayout['components']['ampickupmsi.locations']['storeCode'] = $storeCode;
        $this->jsLayout['components']['ampickupmsi.locations']['curbsideLabelEnabled']
            = $this->pickupLocatorConfigProvider->isCurbsideLabelsEnabled();
        $this->jsLayout['components']['ampickupmsi.locations']['curbsideLabel']
            = $this->pickupLocatorConfigProvider->getCurbsideLabelText();

        return parent::getJsLayout();
    }
}
