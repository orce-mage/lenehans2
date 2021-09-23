<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\CartValidator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;

class ValidateCart implements ObserverInterface
{
    /**
     * @var CartValidator
     */
    private $cartValidator;

    public function __construct(
        CartValidator $cartValidator
    ) {
        $this->cartValidator = $cartValidator;
    }

    public function execute(Observer $observer)
    {
        /** @var CartInterface $quote */
        if ($observer->getEvent()->getName() === 'checkout_cart_save_after') {
            $quote = $observer->getEvent()->getCart()->getQuote();
        } else {
            $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
        }
        $this->cartValidator->validate($quote);
    }
}
