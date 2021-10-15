<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Plugin;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use WeltPixel\EnhancedEmail\Helper\Data as EnhancedEmailHelper;

/**
 * Class EmailTemplateConfig
 * @package WeltPixel\EnhancedEmail\Plugin
 */
class EmailTemplateConfig
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var EnhancedEmailHelper
     */
    protected $_wpHelper;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * EmailTemplateConfig constructor.
     * @param RequestInterface $request
     * @param ProductMetadataInterface $productMetadata
     * @param EnhancedEmailHelper $wpHelper
     */
    public function __construct(
        RequestInterface $request,
        ProductMetadataInterface $productMetadata,
        EnhancedEmailHelper $wpHelper
    ) {
        $this->_request = $request;
        $this->productMetadata = $productMetadata;
        $this->_wpHelper = $wpHelper;
    }

    public function afterGetTemplateFilename(
        \Magento\Email\Model\Template\Config $subject,
        $result
    ) {
        $restrictedPages = [
            'adminhtml_email_template_defaultTemplate'
        ];
        $requestActionName = $this->_request->getFullActionName();
        if (!in_array($requestActionName, $restrictedPages)) {
            return $result;
        }

        if (strpos($result, 'WeltPixel/EnhancedEmail') !== false) {
            $magentoVersion = $this->productMetadata->getVersion();
            if (version_compare($magentoVersion, '2.3.4', '<')) {
                $result = str_replace('email', 'email/legacy', $result);
            }
        }

        return $result;
    }

}
