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

namespace Magetrend\PdfTemplates\Model;

/**
 * Attachments manager class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class AttachmentManager
{
    const REGISTRY_KEY_TEMPLATE_ID = 'mt_pdf_template_id';

    const REGISTRY_KEY_TEMPLATE_VARS = 'mt_pdf_template_vars';

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    public $eventManager;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Event\Manager $eventManager

    ) {
        $this->registry = $registry;
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Framework\Mail\Message $message
     */
    public function attach($message)
    {
        $this->eventManager->dispatch('mt_pdf_email_attach', [
            'message' => $message,
            'template_id' => $this->registry->registry(self::REGISTRY_KEY_TEMPLATE_ID),
            'template_vars' => $this->registry->registry(self::REGISTRY_KEY_TEMPLATE_VARS),
        ]);
    }
}
