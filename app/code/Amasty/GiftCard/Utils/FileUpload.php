<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Utils;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Amasty\GiftCard\Model\Image\ImageBakingProcessor;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\Adapter\AbstractAdapter;
use Magento\Framework\ImageFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class FileUpload
{
    const AMGIFTCARD_IMAGE_MEDIA_PATH = 'amasty/amgcard/image';
    const AMGIFTCARD_IMAGE_MEDIA_TMP_PATH = 'amasty/amgcard/image/tmp';

    const ADMIN_UPLOAD_PATH = 'admin_upload';
    const USER_UPLOAD_PATH = 'user_upload';

    /**
     * Maximum file size allowed for file_uploader UI component
     */
    const MAX_FILE_SIZE = 2097152;
    const ADMIN_IMAGE_UPLOAD_ID = 'image';
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    const PROCESSING_IMAGE_EXT = 'gd';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var UploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var File
     */
    private $ioFile;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var ImageBakingProcessor
     */
    private $imageBakingProcessor;

    public function __construct(
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        UrlInterface $url,
        ImageFactory $imageFactory,
        File $ioFile,
        Random $random,
        ImageBakingProcessor $imageBakingProcessor
    ) {
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->url = $url;
        $this->imageFactory = $imageFactory;
        $this->ioFile = $ioFile;
        $this->random = $random;
        $this->imageBakingProcessor = $imageBakingProcessor;
    }

    /**
     * @param array $file
     * @param string $fieldId
     *
     * @return array
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function saveFileToTmpDir(array $file, string $fieldId = self::ADMIN_IMAGE_UPLOAD_ID): array
    {
        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new LocalizedException(__('File can not be saved because it is too big.'));
        }
        $path = $this->getTmpPath();
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $writer->create($path);
        $extension = '.' . $this->ioFile->getPathInfo($file['name'])['extension'];
        $fileHash = $this->random->getUniqueHash() . $extension;

        if ($writer->isExist($path . $fileHash)) {
            $this->deleteTemp($fileHash);
        }
        try {
            $uploader = $this->fileUploaderFactory->create(['fileId' => $fieldId]);
            $uploader->setAllowedExtensions(self::ALLOWED_EXTENSIONS);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($path, $fileHash);

            if (!$result) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File can not be saved to the destination folder.')
                );
            }
            $result['name'] = $fileHash;
            unset($result['path']);

            $result['url'] = $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . self::AMGIFTCARD_IMAGE_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $fileHash;

        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->logger->critical($e);
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $result;
    }

    /**
     * @param string $fileName
     * @param bool $isUser
     *
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function saveFromTemp(string $fileName, bool $isUser)
    {
        $tmpPath = $this->getTmpPath();
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        if ($writer->isExist($tmpPath . $fileName)) {
            $savePath = $this->getMediaPath($isUser);
            $writer->create($savePath);
            $writer->copyFile($tmpPath . $fileName, $savePath . $fileName);
            $writer->delete($tmpPath . $fileName);
        }
    }

    /**
     * @param string $fileName
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTempImgUrl($fileName): string
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        );

        return $mediaUrl . self::AMGIFTCARD_IMAGE_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param string $fileName
     *
     * @throws FileSystemException
     */
    public function deleteTemp(string $fileName)
    {
        $path = $this->getTmpPath();
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path .= $fileName;

        if ($writer->isExist($path)) {
            $writer->delete($path);
        }
    }

    /**
     * @param string $imageName
     * @param bool $isUser
     *
     * @throws FileSystemException
     */
    public function deleteImage(string $imageName, bool $isUser)
    {
        $path = $this->getMediaPath($isUser);
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        if ($writer->isExist($path . $imageName)) {
            $writer->delete($path . $imageName);
        }
    }

    /**
     * @param array $file
     *
     * @return string
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function convertFileToBase64(array $file): string
    {
        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new LocalizedException(__('File can not be saved because it is too big.'));
        }
        $type = $file['type'];
        $directoryReader = $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);

        if (!$directoryReader->isExist($file['tmp_name'])) {
            return '';
        }
        $fileContent = $directoryReader->readFile($file['tmp_name']);

        return 'data:' . $type . ';base64,' . base64_encode($fileContent);
    }

    /**
     * @param string $fileName
     * @param bool $isUser
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl(string $fileName, bool $isUser = false): string
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        );
        if ($isUser) {
            $imagePath = self::AMGIFTCARD_IMAGE_MEDIA_PATH . DIRECTORY_SEPARATOR
                . self::USER_UPLOAD_PATH . DIRECTORY_SEPARATOR;
        } else {
            $imagePath = self::AMGIFTCARD_IMAGE_MEDIA_PATH . DIRECTORY_SEPARATOR
                . self::ADMIN_UPLOAD_PATH . DIRECTORY_SEPARATOR;
        }

        return $mediaUrl . $imagePath . $fileName;
    }

    /**
     * @param ImageInterface $image
     * @param string $code
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEmailImageUrl(ImageInterface $image, string $code): string
    {
        $reader = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        );
        $extension = '.' . $this->ioFile->getPathInfo($image->getImagePath())['extension'];
        $emailImagePath = ImageBakingProcessor::AMGIFTCARD_IMAGE_WITH_CODE_MEDIA_PATH
            . DIRECTORY_SEPARATOR . $code . $extension;

        if (!$reader->isExist($emailImagePath)) {
            $imageUrl = $this->buildEmailImage($image, $code);
        } else {
            $imageUrl = $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $emailImagePath;
        }

        return $imageUrl;
    }

    /**
     * @param string $imageUrl
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImagePathByUrl(string $imageUrl): string
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        );
        $imageMediaPath = str_replace($mediaUrl, '', $imageUrl);
        $reader = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        );

        if ($reader->isExist($imageMediaPath)) {
            return $reader->getAbsolutePath($imageMediaPath);
        }

        return '';
    }

    /**
     * @param bool $isUser
     *
     * @return string
     */
    private function getMediaPath(bool $isUser): string
    {
        if ($isUser) {
            $directoryPath = self::USER_UPLOAD_PATH;
        } else {
            $directoryPath = self::ADMIN_UPLOAD_PATH;
        }

        return $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::AMGIFTCARD_IMAGE_MEDIA_PATH . DIRECTORY_SEPARATOR . $directoryPath . DIRECTORY_SEPARATOR
        );
    }

    /**
     * @return string
     */
    private function getTmpPath(): string
    {
        return $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::AMGIFTCARD_IMAGE_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Trying to generate image with code and return it url
     * if fail - return original image url
     *
     * @param ImageInterface $image
     * @param string $code
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function buildEmailImage(ImageInterface $image, string $code): string
    {
        $originalImageUrl = $this->getImageUrl(
            $image->getImagePath(),
            $image->isUserUpload()
        );

        if (!extension_loaded(self::PROCESSING_IMAGE_EXT) || $image->isUserUpload()) {
            return $originalImageUrl;
        }
        $imagePath = $this->getImagePath($image->getImagePath(), $image->isUserUpload());
        //phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
        $imageInfo = getimagesize($imagePath);

        if (!$imageInfo) {
            return $originalImageUrl;
        }

        try {
            $generatedImageName = $this->imageBakingProcessor->generateImage(
                $image,
                $imagePath,
                $code
            );

            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . ImageBakingProcessor::AMGIFTCARD_IMAGE_WITH_CODE_MEDIA_PATH
                . DIRECTORY_SEPARATOR . $generatedImageName;
        } catch (\Exception $e) {
            return $originalImageUrl;
        }
    }

    /**
     * @param string $fileName
     * @param bool $isUser
     *
     * @return string
     */
    protected function getImagePath(string $fileName, bool $isUser = false): string
    {
        $mediaPath = $this->getMediaPath($isUser);

        return $mediaPath . $fileName;
    }
}
