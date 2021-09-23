<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\GiftCardEmailProcessor;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Prepare email data from Gift Card Account and send email
 */
class EmailAccountProcessor
{
    /**
     * @var GiftCardEmailProcessor
     */
    private $giftCardEmailProcessor;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var DateTime
     */
    private $date;

    public function __construct(
        GiftCardEmailProcessor $giftCardEmailProcessor,
        OrderItemRepositoryInterface $orderItemRepository,
        DateTime $date
    ) {
        $this->giftCardEmailProcessor = $giftCardEmailProcessor;
        $this->orderItemRepository = $orderItemRepository;
        $this->date = $date;
    }

    /**
     * @param GiftCardAccountInterface $account
     * @param string $recipientName
     * @param string $recipientEmail
     * @param int $storeId
     *
     * @return bool true on successful email sending
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function sendGiftCardEmail(
        GiftCardAccountInterface $account,
        string $recipientName = null,
        string $recipientEmail = null,
        int $storeId = 0
    ): bool {
        if (!$recipientEmail && !$account->getOrderItemId()) {
            return false;
        }

        if ($account->getOrderItemId()) {
            try {
                $this->sendByOrderItem($account, $recipientName, $recipientEmail, $storeId);
                return true;
            } catch (\Exception $e) {
                null; //do nothing
            }
        }

        try {
            $this->sendByData($account, $recipientName, $recipientEmail, $storeId);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param GiftCardAccountInterface $account
     */
    public function sendExpirationEmail(GiftCardAccountInterface $account)
    {
        if (!$account->getOrderItemId()) {
            return;
        }
        $orderItem = $this->orderItemRepository->get($account->getOrderItemId());
        $code = $account->getCodeModel()->getCode();
        $this->giftCardEmailProcessor->sendExpirationEmail($orderItem, $code);
    }

    /**
     * @param GiftCardAccountInterface $account
     * @param string $recipientName
     * @param string $recipientEmail
     *
     * @param int $storeId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    private function sendByOrderItem(
        GiftCardAccountInterface $account,
        string $recipientName = null,
        string $recipientEmail = null,
        int $storeId = 0
    ) {
        $orderItem = $this->orderItemRepository->get($account->getOrderItemId());
        $productOptions = $orderItem->getProductOptions();
        $productOptions[GiftCardOptionInterface::RECIPIENT_NAME] =
            $recipientName ?: $productOptions[GiftCardOptionInterface::RECIPIENT_NAME] ?? '';
        $productOptions[GiftCardOptionInterface::RECIPIENT_EMAIL] =
            $recipientEmail ?: $productOptions[GiftCardOptionInterface::RECIPIENT_EMAIL] ?? '';
        $orderItem->setProductOptions($productOptions);

        $this->giftCardEmailProcessor->sendGiftCardEmailsByOrderItem(
            $orderItem,
            [$account->getCodeModel()->getCode()],
            $account->getInitialValue(),
            $account->getExpiredDate(),
            $storeId
        );
    }

    /**
     * @param GiftCardAccountInterface $account
     * @param string $recipientName
     * @param string $recipientEmail
     * @param int $storeId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    private function sendByData(
        GiftCardAccountInterface $account,
        string $recipientName,
        string $recipientEmail,
        int $storeId
    ) {
        $emailData = [
            GiftCardOptionInterface::RECIPIENT_NAME => $recipientName,
            GiftCardOptionInterface::RECIPIENT_EMAIL => $recipientEmail,
            GiftCardOptionInterface::IMAGE => $account->getImageId()
        ];
        $this->giftCardEmailProcessor->sendGiftCardEmailByData(
            $emailData,
            $account->getCodeModel()->getCode(),
            $account->getInitialValue(),
            $account->getExpiredDate(),
            $storeId
        );
    }
}
