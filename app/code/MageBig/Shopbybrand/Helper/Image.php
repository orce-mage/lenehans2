<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogRule data helper
 */
namespace MageBig\Shopbybrand\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_width;
    protected $_height;

    /**
     * Default quality value (for JPEG images only).
     *
     * @var int
     */
    protected $_quality = 100;
    protected $_keepAspectRatio = true;
    protected $_keepFrame = true;
    protected $_keepTransparency = true;
    protected $_constrainOnly = true;
    protected $_backgroundColor = [255, 255, 255];
    protected $_baseFile;
    protected $_isBaseFilePlaceholder;
    protected $_newFile;
    protected $_processor;
    protected $_destinationSubdir;
    protected $_angle;
    protected $_watermarkFile;
    protected $_watermarkPosition;
    protected $_watermarkWidth;
    protected $_watermarkHeight;
    protected $_watermarkImageOpacity = 0;

    protected $_cachePath = 'catalog/product/cache/brand/';
    protected $_placeholderFile = 'catalog/brand/placeholder.png';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_imageFactory = $imageFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function init($baseFile)
    {
        $this->_newFile = '';
        $this->_baseFile = $baseFile;

        return $this;
    }

    public function clearCache()
    {
        if ($this->_mediaDirectory->isExist($this->_cachePath)) {
            if ($this->_mediaDirectory->isWritable($this->_cachePath)) {
                $this->_mediaDirectory->delete($this->_cachePath);
            } else {
                $cacheDirectory = $this->_mediaDirectory->getAbsolutePath($this->_cachePath);
                throw new \Exception("Please give write permisson for directory \"{$cacheDirectory}\"");
            }
        }
    }

    public function getImageProcessor()
    {
        $filename = $this->_baseFile ? $this->_mediaDirectory->getAbsolutePath($this->_baseFile) : null;
        if (!$this->_fileExists($this->_baseFile)) {
            $filename = $this->_mediaDirectory->getAbsolutePath($this->_placeholderFile);
            $this->_newFile = $this->_cachePath . $this->_width . 'x' . $this->_height. '/' . $this->_placeholderFile;
        }
        $this->_processor = $this->_imageFactory->create($filename);


        $this->_processor->keepAspectRatio($this->_keepAspectRatio);

        if ($this->_height === null) {
            $this->_processor->keepFrame(false);
        } else {
            $this->_processor->keepFrame($this->_keepFrame);
        }

        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($this->_quality);
        $this->_processor->resize($this->_width, $this->_height);
        return $this->_processor;
    }

    public function saveFile()
    {

        $imageProcessor = $this->getImageProcessor();
        $filename = $this->_mediaDirectory->getAbsolutePath($this->_newFile);
        $imageProcessor->save($filename);
        return $this;
    }

    protected function _fileExists($filename)
    {
        if ($this->_mediaDirectory->isFile($filename)) {
            return true;
        } else {
            return false;
        }
    }

    public function isCached()
    {
        if (is_string($this->_newFile)) {
            return $this->_fileExists($this->_newFile);
        }
    }

    public function resize($width, $height = null)
    {
        if($this->_baseFile){
            $this->_width = $width;
            $this->_height = $height;
            if ($height === null) {
                $this->_keepFrame = true;
            }
            $path = $this->_cachePath . $width.'x'.$height.'/';
            $this->_newFile = $path. $this->_baseFile;
            if(!$this->isCached()){
                $this->saveFile();
            }
        }
        return $this;
    }

    public function __toString()
    {
        $url = '';
        if($this->_baseFile){
            $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $this->_newFile;
        }
        return $url;
    }
}
