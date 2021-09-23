<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Block\Adminhtml\Catalog\Product\Composite\Fieldset;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Model\Config\Source\GiftCardType;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\Image\ResourceModel\CollectionFactory;
use Amasty\GiftCard\Model\OptionSource\GiftCardOption;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Giftcard extends \Amasty\GiftCard\Block\Product\View\Type\GiftCard
{
    /**
     * @var array
     */
    protected $availableOptions = [];

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $productHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var GiftCardType
     */
    private $giftCardType;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        ConfigProvider $configProvider,
        Repository $imageRepository,
        GiftCardOption $giftCardOption,
        ListsInterface $localeLists,
        CollectionFactory $imageCollectionFactory,
        FileUpload $fileUpload,
        Json $jsonSerializer,
        \Magento\Catalog\Helper\Product $productHelper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        GiftCardType $giftCardType,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $configProvider,
            $imageRepository,
            $giftCardOption,
            $localeLists,
            $imageCollectionFactory,
            $fileUpload,
            $jsonSerializer,
            $data
        );
        $this->availableOptions = $jsonSerializer->unserialize($this->getAvailableOptions());
        $this->productHelper = $productHelper;
        $this->priceCurrency = $priceCurrency;
        $this->pricingHelper = $pricingHelper;
        $this->giftCardType = $giftCardType;
    }

    /**
     * Checks whether block is last fieldset in popup
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsLastFieldset(): bool
    {
        if ($this->hasData('is_last_fieldset')) {
            return $this->getData('is_last_fieldset');
        } else {
            return !$this->getProduct()->getOptions();
        }
    }

    /**
     * Get current currency code
     *
     * @param int $storeId $storeId
     *
     * @return string
     * @codeCoverageIgnore
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrencyCode(int $storeId): string
    {
        return $this->_storeManager->getStore($storeId)->getCurrentCurrencyCode();
    }

    /**
     * @return bool
     */
    public function isSkipSaleableCheck(): bool
    {
        return $this->productHelper->getSkipSaleableCheck();
    }

    /**
     * @return array
     */
    public function getGiftCardTypes(): array
    {
        return $this->giftCardType->getAllOptions();
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getAmounts(ProductInterface $product): array
    {
        $result = [];

        foreach ($product->getAmGiftcardPrices() as $amount) {
            $result[] = $this->priceCurrency->round($amount[\Amasty\GiftCard\Api\Data\GiftCardPriceInterface::VALUE]);
        }
        sort($result);

        return $result;
    }

    /**
     * @param float $amount
     * @param int $storeId
     * @param bool $format
     * @param bool $includeContainer
     *
     * @return float|string
     */
    public function getCurrencyByStore($amount, $storeId, $format = true, $includeContainer = true)
    {
        return $this->pricingHelper->currencyByStore($amount, $storeId, true, false);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getDefaultValue(string $key): string
    {
        return (string)$this->getProduct()->getPreconfiguredValues()->getData($key);
    }

    /**
     * @return array
     */
    public function getImagesArray(): array
    {
        $images = [];

        if ($productImagesId = $this->getProduct()->getAmGiftcardCodeImage()) {
            $productImagesId = explode(',', $productImagesId);
            $collection = $this->imageCollectionFactory->create()
                ->addFieldToFilter(ImageInterface::IMAGE_ID, ['in' => $productImagesId]);

            foreach ($collection->getItems() as $image) {
                $images[] = [
                    'id' => $image->getImageId(),
                    'name' => __($image->getTitle())
                ];
            }
        }
        if ($this->getDefaultValue(GiftCardOptionInterface::IMAGE)
            == \Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard::CUSTOM_IMAGE_PARAM
        ) {
            $images[] = [
                'id' => 'custom',
                'name' => __('Client Image')
            ];
        }

        return $images;
    }

    /**
     * @return array
     */
    public function getListTimezonesArray(): array
    {
        return $this->jsonSerializer->unserialize($this->getListTimezones());
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isOptionAvailable(string $key): bool
    {
        return in_array($key, $this->availableOptions);
    }
}
