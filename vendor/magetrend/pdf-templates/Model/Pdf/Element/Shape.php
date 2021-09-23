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

/**
 * Draw pdf element shape
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Shape extends \Magetrend\PdfTemplates\Model\Pdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Shape';

    /**
     * Draw shape element
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

        $this->drawBox();
        return $this;
    }

    /**
     * Draw box
     */
    public function drawBox()
    {
        $attributes = $this->getAttributes();

        $y1 = $this->invertY($this->toPoint($attributes['top']));
        $x1 = $this->toPoint($attributes['left']);

        $x2 = $x1 + $this->toPoint($attributes['width']);
        $y2 = $y1 - $this->toPoint($attributes['height']);

        if (isset($attributes['rotate'])) {
            $rotate = str_replace('deg', '', $attributes['rotate']);
            if ($rotate != 0) {
                $this->pdfPage->rotate($x1+($x2 - $x1)/2, $y1+($y2-$y1)/2, deg2rad($rotate)*-1);
            }
        }

        if (isset($attributes['background_color'])) {
            $color = $this->getPdfColor($attributes['background_color']);
            $this->pdfPage->setFillColor($color);
            $this->pdfPage->setLineColor($color);

            $this->pdfPage->setLineWidth(0);
            $this->pdfPage->drawRectangle($x1, $y1, $x2, $y2, \Zend_Pdf_Page::SHAPE_DRAW_FILL);
        }

        if (isset($attributes['border_size']) && $attributes['border_size'] > 0) {
            $attributes['border_style'] = 'solid';
            $this->drawBorders(
                $attributes['top'],
                $attributes['left'],
                $attributes['width'],
                $attributes['height'],
                $attributes['border_size'],
                $attributes['border_color'],
                $attributes['border_style']
            );
        }

        if (isset($attributes['rotate'])) {
            $rotate = str_replace('deg', '', $attributes['rotate']);
            if ($rotate != 0) {
                $this->pdfPage->rotate($x1+($x2 - $x1)/2, $y1+($y2-$y1)/2, deg2rad($rotate));
            }
        }
    }
}
