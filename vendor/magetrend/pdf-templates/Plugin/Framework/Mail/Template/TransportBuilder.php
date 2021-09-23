<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Plugin\Framework\Mail\Template;

/**
 * TransportInterfaceFactory Plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class TransportBuilder
{
    public $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function beforeSetTemplateIdentifier($subject, $templateIdentifier)
    {
        $this->registry->unregister(\Magetrend\PdfTemplates\Model\AttachmentManager::REGISTRY_KEY_TEMPLATE_ID);
        $this->registry->register(
            \Magetrend\PdfTemplates\Model\AttachmentManager::REGISTRY_KEY_TEMPLATE_ID,
            $templateIdentifier,
            true
        );

        return [$templateIdentifier];
    }

    public function beforeSetTemplateVars($subject, $vars)
    {
        $this->registry->unregister(\Magetrend\PdfTemplates\Model\AttachmentManager::REGISTRY_KEY_TEMPLATE_VARS);
        $this->registry->register(
            \Magetrend\PdfTemplates\Model\AttachmentManager::REGISTRY_KEY_TEMPLATE_VARS,
            $vars,
            true
        );

        return [$vars];
    }
}
