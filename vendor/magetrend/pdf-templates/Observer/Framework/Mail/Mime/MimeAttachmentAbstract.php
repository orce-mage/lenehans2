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

namespace Magetrend\PdfTemplates\Observer\Framework\Mail\Mime;

use Magento\Framework\Event\Observer;
use Magento\Store\Model\ScopeInterface;

/**
 * TransportInterfaceFactory Plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
abstract class MimeAttachmentAbstract extends \Magetrend\PdfTemplates\Observer\Framework\Mail\AttachmentAbstract
{
    public $objectManager;

    public $mimeAttachmentManager;

    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magetrend\PdfTemplates\Model\Template $pdfTemplate,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magetrend\PdfTemplates\Model\MimeAttachmentManager $mimeAttachmentManager,
        \Magetrend\PdfTemplates\Model\AttachmentRegistry $attachmentRegistry
    ) {
        $this->objectManager = $objectManager;
        $this->mimeAttachmentManager = $mimeAttachmentManager;
        parent::__construct(
            $moduleHelper,
            $scopeConfig,
            $pdfTemplate,
            $orderRepository,
            $invoiceRepository,
            $shipmentRepository,
            $creditmemoRepository,
            $dateTime,
            $attachmentRegistry
        );
    }

    /**
     * Attach file to email
     * @param $observer
     * @param $fileContent
     * @param $fileName
     * @return bool
     */
    public function attachFile($observer, $fileContent, $fileName)
    {
        if (empty($fileContent)) {
            return false;
        }

        $attachment = $this->objectManager->create(
            'Magento\Framework\Mail\MimePartInterface',
            [
                'content' => $fileContent,
                'type' => 'application/pdf',
                'fileName' => $fileName,
                'disposition' => \Zend\Mime\Mime::DISPOSITION_ATTACHMENT,
                'encoding' => \Zend\Mime\Mime::ENCODING_BASE64
            ]
        );

        $this->mimeAttachmentManager->addPart($attachment);
        return true;
    }
}
