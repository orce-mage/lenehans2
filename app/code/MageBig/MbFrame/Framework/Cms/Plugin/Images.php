<?php

namespace MageBig\MbFrame\Framework\Cms\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;

class Images extends \Magento\Cms\Helper\Wysiwyg\Images
{
    /**
     * @param $subject
     * @param $proceed
     * @param $filename
     * @param false $renderAsTag
     * @return string
     * @throws NoSuchEntityException
     */
    public function aroundGetImageHtmlDeclaration($subject, $proceed, $filename, bool $renderAsTag = false): string
    {
        $fileUrl = $this->getCurrentUrl() . $filename;
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediaPath = str_replace($mediaUrl, '', $fileUrl);
        $directive = sprintf("{{media url='%s'}}", $mediaPath);
        if ($renderAsTag) {
            $src = $this->isUsingStaticUrlsAllowed() ? $fileUrl : $this->escaper->escapeHtml($directive);
            $html = sprintf('<img src="%s" alt="" />', $src);
        } else {
            if ($this->isUsingStaticUrlsAllowed()) {
                $html = $fileUrl;
            } else {
                $directive = $this->urlEncoder->encode($directive);
                $html = $this->_backendData->getUrl(
                    'cms/wysiwyg/directive',
                    [
                        '___directive' => $directive,
                        '_escape_params' => false,
                    ]
                );
            }
        }

        return $html;
    }
}
