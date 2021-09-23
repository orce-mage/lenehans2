<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCardAccount\Api\GiftCardAccountManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;

class GiftCardAccountManagement implements GiftCardAccountManagementInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var GiftCardCartProcessor
     */
    private $gCardCartProcessor;

    public function __construct(
        Repository $accountRepository,
        CartRepositoryInterface $quoteRepository,
        GiftCardCartProcessor $gCardCartProcessor
    ) {
        $this->accountRepository = $accountRepository;
        $this->quoteRepository = $quoteRepository;
        $this->gCardCartProcessor = $gCardCartProcessor;
    }

    public function removeGiftCardFromCart($cartId, string $giftCardCode): string
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$quote->getItemsCount()) {
            throw new CouldNotDeleteException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }

        try {
            $giftCard = $this->accountRepository->getByCode($giftCardCode);
            $this->gCardCartProcessor->removeFromCart($giftCard, $quote);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__("The gift card couldn't be deleted from the quote."));
        }

        return $giftCardCode;
    }

    public function applyGiftCardToCart($cartId, string $giftCardCode): string
    {
        $giftCardCode = trim($giftCardCode);

        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$quote->getItemsCount()) {
            throw new CouldNotSaveException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }

        try {
            $giftCard = $this->accountRepository->getByCode($giftCardCode);
            $this->gCardCartProcessor->applyToCart($giftCard, $quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $giftCardCode;
    }
}
