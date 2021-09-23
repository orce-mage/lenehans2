<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Model\GiftCard;

use Amasty\GiftCard\Api\Data\GiftCardEmailInterface;
use Amasty\GiftCard\Api\Data\GiftCardEmailInterfaceFactory;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Model\Config\Source\EmailTemplate;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\App\Area;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Store\Model\StoreManagerInterface;

class EmailPreviewProcessor
{
    /**
     * @var Repository
     */
    private $imageRepository;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var FactoryInterface
     */
    private $templateFactory;

    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @var GiftCardEmailInterfaceFactory
     */
    private $cardEmailFactory;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Repository $imageRepository,
        FileUpload $fileUpload,
        FactoryInterface $templateFactory,
        ConfigProvider $config,
        GiftCardEmailInterfaceFactory $cardEmailFactory,
        CurrencyInterface $localeCurrency,
        StoreManagerInterface $storeManager
    ) {
        $this->imageRepository = $imageRepository;
        $this->fileUpload = $fileUpload;
        $this->templateFactory = $templateFactory;
        $this->config = $config;
        $this->cardEmailFactory = $cardEmailFactory;
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $requestData
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function process(array $requestData): string
    {
        $variables = $this->getEmailVariables($requestData);

        try {
            $templateId = $this->config->getEmailTemplate();
            $template = $this->templateFactory->get($templateId);
        } catch (\Exception $e) {
            $template = $this->templateFactory->get(EmailTemplate::DEFAULT_EMAIL_TEMPLATE);
        } finally {
            $template = $this->applyVarsAndOptions($template, $variables);
            $result = $template->processTemplate();
        }

        return $result;
    }

    /**
     * @param array $requestData
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    protected function getEmailVariables(array $requestData): array
    {
        /** @var GiftCardEmailInterface $cardEmail */
        $cardEmail = $this->cardEmailFactory->create();
        $cardEmail->setBalance(__('XXX'))
            ->setGiftCode(__('GIFTCARDCODE'))
            ->setRecipientName(__('Gift Card Recipient Name'))
            ->setSenderName(__('Your Name'))
            ->setSenderEmail(__('Your Email'))
            ->setSenderMessage(__('Additional message for Gift Card'));

        if ($recipientName = $requestData[GiftCardOptionInterface::RECIPIENT_NAME] ?? null) {
            $cardEmail->setRecipientName($recipientName);
        }
        if ($senderName = $requestData[GiftCardOptionInterface::SENDER_NAME] ?? null) {
            $cardEmail->setSenderName($senderName);
        }
        if ($senderEmail = $requestData[GiftCardOptionInterface::SENDER_EMAIL] ?? null) {
            $cardEmail->setSenderEmail($senderEmail);
        }
        if ($senderMessage = $requestData[GiftCardOptionInterface::MESSAGE] ?? null) {
            $cardEmail->setSenderMessage($senderMessage);
        }
        $amount = $requestData[GiftCardOptionInterface::GIFTCARD_AMOUNT] ?? null;

        if (!$amount && isset($requestData[GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT])) {
            if ($initialValue = $requestData[GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT]) {
                $cardEmail->setBalance($this->formatAmount($initialValue));
            }
        } elseif ($amount) {
            $cardEmail->setBalance($this->formatAmount($amount));
        }
        $cardEmail->setImage($this->getPreviewImage($requestData));

        return ['gcard_email' => $cardEmail];
    }

    /**
     * @param array $requestData
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPreviewImage(array $requestData): string
    {
        $totalImage = '';
        $customImage = $requestData[GiftCardOptionInterface::CUSTOM_IMAGE] ?? null;

        if ($customImage) {
            if (is_array($customImage)) {
                $totalImage = $this->fileUpload->convertFileToBase64($customImage);
            } else {
                $totalImage = $customImage;
            }
        } elseif ($imageId = $requestData[GiftCardOptionInterface::IMAGE] ?? null) {
            $image = $this->imageRepository->getById($imageId);
            $totalImage = $this->fileUpload->getImageUrl($image->getImagePath());
        }

        return $totalImage;
    }

    /**
     * @param TemplateInterface $template
     * @param array $variables
     *
     * @return TemplateInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function applyVarsAndOptions(TemplateInterface $template, array $variables): TemplateInterface
    {
        /** TemplateInterface $template */
        return $template->setVars($variables)
            ->setOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ]
            );
    }

    /**
     * @param string $amount
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    private function formatAmount(string $amount): string
    {
        $baseCurrencyCode = $this->storeManager->getStore()
            ->getBaseCurrencyCode();

        return $this->localeCurrency->getCurrency($baseCurrencyCode)
            ->toCurrency($amount);
    }
}
