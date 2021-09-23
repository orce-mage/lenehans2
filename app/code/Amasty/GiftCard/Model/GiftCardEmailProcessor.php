<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model;

use Amasty\GiftCard\Api\Data\GiftCardEmailInterface;
use Amasty\GiftCard\Api\Data\GiftCardEmailInterfaceFactory;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Utils\EmailSender;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Model\StoreManagerInterface;

class GiftCardEmailProcessor
{
    /**
     * @var GiftCardEmailInterfaceFactory
     */
    private $cardEmailFactory;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var Repository
     */
    private $imageRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        GiftCardEmailInterfaceFactory $cardEmailFactory,
        EmailSender $emailSender,
        CurrencyInterface $localeCurrency,
        ConfigProvider $configProvider,
        FileUpload $fileUpload,
        Repository $imageRepository,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager
    ) {
        $this->cardEmailFactory = $cardEmailFactory;
        $this->emailSender = $emailSender;
        $this->localeCurrency = $localeCurrency;
        $this->configProvider = $configProvider;
        $this->fileUpload = $fileUpload;
        $this->imageRepository = $imageRepository;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @param OrderItemInterface $orderItem
     * @param array $codes
     * @param float $amount
     * @param string $expiredDate
     * @param int $store
     *
     * @throws \Zend_Currency_Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendGiftCardEmailsByOrderItem(
        OrderItemInterface $orderItem,
        array $codes,
        float $amount,
        string $expiredDate = null,
        int $store = 0
    ) {
        if (!$codes || !$orderItem->getProductOptionByCode(GiftCardOptionInterface::RECIPIENT_EMAIL)) {
            return;
        }
        $storeId = $store ?: (int)$orderItem->getStoreId();
        $isSenderConfirmation = $this->configProvider->isSendConfirmationToSender($storeId);
        $orderItemOptions = $orderItem->getProductOptions();
        $emailTemplate = $orderItemOptions[Attributes::EMAIL_TEMPLATE]
            ?? $this->configProvider->getEmailTemplate($storeId);
        $recipients[] = [
            $orderItemOptions[GiftCardOptionInterface::RECIPIENT_EMAIL],
            $orderItemOptions[GiftCardOptionInterface::RECIPIENT_NAME] ?? ''
        ];

        if ($sendCopyTo = $this->configProvider->getEmailRecipients($storeId)) {
            $recipients = array_merge($recipients, $sendCopyTo);
        }
        $baseCurrencyCode = $orderItem->getStore()
            ->getBaseCurrencyCode();
        $balance = $this->localeCurrency->getCurrency($baseCurrencyCode)
            ->toCurrency($amount);
        $sender = $this->configProvider->getEmailSender($storeId);

        /** @var GiftCardEmailInterface $cardEmail */
        $cardEmail = $this->cardEmailFactory->create();

        $cardEmail->setRecipientName($orderItemOptions[GiftCardOptionInterface::RECIPIENT_NAME] ?? '')
            ->setSenderName(
                $orderItemOptions[GiftCardOptionInterface::SENDER_NAME] ?? $orderItem->getOrder()->getCustomerName()
            )
            ->setSenderEmail(
                $orderItemOptions[GiftCardOptionInterface::SENDER_EMAIL] ?? $orderItem->getOrder()->getCustomerEmail()
            )
            ->setSenderMessage($orderItemOptions[GiftCardOptionInterface::MESSAGE] ?? '')
            ->setExpiredDate($expiredDate)
            ->setBalance($balance);

        foreach ($codes as $code) {
            $imageUrl = $this->prepareImageUrl((int)$orderItemOptions[GiftCardOptionInterface::IMAGE] ?? 0, $code);
            $imagePath = $this->fileUpload->getImagePathByUrl($imageUrl);

            $cardEmail->setGiftCode($code)->setImage($imageUrl);
            $this->emailSender->sendEmail(
                $recipients,
                $sender,
                $storeId,
                $emailTemplate,
                ['gcard_email' => $cardEmail],
                $imagePath
            );

            if ($isSenderConfirmation && isset($orderItemOptions[GiftCardOptionInterface::SENDER_EMAIL])) {
                $this->emailSender->sendEmail(
                    [$orderItemOptions[GiftCardOptionInterface::SENDER_EMAIL]],
                    $sender,
                    $storeId,
                    $this->configProvider->getSenderConfirmationTemplate($storeId),
                    ['gcard_email' => $cardEmail]
                );
            }
        }

        $this->eventManager->dispatch( //update account status for send codes
            'amasty_giftcard_generated_accounts_message_sent',
            ['codes' => $codes]
        );
    }

    /**
     * @param array $emailData
     * @param string $code
     * @param float $amount
     * @param string $expiredDate
     * @param int $storeId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function sendGiftCardEmailByData(
        array $emailData,
        string $code,
        float $amount,
        string $expiredDate = null,
        int $storeId = 0
    ) {
        $recipients[] = [
            $emailData[GiftCardOptionInterface::RECIPIENT_EMAIL],
            $emailData[GiftCardOptionInterface::RECIPIENT_NAME] ?? ''
        ];

        if ($sendCopyTo = $this->configProvider->getEmailRecipients($storeId)) {
            $recipients = array_merge($recipients, $sendCopyTo);
        }
        $baseCurrencyCode = $this->storeManager->getStore($storeId)
            ->getBaseCurrencyCode();
        $balance = $this->localeCurrency->getCurrency($baseCurrencyCode)
            ->toCurrency($amount);
        $imageUrl = $this->prepareImageUrl(
            (int)$emailData[GiftCardOptionInterface::IMAGE] ?? 0,
            $code
        );
        $imagePath = $this->fileUpload->getImagePathByUrl($imageUrl);

        /** @var GiftCardEmailInterface $cardEmail */
        $cardEmail = $this->cardEmailFactory->create();

        $cardEmail->setRecipientName($emailData[GiftCardOptionInterface::RECIPIENT_NAME] ?? '')
            ->setExpiredDate($expiredDate)
            ->setBalance($balance)
            ->setGiftCode($code)
            ->setImage($imageUrl);

        $this->emailSender->sendEmail(
            $recipients,
            $this->configProvider->getEmailSender($storeId),
            $storeId,
            $this->configProvider->getEmailTemplate($storeId),
            ['gcard_email' => $cardEmail],
            $imagePath
        );

        $this->eventManager->dispatch( //update account status for send codes
            'amasty_giftcard_generated_accounts_message_sent',
            ['codes' => [$code]]
        );
    }

    /**
     * @param OrderItemInterface $orderItem
     * @param string $code
     */
    public function sendExpirationEmail(OrderItemInterface $orderItem, string $code)
    {
        $storeId = (int)$orderItem->getStoreId();
        $orderItemOptions = $orderItem->getProductOptions();

        if (!isset($orderItemOptions[GiftCardOptionInterface::RECIPIENT_EMAIL])) {
            return; //printed cards don't have recipient email
        }
        $recipients[] = [
            $orderItemOptions[GiftCardOptionInterface::RECIPIENT_EMAIL],
            $orderItemOptions[GiftCardOptionInterface::RECIPIENT_NAME] ?? ''
        ];

        /** @var GiftCardEmailInterface $cardEmail */
        $cardEmail = $this->cardEmailFactory->create();

        $cardEmail->setRecipientName($orderItemOptions[GiftCardOptionInterface::RECIPIENT_NAME] ?? '')
            ->setGiftCode($code)
            ->setExpiryDays($this->configProvider->getNotifyExpiresDateDays($storeId));

        $this->emailSender->sendEmail(
            $recipients,
            $this->configProvider->getEmailSender($storeId),
            $storeId,
            $this->configProvider->getEmailExpirationTemplate($storeId),
            ['gcard_email' => $cardEmail]
        );
    }

    /**
     * @param int $imageId
     * @param string $code
     *
     * @return string
     */
    protected function prepareImageUrl(int $imageId, string $code): string
    {
        $imageUrl = '';

        if ($imageId) {
            try {
                $image = $this->imageRepository->getById($imageId);
                $imageUrl = $this->fileUpload->getEmailImageUrl($image, $code);
            } catch (LocalizedException $e) {
                $imageUrl = '';
            }
        }

        return $imageUrl;
    }
}
