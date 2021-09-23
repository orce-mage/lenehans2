<?php

namespace MageBig\WidgetPlus\Model\Plugin\Cms\Helper\Wysiwyg;

class Images
{
    public function aroundGetImageHtmlDeclaration($subject, $proceed, $filename, $renderAsTag = false)
    {
        $returnValue = $proceed($filename, $renderAsTag);

        $fileurl = $subject->getCurrentUrl().$filename;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediaPath = str_replace($mediaUrl, '', $fileurl);

        $request = $objectManager->get("\Magento\Framework\App\Request\Http");
        if ($request->getParam('is_background')) {
            return $mediaPath;
        }

        return $returnValue;
    }
}
