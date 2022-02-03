<?php

namespace Amasty\Faq\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImageProcessor
{
    /**
     * FAQ area inside media folder
     */
    const  FAQ_MEDIA_PATH = 'amasty/faq';

    /**
     * FAQ Category area inside media folder
     */
    const  CATEGORY_MEDIA_PATH = 'amasty/faq/category';

    /**
     * FAQ Category temporary area inside media folder
     */
    const  CATEGORY_MEDIA_TMP_PATH = 'amasty/faq/tmp/category';

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

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Magento\Framework\ImageFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->imageUploader = $imageUploader;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
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
     * @param string $iconName
     *
     * @return string
     */
    public function getCategoryIconUrl($iconName)
    {
        return $this->getCategoryIconMedia() . '/' . $iconName;
    }

    /**
     * @param string $iconName
     *
     * @return string
     */
    public function getCategoryIconRelativePath($iconName)
    {
        return self::CATEGORY_MEDIA_PATH . DIRECTORY_SEPARATOR . $iconName;
    }

    /**
     * Url type http://url/pub/media/amasty/faq/category/
     *
     * @return string
     */
    public function getCategoryIconMedia()
    {
        return $this->storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::CATEGORY_MEDIA_PATH;
    }

    /**
     * Move file from temporary directory and resize it to 50x50
     *
     * @param string $iconName
     */
    public function processCategoryIcon($iconName)
    {
        $this->imageUploader->moveFileFromTmp($iconName, true);

        $filename = $this->getMediaDirectory()->getAbsolutePath($this->getCategoryIconRelativePath($iconName));
        try {
            /** @var \Magento\Framework\Image $imageProcessor */
            $imageProcessor = $this->imageFactory->create(['fileName' => $filename]);
            $imageProcessor->keepAspectRatio(true);
            $imageProcessor->keepFrame(true);
            $imageProcessor->keepTransparency(true);
            $imageProcessor->backgroundColor([255, 255, 255]);
            $imageProcessor->resize(50, 50);
            $imageProcessor->save();
        } catch (\Exception $e) {
            // Unsupported image format.
            null;
        }
    }

    /**
     * @param string $iconName
     */
    public function deleteImage($iconName)
    {
        $this->getMediaDirectory()->delete(
            $this->getCategoryIconRelativePath($iconName)
        );
    }
}
