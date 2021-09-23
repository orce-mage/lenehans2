<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Block\Product\View\Type;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\Image\ResourceModel\CollectionFactory;
use Amasty\GiftCard\Model\OptionSource\GiftCardOption;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Serialize\Serializer\Json;

class GiftCard extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Repository
     */
    private $imageRepository;

    /**
     * @var GiftCardOption
     */
    private $giftCardOption;

    /**
     * @var ListsInterface
     */
    private $localeLists;

    /**
     * @var CollectionFactory
     */
    protected $imageCollectionFactory;

    /**
     * @var FileUpload
     */
    protected $fileUpload;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

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
        array $data = []
    ) {
        parent::__construct($context, $arrayUtils, $data);
        $this->configProvider = $configProvider;
        $this->imageRepository = $imageRepository;
        $this->giftCardOption = $giftCardOption;
        $this->localeLists = $localeLists;
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->fileUpload = $fileUpload;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function isConfigured(Product $product): bool
    {
        if (!$product->getAmAllowOpenAmount() && !$product->getAmGiftcardPrices()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getImages(): string
    {
        $images = [];

        if ($productImagesId = $this->getProduct()->getAmGiftcardCodeImage()) {
            $productImagesId = explode(',', $productImagesId);
            $collection = $this->imageCollectionFactory->create()
                ->addFieldToFilter(ImageInterface::IMAGE_ID, ['in' => $productImagesId]);

            foreach ($collection->getItems() as $image) {
                try {
                    $images[] = [
                        'id' => $image->getImageId(),
                        'src' => $this->fileUpload->getImageUrl(
                            $image->getImagePath()
                        )
                    ];
                } catch (LocalizedException $e) {
                    null;
                }
            }
        }

        return $this->jsonSerializer->serialize($images);
    }

    /**
     * @return string
     */
    public function getAvailableOptions(): string
    {
        $options = [];
        $allFields = $this->giftCardOption->toArray();
        $fields = $this->configProvider->getGiftCardFields();

        foreach ($allFields as $value => $label) {
            if (in_array($value, $fields)) {
                $options[] = $value;
            }
        }

        return $this->jsonSerializer->serialize($options);
    }

    /**
     * @return string
     */
    public function getListTimezones(): string
    {
        $result = [];
        $allTimeZones = $this->localeLists->getOptionTimezones();
        $selectedTimeZones = $this->configProvider->getGiftCardTimezone();

        if (!$selectedTimeZones) {
            return $this->jsonSerializer->serialize($allTimeZones);
        }

        foreach ($allTimeZones as $timeZone) {
            if (in_array($timeZone['value'], $selectedTimeZones)) {
                $result[] = [
                    'value' => $timeZone['value'],
                    'label' => $timeZone['label']
                ];
            }
        }

        return $this->jsonSerializer->serialize($result);
    }

    /**
     * @return bool
     */
    public function getAllowUsersUploadImages(): bool
    {
        return $this->configProvider->isAllowUserImages();
    }

    /**
     * @return string
     */
    public function getTooltipContent(): string
    {
        return $this->configProvider->getImageUploadTooltip();
    }

    /**
     * @return string
     */
    public function getDefaultValues(): string
    {
        return $this->jsonSerializer->serialize($this->getProduct()->getPreconfiguredValues()->getData());
    }

    /**
     * @return string
     */
    public function getCustomImageUrl(): string
    {
        if ($img = $this->getProduct()->getPreconfiguredValues()->getData(GiftCardOptionInterface::CUSTOM_IMAGE)) {
            return $this->fileUpload->getTempImgUrl($img);
        }

        return '';
    }
}
