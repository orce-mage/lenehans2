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
class Image extends \Magetrend\PdfTemplates\Model\Pdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Image';
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

        $this->drawImage();
        return $this;
    }

    /**
     * Draw image
     */
    public function drawImage()
    {
        $attributes = $this->getAttributes();
        $imageXY = $this->getImagePosition(
            $attributes['top'],
            $attributes['left'],
            $attributes['width'],
            $attributes['height']
        );

        $imgPath = $this->getImagePath($attributes['src']);
        $imgPath = $this->moduleHelper->removeInterlaceFromPng($imgPath, true);

        try {
            $image = \Zend_Pdf_Image::imageWithPath($imgPath);
        } catch (\Zend_Pdf_Exception $e) {
            return;
        }

        if (isset($attributes['background_color']) && $attributes['background_color'] != 'rgba(0, 0, 0, 0)') {
            $color = $this->getPdfColor($attributes['background_color']);
            $this->pdfPage->setFillColor($color);
            $this->pdfPage->setLineColor($color);

            $this->pdfPage->setLineWidth(0);
            $this->pdfPage->drawRectangle(
                $imageXY['x1'],
                $imageXY['y1'],
                $imageXY['x2'],
                $imageXY['y2'],
                \Zend_Pdf_Page::SHAPE_DRAW_FILL
            );
        }

        $this->pdfPage->drawImage($image, $imageXY['x1'], $imageXY['y1'], $imageXY['x2'], $imageXY['y2']);
    }

    /**
     * Returns image path
     * @param $imgUrl
     * @return string
     */
    public function getImagePath($imgUrl)
    {
        $eImgUrl = explode('/', $imgUrl);
        if (in_array('pdftemplates', $eImgUrl)) {
            $path = $this->fileSystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('media/pdftemplates');
            $imgUrl = explode('/pdftemplates/', $imgUrl);
            $imgPath = rtrim($path, '/').'/'.$imgUrl[1];
        } else {
            $moduleName = $this->getModuleNameFromUrl($imgUrl);
            $subPath = explode('/images/', $imgUrl);
            $subPath = end($subPath);

            $file = $this->repository->createAsset(
                $moduleName.'::images/'.$subPath,
                ['area' => 'adminhtml']
            )->getSourceFile();
            return $file;
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
