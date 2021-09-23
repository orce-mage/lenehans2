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
class Image extends \Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Image';
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
        $this->drawImage();
        return $this;
    }

    /**
     * Draw image
     */
    public function drawImage()
    {
        $attributes = $this->getAttributes();
        $y = $this->toPoint($attributes['top']);
        $x = $this->toPoint($attributes['left']);
        $width = $this->toPoint($attributes['width']);
        $height = $this->toPoint($attributes['height']);

        if (isset($attributes['background_color'])) {
            $fillColor = $this->getPdfColor($attributes['background_color']);
            $this->pdf->Rect($x, $y, $width, $height, 'DF', ['width' => 0], $fillColor);
        }

        $imagePath = $this->getImagePath($attributes['src']);
        $this->pdf->Image($imagePath, $x, $y, $width, $height, '', '', '', false, 72, '', false, false, 0);
    }

    /**
     * Returns image path
     * @param $imgUrl
     * @return string
     */
    public function getImagePath($imgUrl)
    {
        if (substr_count($imgUrl, 'pdftemplates') == 1) {
            $path = $this->fileSystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('media/pdftemplates');
            $imgUrl = explode('pdftemplates', $imgUrl);
            $imgPath = rtrim($path, '/').$imgUrl[1];
        } else {
            $moduleName = $this->getModuleNameFromUrl($imgUrl);
            $subPath = explode('/images/', $imgUrl);
            $subPath = end($subPath);

            $imgPath = $this->repository->createAsset(
                $moduleName.'::images/'.$subPath,
                ['area' => 'adminhtml']
            )->getSourceFile();
        }

        return $imgPath;
    }


    public function getModuleNameFromUrl($imgUrl)
    {
        $moduleName = explode('/images/', $imgUrl);
        $moduleName = explode('/', $moduleName[0]);
        $moduleName = end($moduleName);
        return $moduleName;
    }
}
