<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;

class QuoteMergeAfter implements ObserverInterface
{
    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    public function __construct(
        CartExtensionFactory $cartExtensionFactory
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getExtensionAttributes() && $source->getExtensionAttributes()->getAmGiftcardQuote()) {
            $gCardQuote = $source->getExtensionAttributes()->getAmGiftcardQuote();
            $gCardQuote->setQuoteId((int)$quote->getId());
            $extension = $quote->getExtensionAttributes();

            if ($extension === null) {
                $extension = $this->cartExtensionFactory->create();
            }
            $extension->setAmGiftcardQuote($gCardQuote);
            $quote->setExtensionAttributes($extension);
        }
    }
}
