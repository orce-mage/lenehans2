<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Helper\Catalog\Product;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\OptionSource\GiftCardOption;
use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Escaper;

class Configuration implements ConfigurationInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GiftCardOption
     */
    private $giftCardOption;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    private $productConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        ConfigProvider $configProvider,
        GiftCardOption $giftCardOption,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        Escaper $escaper
    ) {
        $this->configProvider = $configProvider;
        $this->giftCardOption = $giftCardOption;
        $this->productConfig = $productConfig;
        $this->priceCurrency = $priceCurrency;
        $this->escaper = $escaper;
    }

    /**
     * @param ItemInterface $item
     *
     * @return array
     */
    public function getGiftCardOptions(ItemInterface $item): array
    {
        $result = [];
        if (!$this->configProvider->isEnabled() || !$this->configProvider->isShowOptionsInCartAndCheckout()) {
            return $result;
        }
        $allDisplayOptions = $this->giftCardOption->getAllDisplayOptions();

        foreach ($item->getProduct()->getCustomOptions() as $customOption) {
            if (!isset($allDisplayOptions[$customOption->getCode()])) {
                continue;
            }
            $value = $this->escaper->escapeHtml($customOption->getValue());

            if ($customOption->getCode() === GiftCardOptionInterface::GIFTCARD_AMOUNT) {
                $value = $this->priceCurrency->convertAndFormat($customOption->getValue(), false);
            }

            if (!$value) {
                continue;
            }
            $result[] = [
                'label' => $allDisplayOptions[$customOption->getCode()],
                'value' => $value
            ];
        }

        return $result;
    }

    /**
     * @param ItemInterface $item
     *
     * @return array
     */
    public function getOptions(ItemInterface $item)
    {
        return array_merge(
            $this->productConfig->getCustomOptions($item),
            $this->getGiftcardOptions($item)
        );
    }
}
