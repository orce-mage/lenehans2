<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model;

use Amasty\GiftCard\Model\Code\Repository as CodeRepository;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CollectionFactory as CodePoolCollectionFactory;
use Amasty\GiftCard\Model\OptionSource\Status;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository as AccountRepository;
use Amasty\GiftCardAccount\Model\GiftCardExtension\GiftCardExtensionResolver;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

class RefundStrategy
{
    const KEY_AMOUNT = 1;
    const KEY_CODE = 0;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var GiftCardExtensionResolver
     */
    private $gCardExtensionResolver;

    /**
     * @var CodePoolCollectionFactory
     */
    private $codePoolCollectionFactory;

    /**
     * @var CodeRepository
     */
    private $codeRepository;

    public function __construct(
        AccountRepository $accountRepository,
        GiftCardExtensionResolver $gCardExtensionResolver,
        CodePoolCollectionFactory $codePoolCollectionFactory,
        CodeRepository $codeRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->gCardExtensionResolver = $gCardExtensionResolver;
        $this->codePoolCollectionFactory = $codePoolCollectionFactory;
        $this->codeRepository = $codeRepository;
    }

    /**
     * Process refunding to account. Returns list of processed accounts with code and refunded amount
     *
     * @param OrderInterface $order
     * @param float $totalAmount
     * @return array
     */
    public function refundToAccount(OrderInterface $order, float $totalAmount): array
    {
        $giftCards = $this->getAppliedGiftCards($order);
        $refundedCards = [];

        foreach ($giftCards as &$giftCard) {
            if ($totalAmount <= .0) {
                break;
            }
            $gCardAmount = $totalAmount >= $giftCard[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT]
                ? $giftCard[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT]
                : $totalAmount;
            $gCardAccount = $this->getAccount($giftCard, $order);

            if ($refunded = $this->restoreBalance($gCardAmount, $gCardAccount)) {
                $refundedCards[] = [
                    self::KEY_CODE => $gCardAccount->getCodeModel()->getCode(),
                    self::KEY_AMOUNT => $gCardAmount
                ];
                $totalAmount -= $refunded;
            }
        }
        $this->setAppliedGiftCards($order, $giftCards);

        return $refundedCards;
    }

    private function restoreBalance(float $amount, GiftCardAccountInterface $account): ?float
    {
        if ($account->getCurrentValue() === $account->getInitialValue()) {
            return null;
        }
        $refundAmount = $account->getCurrentValue() + $amount > $account->getInitialValue()
            ? $account->getInitialValue()
            : $account->getCurrentValue() + $amount;

        if ($account->getStatus() != AccountStatus::STATUS_ACTIVE) {
            $account->setStatus(AccountStatus::STATUS_ACTIVE);
        }
        $account->setCurrentValue($refundAmount);
        $this->accountRepository->save($account);

        return $refundAmount;
    }

    private function getAccount(array &$giftCard, OrderInterface $order): ?GiftCardAccountInterface
    {
        try {
            $account = $this->accountRepository->getByCode($giftCard[GiftCardCartProcessor::GIFT_CARD_CODE]);
        } catch (NoSuchEntityException $e) {
            $account = $this->createAccount($giftCard, $order);
        }
        $giftCard[GiftCardCartProcessor::GIFT_CARD_ID] = $account->getAccountId();

        return $account;
    }

    private function createAccount(array $giftCard, OrderInterface $order): GiftCardAccountInterface
    {
        if (!($codePoolId = $this->getFirstCodePoolId())) {
            throw new LocalizedException(__('No code pools found.'));
        }
        try {
            $code = $this->codeRepository->getByCode($giftCard[GiftCardCartProcessor::GIFT_CARD_CODE]);
        } catch (NoSuchEntityException $e) {
            $code = $this->codeRepository->getEmptyCodeModel()
                ->setCode($giftCard[GiftCardCartProcessor::GIFT_CARD_CODE])
                ->setCodePoolId($codePoolId)
                ->setStatus(Status::USED);
            $this->codeRepository->save($code);
        }

        /** @var GiftCardAccountInterface $account */
        $account = $this->accountRepository->getEmptyAccountModel()
            ->addData([
                GiftCardAccountInterface::STATUS => AccountStatus::STATUS_ACTIVE,
                GiftCardAccountInterface::WEBSITE_ID => (int)$order->getStore()->getWebsiteId(),
                GiftCardAccountInterface::INITIAL_VALUE => $giftCard[GiftCardCartProcessor::GIFT_CARD_AMOUNT],
                GiftCardAccountInterface::CURRENT_VALUE => 0,
                GiftCardAccountInterface::CODE_MODEL => $code,
                GiftCardAccountInterface::CODE_ID => $code->getCodeId(),
                GiftCardAccountInterface::IS_SENT => false
            ]);
        $this->accountRepository->save($account);

        return $account;
    }

    private function getFirstCodePoolId(): ?int
    {
        return $this->codePoolCollectionFactory->create()->getLastItem()->getCodePoolId();
    }

    private function getAppliedGiftCards(OrderInterface $order): array
    {
        if (!($giftCardOrder = $this->gCardExtensionResolver->resolve($order))) {
            return [];
        }

        return $giftCardOrder->getGiftCards();
    }

    private function setAppliedGiftCards(OrderInterface $order, array $giftCards): void
    {
        if (!($giftCardOrder = $this->gCardExtensionResolver->resolve($order))) {
            return;
        }
        $giftCardOrder->setGiftCards($giftCards);
    }
}
