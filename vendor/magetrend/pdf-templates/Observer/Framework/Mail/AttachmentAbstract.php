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

namespace Magetrend\PdfTemplates\Observer\Framework\Mail;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
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
abstract class AttachmentAbstract implements \Magento\Framework\Event\ObserverInterface
{
    public $moduleHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magetrend\PdfTemplates\Model\Template
     */
    public $pdfTemplate;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    public $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    public $shipmentRepository;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    public $creditmemoRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * @var \Magetrend\PdfTemplates\Model\AttachmentRegistry
     */
    public $attachmentRegistry;

    /**
     * AttachmentAbstract constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magetrend\PdfTemplates\Model\Template $pdfTemplate
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magetrend\PdfTemplates\Model\Template $pdfTemplate,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magetrend\PdfTemplates\Model\AttachmentRegistry $attachmentRegistry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->moduleHelper = $moduleHelper;
        $this->pdfTemplate = $pdfTemplate;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->dateTime = $dateTime;
        $this->attachmentRegistry = $attachmentRegistry;
    }

    /**
     * @param Observer $observer
     * @return mixed
     */
    abstract public function execute(Observer $observer);

    /**
     * @param $observer
     * @return int
     */
    public function getStoreId($observer)
    {
        $templateVars = $observer->getTemplateVars();
        if (!isset($templateVars['store'])) {
            return 0;
        }

        $store = $templateVars['store'];
        return $store->getId();
    }

    /**
     * @param $type
     * @param int $storeId
     * @return mixed
     */
    public function needToAttach($type, $storeId = 0)
    {
        return $this->scopeConfig->getValue(
            'pdftemplates/attachments/'.$type,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $observer
     * @param $pdfString
     * @param $fileName
     * @return bool
     */
    public function attachFile($observer, $pdfString, $fileName)
    {
        if (empty($pdfString)) {
            return false;
        }
        /**
         * @var \Magento\Framework\Mail\Message $message
         */
        $message = $observer->getMessage();
        if ($message === false) {
            $attachment = new \Zend\Mime\Part($pdfString);
            $attachment->type = 'application/pdf';
            $attachment->filename = $fileName;
            $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
            $this->attachmentRegistry->addPart($attachment);
            return true;
        }

        $body = $message->getBody();
        if (method_exists($message, 'createAttachment')) {
            /**
             * before Magento 2.3
             */
            $message->createAttachment(
                $pdfString,
                'application/pdf',
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                $fileName
            );

            return true;
        } else {
            return false;
        }

        return false;
    }

    /**
     * @param $objects
     * @return string
     */
    public function getPdf($objects)
    {
        if (empty($objects)) {
            return '';
        }

        $this->pdfTemplate->getAdapter()->resetTemplate();
        $pdf = $this->pdfTemplate->getPdf($objects);
        if (!$pdf) {
            return '';
        }

        return $pdf->render();
    }

    /**
     * @param $observer
     * @return \Magento\Sales\Api\Data\OrderInterface|string
     */
    public function getOrder($observer)
    {
        $templateVars = $observer->getTemplateVars();
        if (!isset($templateVars['order'])) {
            return '';
        }

        $orderVar = $templateVars['order'];
        $orderId = $orderVar->getId();

        $order = $this->orderRepository->get($orderId);
        return $order;
    }

    /**
     * @param $observer
     * @return \Magento\Sales\Api\Data\InvoiceInterface|string
     */
    public function getInvoice($observer)
    {
        $templateVars = $observer->getTemplateVars();
        if (!isset($templateVars['invoice'])) {
            return '';
        }

        $invoiceVariable = $templateVars['invoice'];
        $invoiceId = $invoiceVariable->getId();

        try {
            $invoice = $this->invoiceRepository->get($invoiceId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $invoice = $invoiceVariable;
        }

        return $invoice;
    }

    /**
     * @param $observer
     * @return \Magento\Sales\Api\Data\ShipmentInterface|string
     */
    public function getShipment($observer)
    {
        $templateVars = $observer->getTemplateVars();
        if (!isset($templateVars['shipment'])) {
            return '';
        }

        $shipmentVariable = $templateVars['shipment'];
        $shipmentId = $shipmentVariable->getId();

        $shipment = $this->shipmentRepository->get($shipmentId);
        return $shipment;
    }

    /**
     * @param $observer
     * @return \Magento\Sales\Api\Data\CreditmemoInterface|string
     */
    public function getCreditmemo($observer)
    {
        $templateVars = $observer->getTemplateVars();
        if (!isset($templateVars['creditmemo'])) {
            return '';
        }

        $creditmemoVariable = $templateVars['creditmemo'];
        $creditmemoId = $creditmemoVariable->getId();

        $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        return $creditmemo;
    }

    /**
     * @param $observer
     * @param array $configPaths
     * @return bool
     */
    public function validateTemplate($observer, $configPaths = [])
    {
        $templateId = $observer->getTemplateId();
        $storeId = $this->getStoreId($observer);

        foreach ($configPaths as $xmlPath) {
            $configValue = $this->scopeConfig->getValue(
                $xmlPath,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            if ($configValue == $templateId) {
                return true;
            }
        }

        return false;
    }
}
