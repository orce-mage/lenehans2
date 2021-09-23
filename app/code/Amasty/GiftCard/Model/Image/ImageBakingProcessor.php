<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image;

use Amasty\GiftCard\Api\Data\ImageBakingInfoInterface;
use Amasty\GiftCard\Api\Data\ImageInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\Adapter\AbstractAdapter;

class ImageBakingProcessor
{
    const IMAGE_WIDTH_KEY = 0;
    const BLACK_COLOR = '000000';

    const AMGIFTCARD_IMAGE_WITH_CODE_MEDIA_PATH = 'amasty/amgcard/image/generated_images_cache';
    const FONT_FILE_ARIAL = 'amasty/amgcard/image/arial_bold.ttf';

    const EVENT_NAME = 'amasty_giftcard_image_baking_data_prepare';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \GdImage|resource|null
     */
    private $imageResource = null;

    public function __construct(
        Filesystem $filesystem,
        ManagerInterface $eventManager
    ) {
        $this->filesystem = $filesystem;
        $this->eventManager = $eventManager;
    }

    public function generateImage(ImageInterface $image, string $imagePath, string $code): string
    {
        if (empty($image->getBakingInfo())) {
            throw new LocalizedException(__('No Baking Info found for Image with ID "%1.', $image->getImageId()));
        }

        $bakingInfo = $image->getBakingInfo();
        $this->eventManager->dispatch(
            self::EVENT_NAME,
            ['baking_info' => $bakingInfo, 'code' => $code]
        );

        $this->initImageResource($imagePath);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        $imageInfo = getimagesize($imagePath);

        /** @var ImageBakingInfoInterface $bakingData */
        foreach ($image->getBakingInfo() as $bakingData) {
            if (!$bakingData->isEnabled() || !$bakingData->getValue()) {
                continue;
            }
            $this->bakeTextInImage($bakingData, (int)$imageInfo[self::IMAGE_WIDTH_KEY]);
        }

        $generatedImageName = $code;

        switch ($imageInfo['mime']) {
            case 'image/png':
                $generatedImageName .= '.png';
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                imagepng($this->imageResource, $this->getGeneratedImageMediaPath() . $generatedImageName);
                break;
            case 'image/gif':
                $generatedImageName .= '.gif';
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                imagegif($this->imageResource, $this->getGeneratedImageMediaPath() . $generatedImageName);
                break;
            case 'image/jpeg':
            default:
                $generatedImageName .= '.jpg';
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                imagejpeg($this->imageResource, $this->getGeneratedImageMediaPath() . $generatedImageName);
                break;
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        imagedestroy($this->imageResource);

        return $generatedImageName;
    }

    /**
     * @param string $imagePath
     */
    private function initImageResource(string $imagePath): void
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        $imageInfo = getimagesize($imagePath);

        switch ($imageInfo['mime']) {
            case 'image/png':
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                $imageResource = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                $imageResource = imagecreatefromgif($imagePath);
                break;
            case 'image/jpeg':
            default:
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                $imageResource = imagecreatefromjpeg($imagePath);
                break;
        }
        $this->imageResource = $imageResource;
    }

    private function bakeTextInImage(ImageBakingInfoInterface $bakingData, int $imageWidth): void
    {
        $color = $bakingData->getTextColor()
            ? hexdec(ltrim($bakingData->getTextColor(), '#'))
            : hexdec(self::BLACK_COLOR);
        $fontSize = $bakingData->getFontSize() ?? AbstractAdapter::DEFAULT_FONT_SIZE;

        if (($bakingData->getPosX() + strlen($bakingData->getValue()) * $fontSize)
            > $imageWidth
        ) {
            $posX = max(0, $bakingData->getPosX()
                - (($bakingData->getPosX() + strlen($bakingData->getValue()) * $fontSize)
                    - $imageWidth) + $fontSize);
        } else {
            $posX = $bakingData->getPosX();
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        imagettftext(
            $this->imageResource,
            $fontSize,
            0,
            $posX,
            $bakingData->getPosY() + $fontSize,
            $color,
            $this->getFontPath(),
            $bakingData->getValue()
        );
    }

    /**
     * @return string
     */
    private function getFontPath(): string
    {
        return $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::FONT_FILE_ARIAL
        );
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    private function getGeneratedImageMediaPath(): string
    {
        $reader = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $generatedImagesPath = $reader->getAbsolutePath(
            self::AMGIFTCARD_IMAGE_WITH_CODE_MEDIA_PATH . DIRECTORY_SEPARATOR
        );

        if (!$reader->isExist($generatedImagesPath)) {
            $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)
                ->create(self::AMGIFTCARD_IMAGE_WITH_CODE_MEDIA_PATH . DIRECTORY_SEPARATOR);
        }

        return $generatedImagesPath;
    }
}
