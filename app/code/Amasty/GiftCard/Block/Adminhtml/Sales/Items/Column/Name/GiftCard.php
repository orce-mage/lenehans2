<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Block\Adminhtml\Sales\Items\Column\Name;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Model\Config\Source\GiftCardType;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class GiftCard extends \Magento\Sales\Block\Adminhtml\Items\Column\Name
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Repository
     */
    private $imageRepository;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $dataHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        PriceCurrencyInterface $priceCurrency,
        Repository $imageRepository,
        FileUpload $fileUpload,
        \Magento\Catalog\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
        $this->priceCurrency = $priceCurrency;
        $this->imageRepository = $imageRepository;
        $this->fileUpload = $fileUpload;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    protected function prepareCustomOption(string $code): string
    {
        if ($option = $this->getItem()->getProductOptionByCode($code)) {
            return $this->escapeHtml($option);
        }

        return '';
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getGiftcardOptions(): array
    {
        $result = [];

        if ($value = $this->prepareCustomOption(GiftCardOptionInterface::GIFTCARD_AMOUNT)) {
            $result[] = [
                'label' => __('Card Value'),
                'value' => $this->priceCurrency->format(
                    $value,
                    false,
                    \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                    $this->getOrder()->getStore(),
                    $this->getOrder()->getStore()->getBaseCurrency()
                )
            ];
        }
        $gCardType = $this->prepareCustomOption(GiftCardOptionInterface::GIFTCARD_TYPE);

        if ($gCardType) {
            switch ($gCardType) {
                case GiftCardType::TYPE_VIRTUAL:
                    $value = __('Virtual');
                    break;
                case GiftCardType::TYPE_PRINTED:
                    $value = __('Printed');
                    break;
                case GiftCardType::TYPE_COMBINED:
                    $value = __('Combined');
                    break;
                default:
                    $value = '';
            }
            $result[] = [
                'label' => __('Card Type'),
                'value' => $value
            ];
        }

        if ($value = (int)$this->prepareCustomOption(GiftCardOptionInterface::IMAGE)) {
            try {
                $image = $this->imageRepository->getById($value);

                if ($image->getImageId()) {
                    $src = $this->escapeUrl(
                        $this->fileUpload->getImageUrl(
                            $image->getImagePath(),
                            (bool)$image->isUserUpload()
                        )
                    );
                    $title = $this->escapeHtml(__('Image Id %1', $image->getImageId()));

                    $result[] = [
                        'label' => __('Gift Card Image'),
                        'value' => '<img src="' . $src . '"  width="270px;" title="' . $title . '"/>',
                        'custom_view' => true
                    ];
                }
            } catch (NoSuchEntityException $e) {
                $result[] = [
                    'label' => __('Gift Card Image'),
                    'value' => __('Deleted')
                ];
            }
        }

        if ($value = $this->prepareCustomOption(GiftCardOptionInterface::SENDER_NAME)) {
            $email = $this->prepareCustomOption(GiftCardOptionInterface::SENDER_EMAIL)
                ?: $this->getItem()->getOrder()->getCustomerEmail();

            if ($email) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = [
                'label' => __('Gift Card Sender'),
                'value' => $value
            ];
        }

        if (($value = $this->prepareCustomOption(GiftCardOptionInterface::RECIPIENT_NAME))
            && $gCardType != GiftCardType::TYPE_PRINTED
        ) {
            $email = $this->prepareCustomOption(GiftCardOptionInterface::RECIPIENT_EMAIL);

            if ($email) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = [
                'label' => __('Gift Card Recipient'),
                'value' => $value
            ];
        }

        if ($value = $this->prepareCustomOption(GiftCardOptionInterface::MESSAGE)) {
            $result[] = [
                'label' => __('Gift Card Message'),
                'value' => $value
            ];
        }

        if ($value = $this->prepareCustomOption(Attributes::GIFTCARD_LIFETIME)) {
            $result[] = [
                'label'=> __('Gift Card Lifetime'),
                'value'=> __('%1 days', $value),
            ];
        }

        if ($value = $this->prepareCustomOption(GiftCardOptionInterface::DELIVERY_DATE)) {
            $result[] = [
                'label'=> __('Date of Certificate Delivery'),
                'value'=> $this->formatDate($value, \IntlDateFormatter::SHORT, true),
            ];
        }

        if ($value = $this->prepareCustomOption(GiftCardOptionInterface::DELIVERY_TIMEZONE)) {
            $result[] = [
                'label'=> __('Delivery Timezone'),
                'value'=> $value,
            ];
        }
        $createdCodes = 0;
        $totalCodes = $this->getItem()->getQtyOrdered();
        $codes = (array)$this->getItem()->getProductOptionByCode(GiftCardOptionInterface::GIFTCARD_CREATED_CODES);

        if ($codes) {
            $createdCodes = count($codes);
        }

        for ($i = $createdCodes; $i < $totalCodes; $i++) {
            $codes[] = __('N/A');
        }

        $result[] = [
            'label' => __('Gift Card Accounts'),
            'value' => implode('<br />', $codes),
            'custom_view' => true
        ];

        return $result;
    }

    /**
     * Return gift card and custom options array
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderOptions()
    {
        return array_merge($this->getGiftcardOptions(), parent::getOrderOptions());
    }

    /**
     * @return string
     */
    public function getSkuBlock()
    {
        return implode('<br />', $this->dataHelper->splitSku($this->escapeHtml($this->getSku())));
    }
}
