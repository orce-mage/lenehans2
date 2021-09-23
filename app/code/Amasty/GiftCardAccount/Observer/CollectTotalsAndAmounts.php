<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CollectTotalsAndAmounts implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();
        $salesModelExtension = $cart->getSalesModel()->getDataUsingMethod('extension_attributes');

        if (!$salesModelExtension) {
            return;
        }
        switch (true) {
            case $salesModelExtension instanceof \Magento\Sales\Api\Data\OrderExtension:
                $value = $salesModelExtension->getAmGiftcardOrder()->getBaseGiftAmount();
                break;
            case $salesModelExtension instanceof \Magento\Quote\Api\Data\CartExtension:
                $value = $salesModelExtension->getAmGiftcardQuote()->getBaseGiftAmountUsed();
                break;
            default:
                $value = 0;
        }

        if ($value > 0) {
            $cart->addDiscount((float)$value);
        }
    }
}
