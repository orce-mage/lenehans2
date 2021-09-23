<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Checkout\Block\Onepage;

use Amasty\GiftCardAccount\Block\Checkout\LayoutProcessor as GiftLayoutProcessor;
use Amasty\GiftCardAccount\Model\Config\Source\CheckoutPosition;
use Amasty\GiftCardAccount\Model\ConfigProvider;
use Amasty\GiftCardAccount\Model\Stdlib\ArrayManager;

class LayoutProcessorPlugin
{
    const SIDEBAR_SUMMARY_ADDITIONAL = 'components/checkout/children/sidebar/children/summary_additional/children';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\Checkout\Model\Config|null
     */
    private $amastyOscConfig = null;

    public function __construct(
        ArrayManager $arrayManager,
        ConfigProvider $configProvider
    ) {
        $this->arrayManager = $arrayManager;
        $this->configProvider = $configProvider;

        if (class_exists(\Amasty\Checkout\Model\Config::class)) {
            $this->amastyOscConfig = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Amasty\Checkout\Model\Config::class);
        }
    }

    /**
     * To move GiftCardAccounts' coupon field to discounts summary area
     * We should move it after Amasty OSC layout processor execution
     * @see \Amasty\Checkout\Block\Onepage\LayoutProcessor::moveDiscountToReviewBlock
     *
     * @param \Amasty\Checkout\Block\Onepage\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(\Amasty\Checkout\Block\Onepage\LayoutProcessor $subject, array $jsLayout): array
    {
        if ($this->isAmastyOscEnabled()
            && $this->configProvider->getCouponCheckoutPosition() === CheckoutPosition::CHECKOUT_DISCOUNTS
        ) {
            $jsLayout = $this->arrayManager->move(
                GiftLayoutProcessor::PAYMENT_AFTER_METHODS . '/amgift-card',
                self::SIDEBAR_SUMMARY_ADDITIONAL . '/amgift-card',
                $jsLayout
            );
            $jsLayout = $this->arrayManager->set(
                GiftLayoutProcessor::PAYMENT_AFTER_METHODS . '/checked-gift-card-renderer/config/imports/cards',
                $jsLayout,
                '${ "checkout.sidebar.summary_additional.amgift-card" }:checkedCards'
            );
            $jsLayout = $this->arrayManager->move(
                GiftLayoutProcessor::PAYMENT_AFTER_METHODS . '/checked-gift-card-renderer',
                self::SIDEBAR_SUMMARY_ADDITIONAL . '/checked-gift-card-renderer',
                $jsLayout
            );
        }

        return $jsLayout;
    }

    /**
     * @return bool
     */
    private function isAmastyOscEnabled(): bool
    {
        return $this->amastyOscConfig
            ? $this->amastyOscConfig->isEnabled()
            : false;
    }
}
