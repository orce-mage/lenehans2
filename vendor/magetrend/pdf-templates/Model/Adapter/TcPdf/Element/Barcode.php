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

namespace Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Draw pdf element image
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Barcode extends \Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Barcode';

    /**
     * Draw element
     *
     * @param $pdf
     * @param $elemetData
     * @param $source
     * @param $template
     * @param $elements
     * @param $currentPage
     * @return $this
     */
    public function draw($pdf, $elemetData, $source, $template, $elements, $currentPage)
    {
        parent::draw($pdf, $elemetData, $source, $template, $elements, $currentPage);
        $this->drawBarCode();
        return $this;
    }

    /**
     * Draw image
     */
    public function drawBarCode()
    {
        $attributes = $this->getAttributes();
        $barcodeData = $attributes['barcode_data'];
        $barcodeData = $this->processFilters($barcodeData);

        if (empty($barcodeData)) {
            return;
        }

        $tmpDir = $this->fileSystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath();
        $fileName = 'barcode'.time().'.png';
        $pathToFile = $tmpDir.$fileName;
        $height = $this->removePx($attributes['height']);
        $width = $this->removePx($attributes['width']);

        $image = \Magetrend\PdfTemplates\Helper\Barcode::draw(
            $barcodeData,
            $width,
            $height
        );

        imagepng($image, $pathToFile);
        imagedestroy($image);

        $y = $this->toPoint($attributes['top']);
        $x = $this->toPoint($attributes['left']);
        $width = $this->toPoint($attributes['width']);
        $height = $this->toPoint($attributes['height']);

        $this->pdf->Image($pathToFile, $x, $y, $width, $height, '', '', '', false, 96, '', false, false, 0, false);
    }
}
