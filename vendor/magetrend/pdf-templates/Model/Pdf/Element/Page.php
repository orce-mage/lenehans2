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

use Magento\Framework\Exception\LocalizedException;
use Magetrend\PdfTemplates\Model\Pdf\Element;

/**
 * Draw pdf element page
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Page extends \Magetrend\PdfTemplates\Model\Pdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Page';

    /**
     * @var
     */
    public $textElement;

    /**
     * @var int
     */
    public $blockHeight = 0;

    public function draw($pdfPage, $elemetData, $source, $template, $elements, $currentPage)
    {
        parent::draw($pdfPage, $elemetData, $source, $template, $elements, $currentPage);

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

        $lines = $this->splitStringToLines(
            $this->getContent(),
            $this->toPoint($width),
            $fontCode,
            $this->toPoint($fontSize)
        );

        $boxHeight = $paddingTop + $paddingBottom + count($lines) * $lineHeight;
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
            'align' => isset($attributes['align'])?$attributes['align']:'left',
            'padding' => [$paddingTop, $paddingRight, $paddingBottom, $paddingLeft]
        ];
    }

    public function getContent()
    {
        $attributes = $this->getAttributes();
        if (empty($attributes['translate_content'])) {
            return '';
        }
        $content = $this->cleanString($attributes['translate_content']);
        $separator = str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], '', $content);

        if (empty($separator)) {
            $numbers = [$content];
        } else {
            $numbers = explode($separator, $content);
        }

        if (!is_numeric($numbers[0])) {
            throw new LocalizedException(__('Bad page no format'));
        }

        $pageNo = $numbers[0];
        $pageCount = false;

        if (isset($numbers[1]) && is_numeric($numbers[1])) {
            $pageCount = $numbers[1];
        }

        if ($pageCount && $pageNo > $pageCount) {
            $tmpPageNo = $pageNo;
            $pageNo = $pageCount;
            $pageCount = $tmpPageNo;
        }

        if (!$pageCount) {
            $pageCount = 'PAGE_COUNT';
        }

        $content = implode($separator, $numbers);
        $content = str_replace([$pageNo, $pageCount], [
            'CURRENT_PAGE',
            'PAGE_COUNT'
        ], $content);

        $content = str_replace(['CURRENT_PAGE', 'PAGE_COUNT'], [
            $this->registry->registry('pdf_page_current'),
            $this->registry->registry('pdf_page_count')
        ], $content);

        return $content;
    }


    public function drawBox()
    {
        $attributes = $this->getAttributes();
        if (empty($this->textElement)) {
            return;
        }
        $height = $this->textElement['box_height'];

        $y1 = $this->invertY($this->toPoint($attributes['top']));
        $x1 = $this->toPoint($attributes['left']);

        $x2 = $x1 + $this->toPoint($attributes['width']);
        $y2 = $y1 - $this->toPoint($height);

        $method = \Zend_Pdf_Page::SHAPE_DRAW_FILL;

        if (isset($attributes['background_color'])) {
            if ($attributes['background_color'] == 'rgba(0, 0, 0, 0)') {
                $this->pdfPage->setAlpha(0);
            }
            $color = $this->getPdfColor($attributes['background_color']);
            $this->pdfPage->setFillColor($color);
            $this->pdfPage->setLineColor($color);

            $this->pdfPage->setLineWidth(0);
            $this->pdfPage->drawRectangle($x1, $y1, $x2, $y2, $method);
            if ($attributes['background_color'] == 'rgba(0, 0, 0, 0)') {
                $this->pdfPage->setAlpha(1);
            }
        }

        if (isset($attributes['border_size']) && $attributes['border_size'] > 0) {
            $attributes['border_style'] = 'solid';
            $this->drawBorders(
                $attributes['top'],
                $attributes['left'],
                $attributes['width'],
                $height,
                $attributes['border_size'],
                $attributes['border_color'],
                $attributes['border_style']
            );
        }
    }

    public function writeText()
    {
        $attributes = $this->getAttributes();
        if (empty($this->textElement)) {
            return;
        }

        $align = $this->textElement['align'];
        $fontSize = $this->textElement['font_size'];
        $fontCode = $this->textElement['font_code'];
        $padding = $this->textElement['padding'];
        $lineHeight = $this->textElement['line_height'];

        $y1 = $this->removePx($attributes['top']) + $padding[0];
        $x1 = $this->removePx($attributes['left']) + $padding[3];
        if ($align == Element::ALIGN_RIGHT) {
            $width = $this->removePx($attributes['width']) - $padding[3] - $padding[1];
            $x1 += $width;
        }

        $font = $this->getPdfFont($fontCode);
        $color = $this->getPdfColor($this->textElement['color']);
        $this->pdfPage->setFont($font, $this->toPoint($fontSize))
            ->setFillColor($color);

        $this->drawTextLines(
            $this->textElement['text'],
            $this->toPoint($x1),
            $this->toPoint($y1),
            $this->toPoint($lineHeight),
            $this->toPoint($fontSize),
            $align
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
