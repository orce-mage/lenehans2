<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImageProcessor
{
    /**
     * Locator area inside media folder
     */
    const AMLOCATOR_MEDIA_PATH = 'amasty/amlocator';

    /**
     * Locator temporary area inside media folder
     */
    const AMLOCATOR_MEDIA_TMP_PATH = 'amasty/amlocator/tmp';

    /**
     * Gallery area inside media folder
     */
    const AMLOCATOR_GALLERY_MEDIA_PATH = 'amasty/amlocator/gallery';

    /**
     * Gallery temporary area inside media folder
     */
    const AMLOCATOR_GALLERY_MEDIA_TMP_PATH = 'amasty/amlocator/gallery/tmp';

    /**
     * Type image option marker_img
     */
    const MARKER_IMAGE_TYPE = 'marker_img';

    /**
     * Type image option gallery_image
     */
    const GALLERY_IMAGE_TYPE = 'gallery_image';

    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * @var \Magento\Framework\ImageFactory
     */
    private $imageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Allowed extensions
     *
     * @var array
     */
    protected $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Magento\Framework\ImageFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->imageUploader = $imageUploader;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        return $this->mediaDirectory;
    }

    /**
     * @param string $imageName
     *
     * @return string
     */
    public function getImageRelativePath($imageName)
    {
        return $this->imageUploader->getBasePath() . DIRECTORY_SEPARATOR . $imageName;
    }

    /**
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getImageUrl($params = [])
    {
        return $this->getMediaUrl() . implode(DIRECTORY_SEPARATOR, $params);
    }

    /**
     * Move file from temporary directory
     *
     * @param string $imageName
     * @param string $imageType
     * @param int $locationId
     * @param bool $locationIsNew
     */
    public function processImage($imageName, $imageType, $locationId, $locationIsNew)
    {
        $this->setBasePaths($imageType, $locationId, $locationIsNew);
        $this->imageUploader->moveFileFromTmp($imageName, true);

        $filename = $this->getMediaDirectory()->getAbsolutePath($this->getImageRelativePath($imageName));
        try {
            $this->prepareImage($filename, $imageType);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->messageManager->addErrorMessage(
                __($errorMessage)
            );
            $this->logger->critical($e);
        }
    }

    /**
     * @param string $filename
     * @param string $imageType
     * @param bool $needResize
     */
    public function prepareImage($filename, $imageType, $needResize = false)
    {
        /** @var \Magento\Framework\Image $imageProcessor */
        $imageProcessor = $this->imageFactory->create(['fileName' => $filename]);
        $imageProcessor->keepAspectRatio(true);
        $imageProcessor->keepFrame(true);
        $imageProcessor->keepTransparency(true);
        if ($imageType == self::MARKER_IMAGE_TYPE || $needResize) {
            $imageProcessor->resize(27, 43);
        }
        $imageProcessor->save();
    }

    /**
     * @param string $imageName
     */
    public function deleteImage($imageName)
    {
        if ($imageName && strpos($imageName, '.') !== false) {
            $this->getMediaDirectory()->delete(
                $this->getImageRelativePath($imageName)
            );
        }
    }

    /**
     * @param string $imageType
     * @param int $locationId
     * @param bool $locationIsNew
     */
    public function setBasePaths($imageType, $locationId, $locationIsNew)
    {
        // if location doesn't exist, we set 0 to tmp path
        $tmpLocationId = $locationIsNew ? 0 : $locationId;
        $tmpPath = ImageProcessor::AMLOCATOR_MEDIA_TMP_PATH . DIRECTORY_SEPARATOR . $tmpLocationId;
        $this->imageUploader->setBaseTmpPath(
            $tmpPath
        );
        switch ($imageType) {
            case ImageProcessor::MARKER_IMAGE_TYPE:
                $this->imageUploader->setBasePath(
                    ImageProcessor::AMLOCATOR_MEDIA_PATH . DIRECTORY_SEPARATOR . $locationId
                );
                break;

            case ImageProcessor::GALLERY_IMAGE_TYPE:
                $this->imageUploader->setBasePath(
                    ImageProcessor::AMLOCATOR_GALLERY_MEDIA_PATH . DIRECTORY_SEPARATOR . $locationId
                );
                break;
        }
    }
}
