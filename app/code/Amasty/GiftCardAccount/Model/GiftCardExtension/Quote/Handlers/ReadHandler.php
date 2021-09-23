<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;

class ReadHandler
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    public function __construct(
        Repository $repository,
        CartExtensionFactory $cartExtensionFactory
    ) {
        $this->repository = $repository;
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * @param CartInterface $quote
     *
     * @return CartInterface
     */
    public function loadAttributes(CartInterface $quote): CartInterface
    {
        $extension = $quote->getExtensionAttributes();

        if ($extension === null) {
            $extension = $this->cartExtensionFactory->create();
        } elseif ($quote->getExtensionAttributes()->getAmGiftcardQuote() !== null) {
            return $quote;
        }
        $quoteId = (int)$quote->getId();

        try {
            $giftCardQuote = $this->repository->getByQuoteId($quoteId);
        } catch (NoSuchEntityException $e) {
            $giftCardQuote = $this->repository->getEmptyQuoteModel();
            $giftCardQuote->setQuoteId($quoteId);
        }
        $extension->setAmGiftcardQuote($giftCardQuote);
        $quote->setExtensionAttributes($extension);

        return $quote;
    }
}
