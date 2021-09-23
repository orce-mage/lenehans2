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
 * Draw pdf element Qrcode
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Qrcode extends \Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Qrcode';

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
        $this->drawQrCode();
        return $this;
    }

    /**
     * Draw image
     */
    public function drawQrCode()
    {
        $attributes = $this->getAttributes();
        $qrcodeData = $attributes['qrcode_data'];
        $qrcodeData = $this->processFilters($qrcodeData);

        if (empty($qrcodeData)) {
            return;
        }

        $tmpDir = $this->fileSystem->getDirectoryWrite(DirectoryList::TMP)->getAbsolutePath();
        $fileName = 'qr'.time().'.png';
        $pathToFile = $tmpDir.$fileName;
        $height = $this->removePx($attributes['height']);
        $width = $this->removePx($attributes['width']);
        $factor = 1;

        $image = $this->qrCodeHelper->png($qrcodeData, $width, $height, $pathToFile);

        $y = $this->toPoint($attributes['top']);
        $x = $this->toPoint($attributes['left']);
        $width = $this->toPoint($attributes['width']);
        $height = $this->toPoint($attributes['height']);

        $this->pdf->Image($pathToFile, $x, $y, $width, $height, '', '', '', false, 72, '', false, false, 0);
    }
}
