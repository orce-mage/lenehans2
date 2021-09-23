<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Pricing\Render;

use Amasty\GiftCard\Model\Config\Source\Fee;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Render\FinalPriceBox as RenderPrice;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;

class FinalPriceBox extends RenderPrice
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @var array
     */
    protected $minMaxPrice = [];

    /**
     * @var array
     */
    protected $amounts = [];

    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        array $data = [],
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );
        $this->initializeMinMaxPrice();
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Initialize min and max price of product
     * depending of allowed amounts
     */
    protected function initializeMinMaxPrice()
    {
        $min = $max = null;

        if ($this->isOpenAmount()) {
            $min = $this->getOpenAmountMin() ?: 1; //to show price 'from 1' on catalog
            $max = $this->getOpenAmountMax() ?: 0;
        }

        foreach ((array)$this->saleableItem->getAmGiftcardPrices() as $amount) {
            $min = $min === null ? $amount['value'] : min($min, $amount['value']);
            $max = $max === null ? $amount['value'] : max($max, $amount['value']);
        }
        $this->minMaxPrice = ['min' => (float)$min, 'max' => (float)$max];
    }

    /**
     * @return bool
     */
    public function isProductForm(): bool
    {
        return (bool)$this->getData('is_product_from');
    }

    /**
     * @return bool
     */
    public function isSinglePrice(): bool
    {
        return ($this->minMaxPrice['min'] && $this->minMaxPrice['max'])
            ? $this->minMaxPrice['min'] === $this->minMaxPrice['max']
            : false;
    }

    /**
     * @return string
     */
    public function getPredefinedAmounts(): string
    {
        if (!empty($this->amounts)) {
            return $this->jsonSerializer->serialize($this->amounts);
        }

        foreach ((array)$this->saleableItem->getAmGiftcardPrices() as $amount) {
            $this->amounts[] = [
                'value' => (float)$amount['value'],
                'convertValue' => $this->convertCurrency((float)$amount['value']),
                'price' => $this->convertAndFormatCurrency((float)$amount['value'], false)
            ];
        }

        return $this->jsonSerializer->serialize($this->amounts);
    }

    /**
     * @return float
     */
    public function getDefaultAmount(): float
    {
        $amountValue = 0.;

        if ($this->getSaleableItem()->hasPreconfiguredValues()) {
            $default = $this->getSaleableItem()->getPreconfiguredValues()->getData('am_giftcard_amount');

            if ($default) {
                $amountValue = $default;
            }
        }

        return (float)$amountValue;
    }

    /**
     * @return string|null
     */
    public function getDefaultOpenAmount()
    {
        return $this->getSaleableItem()->getPreconfiguredValues()->getData('am_giftcard_amount_custom');
    }

    /**
     * @return bool
     */
    public function isOpenAmount(): bool
    {
        return (bool)$this->saleableItem->getAmAllowOpenAmount();
    }

    /**
     * @return float
     */
    public function getOpenAmountMin(): float
    {
        return (float)$this->saleableItem->getAmOpenAmountMin();
    }

    /**
     * @return float
     */
    public function getOpenAmountMax(): float
    {
        return (float)$this->saleableItem->getAmOpenAmountMax();
    }

    /**
     * @return float
     */
    public function getMinPrice(): float
    {
        return $this->minMaxPrice['min'];
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        $currency = $this->priceCurrency->getCurrency();

        return $currency->getCurrencyCode() ?: $currency->getCurrencySymbol();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol(): string
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * @param float $amount
     * @param bool $includeContainer
     * @return string
     */
    public function convertAndFormatCurrency(float $amount, bool $includeContainer = true): string
    {
        return $this->priceCurrency->convertAndFormat($amount, $includeContainer);
    }

    /**
     * @param float $amount
     * @return float|null
     */
    public function convertCurrency($amount): float
    {
        return $this->priceCurrency->convert($amount);
    }

    /**
     * @return string
     */
    public function getCustomAmountDataValidation(): string
    {
        $result = [
            'number' => true,
            'required' => true
        ];
        if ($min = $this->getOpenAmountMin()) {
            $result['min'] = $this->escapeHtmlAttr($this->convertCurrency($min));
        }
        if ($max = $this->getOpenAmountMax()) {
            $result['max'] = $this->escapeHtmlAttr($this->convertCurrency($max));
        }

        return $this->jsonSerializer->serialize($result);
    }

    public function getFinalPrice($product)
    {
        $customValue = $this->getMinPrice();

        if ($product->getAmGiftcardFeeEnable()) {
            $feeType = $product->getAmGiftcardFeeType();
            $feeValue = $product->getAmGiftcardFeeValue();

            if ($feeType && $feeValue) {
                switch ($feeType) {
                    case Fee::PRICE_TYPE_PERCENT:
                        $customValue += $customValue * $feeValue / 100;
                        break;
                    case Fee::PRICE_TYPE_FIXED:
                        $customValue += $feeValue;
                        break;
                }
            }
        }

        return $customValue;
    }
}
