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

/**
 * Draw pdf element shape
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Shape extends \Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Shape';

    /**
     * Draw shape element
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
        $this->drawBox();
        return $this;
    }

    /**
     * Draw box
     */
    public function drawBox()
    {
        $attributes = $this->getAttributes();

        $y1 = $this->toPoint($attributes['top']);
        $x1 = $this->toPoint($attributes['left']);

        $width = $this->toPoint($attributes['width']);
        $height = $this->toPoint($attributes['height']);

        $xCenter = $x1 + ($width / 2);
        $yCenter = $y1 + ($height / 2);

        if (isset($attributes['rotate'])) {
            $rotate = str_replace('deg', '', $attributes['rotate']);
            if ($rotate != 0) {
                $this->pdf->Rotate($rotate*-1, $xCenter, $yCenter);
            }
        }

        if (isset($attributes['background_color'])) {
            $fillColor = $this->getPdfColor($attributes['background_color']);
            $this->pdf->Rect($x1, $y1, $width, $height, 'DF*', ['width' => 0], $fillColor);
        }

        if (isset($attributes['border_size']) && $attributes['border_size'] > 0) {
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
                $this->pdf->Rotate($rotate, $xCenter, $yCenter);
            }
        }
    }
}
