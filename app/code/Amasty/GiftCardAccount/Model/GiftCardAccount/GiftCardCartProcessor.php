<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers\ReadHandler;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class GiftCardCartProcessor
{
    /**#@+
     * Constants for quote gift cards array
     */
    const GIFT_CARD_ID = 'id';
    const GIFT_CARD_CODE = 'code';
    const GIFT_CARD_AMOUNT = 'amount';
    const GIFT_CARD_BASE_AMOUNT = 'b_amount';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var GiftCardAccountValidator
     */
    private $accountValidator;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var ReadHandler
     */
    private $readHandler;

    public function __construct(
        Session $checkoutSession,
        GiftCardAccountValidator $accountValidator,
        CartRepositoryInterface $quoteRepository,
        ReadHandler $readHandler
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->accountValidator = $accountValidator;
        $this->quoteRepository = $quoteRepository;
        $this->readHandler = $readHandler;
    }

    /**
     * @param GiftCardAccountInterface $account
     * @param Quote|null $quote
     *
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function applyToCart(GiftCardAccountInterface $account, $quote = null)
    {
        if ($quote === null) {
            $quote = $this->checkoutSession->getQuote();
        }
        $canApplyForQuote = $this->accountValidator->canApplyForQuote($account, $quote);

        if (!$this->accountValidator->validateCode($account, $quote) || !$canApplyForQuote) {
            throw new LocalizedException(
                __(
                    'Coupon code "%1" cannot be applied to the cart because it does not meet certain conditions. 
                        Please check the details and try again or contact us for assistance.',
                    $account->getCodeModel()->getCode()
                )
            );
        }

        if (!$quote->getExtensionAttributes() || !$quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            $this->readHandler->loadAttributes($quote);
        }
        $cards = $quote->getExtensionAttributes()->getAmGiftcardQuote()->getGiftCards();

        foreach ($cards as $card) {
            if ($card[self::GIFT_CARD_ID] == $account->getAccountId()) {
                throw new LocalizedException(
                    __('This gift card account is already in the quote.')
                );
            }
        }
        $cards[] = [
            self::GIFT_CARD_ID => $account->getAccountId(),
            self::GIFT_CARD_CODE => $account->getCodeModel()->getCode(),
            self::GIFT_CARD_AMOUNT => $account->getCurrentValue(),
            self::GIFT_CARD_BASE_AMOUNT => $account->getCurrentValue(),
        ];
        $quote->getExtensionAttributes()->getAmGiftcardQuote()->setGiftCards($cards);

        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }

    /**
     * @param GiftCardAccountInterface $account
     * @param Quote|null $quote
     *
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function removeFromCart(GiftCardAccountInterface $account, $quote = null)
    {
        if ($quote === null) {
            $quote = $this->checkoutSession->getQuote();
        }
        $cards = [];

        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            $cards = $quote->getExtensionAttributes()->getAmGiftcardQuote()->getGiftCards();
        }

        foreach ($cards as $k => $card) {
            if ($card[self::GIFT_CARD_ID] == $account->getAccountId()) {
                unset($cards[$k]);
                $quote->getExtensionAttributes()->getAmGiftcardQuote()->setGiftCards($cards);
                $quote->collectTotals();
                $this->quoteRepository->save($quote);

                return;
            }
        }

        throw new LocalizedException(__('Gift Card account wasn\'t found in the quote'));
    }

    /**
     * @param Quote|null $quote
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function removeAllGiftCardsFromCart(Quote $quote = null)
    {
        if ($quote === null) {
            $quote = $this->checkoutSession->getQuote();
        }

        if (!$quote->getExtensionAttributes() || !$quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            return;
        }
        $quote->getExtensionAttributes()->getAmGiftcardQuote()->setGiftCards([]);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }
}
