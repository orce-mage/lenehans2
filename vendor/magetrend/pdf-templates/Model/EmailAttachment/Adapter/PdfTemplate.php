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

namespace Magetrend\PdfTemplates\Model\EmailAttachment\Adapter;

class PdfTemplate extends \Magento\Framework\DataObject
{
    /**
     * @var \Magetrend\PdfTemplates\Model\Template
     */
    public $pdfTemplate;

    public $pdfTemplateFactory;

    public $registry;

    /**
     * Order constructor.
     * @param \Magetrend\PdfTemplates\Model\Template $pdfTemplate
     * @param array $data
     */
    public function __construct(
        \Magetrend\PdfTemplates\Model\Template $pdfTemplate,
        \Magetrend\PdfTemplates\Model\TemplateFactory $pdfTemplateFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->pdfTemplate = $pdfTemplate;
        $this->pdfTemplateFactory = $pdfTemplateFactory;
        $this->registry = $registry;
        parent::__construct($data);
    }

    public function getFileName()
    {
        $pdfTemplate = $this->getPdfTemplate();
        $sourceIndex = '';
        switch ($pdfTemplate->getType()) {
            case 'order':
                $sourceIndex = 'order';
                break;
            case 'invoice':
                $sourceIndex = 'invoice';
                break;
            case 'shipment':
                $sourceIndex = 'shipment';
                break;
            case 'creditmemo':
                $sourceIndex = 'creditmemo';
                break;
        }

        $attachment = $this->getAttachment();
        $fileNameTemplate = $attachment->getFileName();
        $templateVars = $this->getTemplateVars();

        if (empty($sourceIndex) || !isset($templateVars[$sourceIndex])) {
            return $fileNameTemplate;
        }

        $source = $templateVars[$sourceIndex];
        return (string)__($fileNameTemplate, $source->getData());
    }

    public function getFileContent()
    {
        $pdfTemplate = $this->getPdfTemplate();
        if (!$pdfTemplate->getId()) {
            return '';
        }

        $this->registry->unregister('mt_pdf_force_template_id');
        $this->registry->register('mt_pdf_force_template_id', $pdfTemplate->getId());
        $pdf = '';
        switch ($pdfTemplate->getType()) {
            case 'order':
                $pdf = $this->getOrderPdf();
                break;
            case 'invoice':
                $pdf = $this->getInvoicePdf();
                break;
            case 'shipment':
                $pdf = $this->getShipmentPdf();
                break;
            case 'creditmemo':
                $pdf = $this->getCreditmemoPdf();
                break;
        }

        if (empty($pdf)) {
            return '';
        }

        return $pdf->render();
    }




    public function getOrderPdf()
    {
        $templateVars = $this->getTemplateVars();
        if (!isset($templateVars['order'])) {
            return '';
        }

        $source = $templateVars['order'];
        $this->pdfTemplate->getAdapter()->resetTemplate();
        $pdf = $this->pdfTemplate->getPdf([$source]);

        return $pdf;
    }

    public function getInvoicePdf()
    {
        $templateVars = $this->getTemplateVars();
        if (!isset($templateVars['invoice'])) {
            return '';
        }

        $source = $templateVars['invoice'];
        $this->pdfTemplate->getAdapter()->resetTemplate();
        $pdf = $this->pdfTemplate->getPdf([$source]);

        return $pdf;
    }

    public function getShipmentPdf()
    {
        $templateVars = $this->getTemplateVars();
        if (!isset($templateVars['shipment'])) {
            return '';
        }

        $source = $templateVars['shipment'];
        $this->pdfTemplate->getAdapter()->resetTemplate();
        $pdf = $this->pdfTemplate->getPdf([$source]);

        return $pdf;
    }

    public function getCreditmemoPdf()
    {
        $templateVars = $this->getTemplateVars();
        if (!isset($templateVars['creditmemo'])) {
            return '';
        }

        $source = $templateVars['creditmemo'];
        $this->pdfTemplate->getAdapter()->resetTemplate();
        $pdf = $this->pdfTemplate->getPdf([$source]);

        return $pdf;
    }

    public function getTemplateId()
    {
        $attachment = $this->getAttachment();
        $type = $attachment->getAttachmentType();
        $templateId = str_replace('pdf_template_', '', $type);

        return $templateId;
    }

    public function getPdfTemplate()
    {
        $pdfTemplate = $this->pdfTemplateFactory->create();
        $templateId = $this->getTemplateId();
        if (is_numeric($templateId)) {
            $pdfTemplate->load($templateId);
        }

        return $pdfTemplate;
    }
}