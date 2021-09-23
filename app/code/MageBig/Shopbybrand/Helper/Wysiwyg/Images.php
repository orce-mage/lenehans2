<?php
/**
 * Copyright � 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageBig\Shopbybrand\Helper\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Wysiwyg Images Helper
 */

class Images extends \Magento\Cms\Helper\Wysiwyg\Images
{
	public function getImageRelativeUrl($filename)
    {
		$fileurl = $this->getCurrentUrl() . $filename;
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return str_replace($mediaUrl, '', $fileurl);
	}


	public function getMediaUrl()
    {
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
}
