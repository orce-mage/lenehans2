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

namespace Magetrend\PdfTemplates\Plugin\Framework\Mail;

/**
 * TransportInterfaceFactory Plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class TransportInterfaceFactory
{
    public $attachmentManager;

    public function __construct(
        \Magetrend\PdfTemplates\Model\AttachmentManager $attachmentManager
    ) {
        $this->attachmentManager = $attachmentManager;
    }

    public function beforeCreate($subject, array $data = [])
    {
        if (isset($data['message'])) {
            $this->attachmentManager->attach($data['message']);
        }

        return [$data];
    }
}
