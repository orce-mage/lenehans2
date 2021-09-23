<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


use Amasty\GiftCard\Model\Image\Image;
use Amasty\GiftCard\Model\Image\ImageBakingInfo;
use Amasty\GiftCard\Model\Image\ImageBakingProcessor;
use Amasty\GiftCard\Model\Image\Repository;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Filesystem $filesystem */
$filesystem = $objectManager->create(Filesystem::class);

$mediaWriter = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
$mediaWriter->create(FileUpload::AMGIFTCARD_IMAGE_MEDIA_PATH . DIRECTORY_SEPARATOR . FileUpload::ADMIN_UPLOAD_PATH);
$absolutePath = $mediaWriter->getAbsolutePath(
    FileUpload::AMGIFTCARD_IMAGE_MEDIA_PATH . DIRECTORY_SEPARATOR . FileUpload::ADMIN_UPLOAD_PATH . DIRECTORY_SEPARATOR
);
$img = imagecreatetruecolor(300, 300);
$color = imagecolorallocate($img, 255, 255, 255);
imagefilledrectangle($img, 0, 0, 300, 300, $color);
imagejpeg($img, $absolutePath . "test_giftcard_image.jpg", 100);

/** @var ImageBakingInfo $gCardImageBakingData */
$gCardImageBakingData = $objectManager->create(ImageBakingInfo::class);
$gCardImageBakingData->setName('code')
    ->setPosX(20)
    ->setPosY(20)
    ->setTextColor('#FF0000');

/** @var Image $gCardImage */
$gCardImage = $objectManager->create(Image::class);
$gCardImage->setTitle('Test Image')
    ->setStatus(1)
    ->setImagePath('test_giftcard_image.jpg')
    ->setIsUserUpload(false)
    ->setBakingInfo(['code' => $gCardImageBakingData]);

/** @var Repository $gCardImageRepo */
$gCardImageRepo = $objectManager->create(Repository::class);
$gCardImageRepo->save($gCardImage);

$fontPath = $mediaWriter->getAbsolutePath(ImageBakingProcessor::FONT_FILE_ARIAL);
copy(__DIR__ . '/test_font.ttf', $fontPath);
