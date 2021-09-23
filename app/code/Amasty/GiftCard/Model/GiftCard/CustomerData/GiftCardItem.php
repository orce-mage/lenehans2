<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\CustomerData;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCard\Model\Image\Repository as ImageRepository;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Checkout\Helper\Data as CheckoutHelperData;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Msrp\Helper\Data as MsrpHelperData;
use Psr\Log\LoggerInterface;

class GiftCardItem extends DefaultItem
{
    /**
     * @var ImageRepository
     */
    private $imageRepository;

    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $itemImages = [];

    public function __construct(
        Image $imageHelper,
        MsrpHelperData $msrpHelper,
        UrlInterface $urlBuilder,
        ConfigurationPool $configurationPool,
        CheckoutHelperData $checkoutHelper,
        ImageRepository $imageRepository,
        FileUpload $fileUpload,
        LoggerInterface $logger
    ) {
        parent::__construct($imageHelper, $msrpHelper, $urlBuilder, $configurationPool, $checkoutHelper);
        $this->imageRepository = $imageRepository;
        $this->fileUpload = $fileUpload;
        $this->logger = $logger;
    }

    protected function doGetItemData(): array
    {
        $itemData = parent::doGetItemData();
        $imageUrl = $this->getItemImageUrl($this->item);

        if ($imageUrl) {
            $itemData['product_image']['src'] = $imageUrl;
        }

        return $itemData;
    }

    public function getItemImageUrl(ItemInterface $item): ?string
    {
        if (!isset($this->itemImages[$item->getItemId()])) {
            $imageUrl = null;
            $imageModel = null;
            $imageOption = $item->getOptionByCode(GiftCardOptionInterface::IMAGE);

            if ($imageOption->getValue() == GiftCard::CUSTOM_IMAGE_PARAM) {
                $imageUrl = $this->fileUpload
                    ->getTempImgUrl($item->getOptionByCode(GiftCardOptionInterface::CUSTOM_IMAGE)->getValue());
            } else {
                try {
                    $imageModel = $this->imageRepository->getById((int)$imageOption->getValue());
                    $imageUrl = $this->fileUpload->getImageUrl(
                        $imageModel->getImagePath(),
                        $imageModel->isUserUpload()
                    );
                } catch (NoSuchEntityException $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            $this->itemImages[$item->getItemId()] = $imageUrl;
        }

        return $this->itemImages[$item->getItemId()];
    }
}
