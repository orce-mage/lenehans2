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
 * MimeMessage Plugin class
 */
class MimeMessage
{
    /**
     * @var \Magetrend\EmailAttachment\Model\AttachmentManager
     */
    public $mimeAttachmentManager;

    public $moduleHelper;

    /**
     * TransportInterfaceFactory constructor.
     * @param \Magetrend\EmailAttachment\Model\AttachmentManager $attachmentManager
     * @param \Magetrend\EmailAttachment\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magetrend\PdfTemplates\Model\MimeAttachmentManager $mimeAttachmentManager,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper
    ) {
        $this->mimeAttachmentManager = $mimeAttachmentManager;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Hook
     * @param $subject
     * @param array $data
     * @return array
     */
    public function afterGetParts($subject, $parts)
    {
        if (!$this->moduleHelper->isActive()) {
            return $parts;
        }

        if (!empty($parts) && $this->mimeAttachmentManager->getParts() === null) {
            $this->mimeAttachmentManager->collectParts();
            $additionalParts = $this->mimeAttachmentManager->getParts();
            if (!empty($additionalParts)) {
                foreach ($additionalParts as $aPart) {
                    $parts[] = $aPart;
                }
            }
        }

        $this->mimeAttachmentManager->resetParts();

        return $parts;
    }
}
