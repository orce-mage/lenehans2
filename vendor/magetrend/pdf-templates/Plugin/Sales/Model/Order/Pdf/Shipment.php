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
 * Credit Memo PDF plugin class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Shipment
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
     * Replace shipment renderer
     *
     * @param \Magento\Sales\Model\Order\Pdf\Invice $shipmentPdfModel
     * @param callable $proceed
     * @param array $shipments
     * @return \Zend_Pdf
     */
    public function aroundGetPdf($shipmentPdfModel, callable $proceed, $shipments = [])
    {
        $storeId = $this->getStoreId($shipments);
        if (!$this->moduleHelper->isActive($storeId)
            || $this->registry->registry(\Magetrend\PdfTemplates\Helper\Data::REGISTRY_IGNORE_KEY)
        ) {
            return $proceed($shipments);
        }

        return $this->pdfTemplate->getPdf($shipments);
    }

    /**
     * Returns order store id
     *
     * @param $shipments
     * @return int
     */
    public function getStoreId($shipments)
    {
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                return $shipment->getStoreId();
            }
        }
        return 0;
    }
}
