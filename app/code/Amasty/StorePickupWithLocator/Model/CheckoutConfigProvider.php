<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Quote|null
     */
    private $quote = null;

    public function __construct(
        CheckoutSession $checkoutSession,
        ConfigProvider $configProvider
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return [
            'amastyStorePickupConfig' => [
                'websiteId' => $this->getQuote()->getStore()->getWebsiteId(),
                'storeId' => $this->getQuote()->getStore()->getStoreId(),
                'curbsideConfig' => $this->getCurbsideConfig()
            ]
        ];
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    private function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * @return array
     */
    private function getCurbsideConfig(): array
    {
        $storeId = $this->getQuote()->getStore()->getStoreId();
        return [
            'checkbox_enabled' => $this->configProvider->isCurbsideCheckboxEnabled($storeId),
            'checkbox_label' => $this->configProvider->getCurbsideCheckboxLabel($storeId),
            'display_curbside_conditions' => $this->configProvider->isDisplayCurbsideConditions($storeId),
            'conditions_label' => $this->configProvider->getCurbsideConditionsLabel($storeId),
            'comments_enabled' => $this->configProvider->isCurbsideCommentsEnabled($storeId),
            'comment_placeholder' => $this->configProvider->getCurbsideCommentPlaceholder($storeId),
            'comment_field_required' => $this->configProvider->isCurbsideCommentRequired($storeId),
            'labels_enabled' => $this->configProvider->isCurbsideLabelsEnabled($storeId),
            'label_text' => $this->configProvider->getCurbsideLabelText($storeId)
        ];
    }
}
