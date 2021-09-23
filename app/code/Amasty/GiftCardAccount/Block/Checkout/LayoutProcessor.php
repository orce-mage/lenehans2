<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Checkout;

use Amasty\GiftCardAccount\Model\Config\Source\CheckoutViewType;
use Amasty\GiftCardAccount\Model\ConfigProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Amasty\GiftCardAccount\Model\Stdlib\ArrayManager;

class LayoutProcessor implements LayoutProcessorInterface
{
    const PAYMENT_AFTER_METHODS = 'components/checkout/children/steps/children/billing-step/children/' .
        'payment/children/afterMethods/children';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ArrayManager $arrayManager,
        ConfigProvider $configProvider
    ) {
        $this->arrayManager = $arrayManager;
        $this->configProvider = $configProvider;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        return $this->changeGiftCardComponentTemplate($jsLayout);
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    private function changeGiftCardComponentTemplate(array $jsLayout): array
    {
        if ($this->configProvider->getCouponCheckoutView() === CheckoutViewType::DROPDOWN) {
            $jsLayout = $this->arrayManager->set(
                self::PAYMENT_AFTER_METHODS . '/amgift-card/config/template',
                $jsLayout,
                'Amasty_GiftCardAccount/payment/gift-card-dropdown'
            );
        }

        return $jsLayout;
    }
}
