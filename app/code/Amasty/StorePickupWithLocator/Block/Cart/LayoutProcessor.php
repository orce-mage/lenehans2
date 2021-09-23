<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Block\Cart;

use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\Config\Source\DisplayInfo;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;

/**
 * LayoutProcessor class add data for dropdown in shipping method 'storepickup'
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        RequestInterface $request,
        ConfigProvider $configProvider,
        CheckoutSession $checkoutSession
    ) {
        $this->request = $request;
        $this->configProvider = $configProvider;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function process($jsLayout)
    {
        $amStorePickup = false;

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step'])) {
            //checkout
            $shippingChildren = &$jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children'];
            $amStorePickup = &$shippingChildren['amstorepickup'];
            $this->removeInformationSection($shippingChildren);
        } elseif (isset($jsLayout['components']['block-summary']['children']['block-rates'])) {
            //cart
            $amStorePickup = &$jsLayout['components']['block-summary']['children']['block-rates']['children']
            ['amstorepickup'];
        }

        if ($amStorePickup) {
            $this->processDateLayout($amStorePickup);
            $this->processTimeLayout($amStorePickup);
        }

        return $jsLayout;
    }

    /**
     * @param array $amStorePickup
     */
    private function processDateLayout(&$amStorePickup)
    {
        if ($this->configProvider->isPickupDateEnabled()) {
            $amStorePickupDate = &$amStorePickup['children']['am_pickup_date'];
            $amStorePickupDate = [
                'component' => 'Amasty_StorePickupWithLocator/js/view/pickup/pickup-date',
                'label' => __('Pickup Date'),
                'template' => 'ui/form/field',
                'additionalClasses' => 'ampickup-field -date',
                'placeholder' => __('Choose a Pickup Date'),
                'dataScope' => 'am_pickup_date',
                'provider' => 'checkoutProvider',
                'config' => ['deps' => ['checkoutProvider']],
                'validation' => ['required-entry' => true]
            ];

            if ($this->configProvider->isSameDayAllowed()) {
                $amStorePickupDate['config']['sameDayPickupAllow'] = true;
                $amStorePickupDate['config']['sameDayCutoffTime'] = $this->configProvider->getSameDayCutOff();
            }

            if ($this->configProvider->isPickupTimeEnabled()) {
                $amStorePickupDate['config']['cartProductsDelay'] = $this->getTimeDelay();
            }
        }
    }

    /**
     * @param array $amStorePickup
     */
    private function processTimeLayout(&$amStorePickup)
    {
        if ($this->configProvider->isPickupDateEnabled() && $this->configProvider->isPickupTimeEnabled()) {
            $amStorePickupTime = &$amStorePickup['children']['am_pickup_time'];
            $amStorePickupTime = [
                'component' => 'Amasty_StorePickupWithLocator/js/view/pickup/pickup-time',
                'label' => __('Pickup Time'),
                'template' => 'ui/form/field',
                'additionalClasses' => 'ampickup-field -time',
                'placeholder' => __('Choose a Time Slot'),
                'dataScope' => 'am_pickup_time',
                'provider' => 'checkoutProvider',
                'validation' => ['required-entry' => true]
            ];
        }
    }

    /**
     * @return float
     */
    private function getTimeDelay()
    {
        /** @var Item $quoteItem */
        foreach ($this->checkoutSession->getQuote()->getItems() as $quoteItem) {
            if ($quoteItem->getBackorders()) {
                return $this->configProvider->getMinTimeBackorder();
            }
        }

        return $this->configProvider->getMinTimeOrder();
    }

    /**
     * @param $amPickupComponents
     */
    private function removeInformationSection(&$amPickupComponents)
    {
        switch ($this->configProvider->areaForShippingInfo()) {
            case DisplayInfo::SHIPPING_ADDRESS_AREA:
                unset($amPickupComponents['amstorepickup']['children']['am_pickup_store_details']);
                break;
            case DisplayInfo::SHIPPING_METHOD_AREA:
                unset($amPickupComponents['am_pickup_store_details']);
                $amPickupComponents['amstorepickup']['children']['am_pickup_store_details']['displayTitle'] =
                    $this->configProvider->areaForShippingInfo();
                break;
        }
    }
}
