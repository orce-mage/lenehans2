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

namespace Magetrend\PdfTemplates\Model\Pdf\Element;

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
class Barcode extends \Magetrend\PdfTemplates\Model\Pdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Barcode';

    /**
     * Draw element
     *
     * @param $pdfPage
     * @param $elemetData
     * @param $source
     * @param $template
     * @param $elements
     * @param $currentPage
     * @return $this
     */
    public function draw($pdfPage, $elemetData, $source, $template, $elements, $currentPage)
    {
        parent::draw($pdfPage, $elemetData, $source, $template, $elements, $currentPage);

        if (!$this->canPrint()) {
            return $this;
        }
        
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

        $imageXY = $this->getImagePosition(
            $attributes['top'],
            $attributes['left'],
            $attributes['width'],
            $attributes['height']
        );

        $image = \Zend_Pdf_Image::imageWithPath($pathToFile);
        $this->pdfPage->drawImage($image, $imageXY['x1'], $imageXY['y1'], $imageXY['x2'], $imageXY['y2']);
    }
}
