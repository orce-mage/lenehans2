<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);


namespace Amasty\GiftCard\Pricing\Price;

use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;

/**
 * Final price model
 */
class FinalPrice extends CatalogFinalPrice
{
    /**
     * @return array
     */
    public function getAmounts(): array
    {
        $amountsCache = [];

        foreach ($this->product->getAmGiftcardPrices() as $amount) {
            $amountsCache[] = $this->priceCurrency->convertAndRound($amount['value']);
        }
        sort($amountsCache);

        return $amountsCache;
    }

    public function getValue()
    {
        $amount = $this->getAmounts();

        return count($amount) ? array_shift($amount) : false;
    }
}
