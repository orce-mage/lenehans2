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
class Message
{
    public $moduleHelper;

    public $attachmentRegistry;

    public $attachmentManager;

    private $isProcessed = false;

    /**
     * TransportInterfaceFactory constructor.
     * @param \Magetrend\EmailAttachment\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\AttachmentRegistry $attachmentRegistry,
        \Magetrend\PdfTemplates\Model\AttachmentManager $attachmentManager
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->attachmentRegistry = $attachmentRegistry;
        $this->attachmentManager = $attachmentManager;
    }

    /**
     * Hook
     * @param $subject
     * @param array $data
     * @return array
     */
    public function afterSetBody($subject, $result)
    {
        if ($this->isProcessed) {
            return $result;
        }

        if (!$this->moduleHelper->isActive()) {
            return $result;
        }

        $this->attachmentManager->attach(false);

        $parts = $this->attachmentRegistry->getParts();

        if (empty($parts)) {
            return $result;
        }

        $body = $subject->getBody();
        if ($body instanceof \Zend\Mime\Message) {
            foreach ($parts as $part) {
                $body->addPart($part);
            }

            $this->isProcessed = true;
            $subject->setBody($body);
        }

        $this->attachmentRegistry->resetParts();

        return $result;
    }
}
