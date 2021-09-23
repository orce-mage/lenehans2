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
 * Draw pdf element Qrcode
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Qrcode extends \Magetrend\PdfTemplates\Model\Pdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Qrcode';

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
