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

namespace Magetrend\PdfTemplates\Model\Adapter\TcPdf;

/**
 * Pdf element factory
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class ElementFactory
{
    public $objectManager;

    private $elementClass = [];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function getModelByType($type)
    {
        $class = '';
        switch ($type) {
            case 'element_text':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Text';
                break;
            case 'element_items':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Items';
                break;
            case 'element_image':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Image';
                break;
            case 'element_barcode':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Barcode';
                break;
            case 'element_qrcode':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Qrcode';
                break;
            case 'element_shape':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Shape';
                break;
            case 'element_total':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Total';
                break;
            case 'element_track':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Track';
                break;
            case 'element_page':
                $class = 'Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element\Page';
                break;
        }

        if (!isset($this->elementClass[$type])) {
            $this->elementClass[$type] = $this->objectManager->create($class);
        }

        return $this->elementClass[$type];
    }

    public function resetInstances()
    {
        $this->elementClass = [];
    }
}
