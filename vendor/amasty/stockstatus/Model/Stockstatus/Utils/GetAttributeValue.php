<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Utils;

use Amasty\Stockstatus\Model\Stockstatus\Formatter;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class GetAttributeValue
{
    /**
     * @var FormatDate
     */
    private $formatDate;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        FormatDate $formatDate,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->formatDate = $formatDate;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     * @return float|string
     */
    public function execute(Product $product, string $attributeCode)
    {
        $result = '';
        if ($value = $product->getData($attributeCode)) {
            $attribute = $product->getResource()->getAttribute($attributeCode);
            if ($attribute && $attribute->usesSource()) {
                $result = $attribute->getSource()->getOptionText($value);
            } elseif (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $value)) {
                $result = $this->formatDate->format($value, Formatter::DEFAULT_DATE_FORMAT);
            } elseif ($attribute->getFrontendInput() == 'price') {
                $result = $this->priceCurrency->format($value, false);
            } else {
                $result = $value;
            }
        }

        return $result;
    }
}
