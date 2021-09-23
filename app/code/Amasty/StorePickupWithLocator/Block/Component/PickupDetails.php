<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Block\Component;

use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Block\Cart\AbstractCart;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote\Item;

class PickupDetails extends AbstractCart
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CompositeConfigProvider
     */
    private $checkoutProvider;

    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ConfigProvider $configProvider,
        CompositeConfigProvider $checkoutProvider,
        Json $jsonSerializer,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->checkoutProvider = $checkoutProvider;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $amStorePickup = &$this->jsLayout['components']['amstorepickup'];

        if ($this->configProvider->isStorePickupEnabled()) {
            $this->processDateLayout($amStorePickup);
        } else {
            unset($amStorePickup);
        }

        return parent::getJsLayout();
    }

    /**
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->checkoutProvider->getConfig();
    }

    /**
     * @return false|string
     */
    public function getJsonCheckoutConfig()
    {
        return $this->jsonSerializer->serialize($this->getCheckoutConfig());
    }

    /**
     * @param array $amStorePickup
     */
    private function processDateLayout(&$amStorePickup)
    {
        if ($this->configProvider->isPickupDateEnabled()) {
            if ($this->configProvider->isSameDayAllowed()) {
                $amStorePickup['children']['am_pickup_date']['config']['sameDayPickupAllow'] = true;
                $amStorePickup['children']['am_pickup_date']['config']['sameDayCutoffTime']
                    = $this->configProvider->getSameDayCutOff();
            }
            $this->processTimeLayout($amStorePickup);
        } else {
            unset($amStorePickup['children']['am_pickup_date']);
        }
    }

    /**
     * @param array $amStorePickup
     */
    private function processTimeLayout(&$amStorePickup)
    {
        if ($this->configProvider->isPickupTimeEnabled()) {
            $amStorePickup['children']['am_pickup_date']['config']['cartProductsDelay'] = $this->getTimeDelay();
        } else {
            unset($amStorePickup['children']['am_pickup_time']);
        }
    }

    /**
     * @return float
     */
    private function getTimeDelay()
    {
        if ($this->isBackorder()) {
            return $this->configProvider->getMinTimeBackorder();
        }

        return $this->configProvider->getMinTimeOrder();
    }

    /**
     * @return bool
     */
    public function isBackorder()
    {
        if ($this->getData('is_backorder') === null) {
            $isBackorder = false;
            foreach ($this->_checkoutSession->getQuote()->getItems() as $quoteItem) {
                if ($quoteItem->getBackorders()) {
                    $isBackorder = true;
                    break;
                }
            }
            $this->setData('is_backorder', $isBackorder);
        }

        return $this->getData('is_backorder');
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeyInfo()
    {
        $keys = parent::getCacheKeyInfo();
        $keys[] = 'B-' . (int)$this->isBackorder();

        return $keys;
    }
}
