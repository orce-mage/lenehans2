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

namespace Magetrend\PdfTemplates\Plugin\Sales\Model\Order\Pdf;

/**
 * Invoice PDF plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Invoice
{
    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magetrend\PdfTemplates\Model\TemplateFactory
     */
    public $pdfTemplate;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Invoice constructor.
     *
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magetrend\PdfTemplates\Model\TemplateFactory $pdfTemplateFactory
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\Template $pdfTemplate,
        \Magento\Framework\Registry $registry
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->pdfTemplate = $pdfTemplate;
        $this->registry = $registry;
    }

    /**
     * Replace invoice renderer
     *
     * @param \Magento\Sales\Model\Order\Pdf\Invice $invoicePdfModel
     * @param callable $proceed
     * @param array $invoices
     * @return \Zend_Pdf
     */
    public function aroundGetPdf($invoicePdfModel, callable $proceed, $invoices = [])
    {
        $storeId = $this->getStoreId($invoices);
        if (!$this->moduleHelper->isActive($storeId)
            || $this->registry->registry(\Magetrend\PdfTemplates\Helper\Data::REGISTRY_IGNORE_KEY)) {
            return $proceed($invoices);
        }

        return $this->pdfTemplate->getPdf($invoices);
    }

    /**
     * Returns order store id
     *
     * @param $invoices
     * @return int
     */
    public function getStoreId($invoices)
    {
        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                return $invoice->getStoreId();
            }
        }
        return 0;
    }
}
