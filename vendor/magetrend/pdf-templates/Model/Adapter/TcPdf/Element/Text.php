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

use Magetrend\PdfTemplates\Model\Config\Source\Direction;

/**
 * Draw pdf element text
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Text extends \Magetrend\PdfTemplates\Model\Adapter\TcPdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Text';

    /**
     * @var
     */
    public $textElement;

    /**
     * @var int
     */
    private $blockHeight = 0;

    /**
     * Draw text element
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
        $this->prepareData();
        $this->drawBox();
        $this->writeText();
        return $this;
    }

    public function prepareData()
    {
        $attributes = $this->getAttributes();
        if (empty($attributes['translate_content'])) {
            return;
        }

        $fontSize = $this->removePx($attributes['font_size']);
        $paddingTop = $this->removePx($attributes['padding_top']);
        $paddingBottom = $this->removePx($attributes['padding_bottom']);
        $paddingLeft = $this->removePx($attributes['padding_left']);
        $paddingRight = $this->removePx($attributes['padding_right']);
        $lineHeight = $this->removePx($attributes['text_line_height']);
        $fontCode = $attributes['font'];

        $boxWidth = $this->removePx($attributes['width']);
        $width = $boxWidth - $paddingLeft - $paddingRight;
        $font = $this->getPdfFont($attributes['font']);
        $content = $this->cleanString($attributes['translate_content']);

        $content = $this->processFilters($content);

        $lines = $this->splitStringToLines(
            $content,
            $this->toPoint($width),
            $fontCode,
            $this->toPoint($fontSize)
        );

        $boxHeight = $paddingTop + $paddingBottom + count($lines) * $lineHeight;
        if ($boxHeight < $attributes['height']) {
            $boxHeight = $attributes['height'];
        }
        $this->blockHeight = $boxHeight;

        $this->textElement = [
            'text' => $lines,
            'color' => $attributes['color'],
            'font_code' => $fontCode,
            'font_size' => $fontSize,
            'line_height' => $lineHeight,
            'width' => $width,
            'box_height' => $boxHeight,
            'box_width' => $boxWidth,
            'padding' => [$paddingTop, $paddingRight, $paddingBottom, $paddingLeft]
        ];
    }

    public function drawBox()
    {
        $attributes = $this->getAttributes();
        if (empty($this->textElement)) {
            return;
        }
        $height = $this->toPoint($this->textElement['box_height']);
        $width = $this->toPoint($this->textElement['box_width']);

        $y1 = $this->toPoint($attributes['top']);
        $x1 = $this->toPoint($attributes['left']);

        $fillColor = [];
        if (isset($attributes['background_color'])) {
            $fillColor = $this->getPdfColor($attributes['background_color']);
            if (isset($fillColor[3])) {
                $this->pdf->SetAlpha($fillColor[3]);
            }

            $this->pdf->Rect($x1, $y1, $width, $height, 'DF', ['width' => 0], $fillColor);

            if (isset($fillColor[3])) {
                $this->pdf->SetAlpha(1);
            }
        }
    }

    public function writeText()
    {
        $attributes = $this->getAttributes();
        if (empty($this->textElement)) {
            return;
        }

        $fontSize = $this->textElement['font_size'];
        $fontCode = $this->textElement['font_code'];
        $padding = $this->textElement['padding'];
        $lineHeight = $this->textElement['line_height'];

        $y1 = $this->removePx($attributes['top']) + $padding[0];
        $x1 = $this->removePx($attributes['left']) + $padding[3];

        $width = $this->removePx($attributes['width']) - $padding[3] - $padding[1];
        $font = $this->getPdfFont($fontCode);

        $this->pdf->SetFont($font, '', $this->toPoint($fontSize));
        $this->setTextColor($this->textElement['color']);

        $direction = isset($attributes['direction'])?$attributes['direction']:Direction::LTR;
        $this->drawTextLines(
            $this->textElement['text'],
            $this->toPoint($x1),
            $this->toPoint($y1),
            $this->toPoint($lineHeight),
            $this->toPoint($fontSize),
            null,
            [],
            $direction,
            $this->toPoint($width)
        );
    }

    public function getBottomY()
    {
        $attributes = $this->getAttributes();
        return $this->removePx($attributes['top']) + $this->textElement['box_height'];
    }

    public function getInfo($elementData)
    {
        $attributes = $this->getAttributes();
        $info = parent::getInfo($elementData);
        $info['pdf_height'] = $this->blockHeight;
        return $info;
    }
}
