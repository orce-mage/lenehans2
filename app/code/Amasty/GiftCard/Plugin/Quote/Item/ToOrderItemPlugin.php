<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Plugin\Quote\Item;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCard\Model\OptionSource\GiftCardOption;
use Amasty\GiftCard\Model\OptionSource\ImageStatus;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Model\Order\Item;

/**
 * Conver product options to order item options
 * process custom images
 */
class ToOrderItemPlugin
{
    const CUSTOM_IMAGE_TITLE = 'User Image';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GiftCardOption
     */
    private $giftCardOption;

    /**
     * @var Repository
     */
    private $imageRepository;

    public function __construct(
        ConfigProvider $configProvider,
        GiftCardOption $giftCardOption,
        Repository $imageRepository
    ) {
        $this->configProvider = $configProvider;
        $this->giftCardOption = $giftCardOption;
        $this->imageRepository = $imageRepository;
    }

    /**
     * @param ToOrderItem $subject
     * @param Item $orderItem
     * @param AbstractItem $quoteItem
     * @param array $data
     *
     * @return Item
     */
    public function afterConvert(
        ToOrderItem $subject,
        Item $orderItem,
        AbstractItem $quoteItem,
        array $data = []
    ): Item {
        $productOptions = $orderItem->getProductOptions();
        $product = $quoteItem->getProduct();

        if ($product->getTypeId() != GiftCard::TYPE_AMGIFTCARD) {
            return $orderItem;
        }

        foreach ($this->giftCardOption->getOrderOptionsKeys() as $optionKey) {
            if ($option = $product->getCustomOption($optionKey)) {
                if ($optionKey == GiftCardOptionInterface::IMAGE
                    && $product->getCustomOption(GiftCardOptionInterface::CUSTOM_IMAGE)
                ) {
                    $this->processCustomImage(
                        $option,
                        $product->getCustomOption(GiftCardOptionInterface::CUSTOM_IMAGE)->getValue()
                    );
                }
                $productOptions[$optionKey] = $option->getValue();
            }
        }
        $productOptions[Attributes::GIFTCARD_LIFETIME] =
            $product->getAmGiftcardLifetime() == Attributes::ATTRIBUTE_CONFIG_VALUE
                ? $this->configProvider->getLifetime()
                : $product->getAmGiftcardLifetime();
        $productOptions[Attributes::EMAIL_TEMPLATE] =
            $product->getAmEmailTemplate() == Attributes::ATTRIBUTE_CONFIG_VALUE
                ? $this->configProvider->getEmailTemplate()
                : $product->getAmEmailTemplate();
        $productOptions[Attributes::CODE_SET] = $product->getAmGiftcardCodeSet();
        $orderItem->setProductOptions($productOptions);

        return $orderItem;
    }

    /**
     * Save custom image as entity and set its id to image option
     * @param $imageOption
     * @param $customImageName
     */
    private function processCustomImage($imageOption, $customImageName)
    {
        $imageModel = $this->imageRepository->getEmptyImageModel();
        $imageModel->setIsUserUpload(true)
            ->setImagePath($customImageName)
            ->setStatus(ImageStatus::DISABLED)
            ->setTitle(self::CUSTOM_IMAGE_TITLE);
        $this->imageRepository->save($imageModel);
        $imageOption->setValue($imageModel->getImageId());
    }
}
