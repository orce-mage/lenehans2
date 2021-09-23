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

namespace Magetrend\PdfTemplates\Model\Adapter\TcPdf;

use Magetrend\PdfTemplates\Model\Config\Source\Direction;

/**
 * Draw pdf element class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Element
{
    const ALIGN_LEFT = 'left';

    const ALIGN_RIGHT = 'right';

    public $configClassName = '';

    /**
     * @var \Magento\Sales\Model\AbstractModel
     */
    public $source;

    /**
     * @var \Magetrend\PdfTemplates\Model\Config\Source\Font
     */
    public $fontConfig;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    public $invoice;

    /**
     * @var \Magetrend\PdfTemplates\Model\Template
     */
    public $template;

    /**
     * @var \TCPDF
     */
    public $pdf;

    /**
     * @var array
     */
    public $elementData;

    /**
     * @var int
     */
    public $ppi;

    /**
     * @var \Zend_Pdf_Page
     */
    public $currentPage;

    /**
     * @var int
     */
    public $totalPages;

    /**
     * @var int
     */
    public $pageHeight;

    /**
     * @var int
     */
    public $pageWidth;

    /**
     * @var array
     */
    public $fonts = [];

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $fileSystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    public $readFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    public $repository;

    /**
     * @var
     */
    public $order = null;

    /**
     * @var
     */
    public $elements;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config
     */
    public $pdfConfig;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Total\Factory
     */
    public $pdfTotalFactory;

    /**
     * @var bool
     */
    public $bottomY = false;

    /**
     * @var int
     */
    public $width = 0;

    /**
     * @var int
     */
    public $height = 0;

    /**
     * @var int
     */
    public $top = 0;

    /**
     * @var int
     */
    public $left = 0;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    public $taxData;

    /**
     * @var Element\Items\Invoice\Bundle
     */
    public $bundleItemRenderer;

    /**
     * @var Element\Items\Invoice\DefaultRenderer
     */
    public $defaultItemRenderer;

    /**
     * @var \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\Configurable
     */
    public $configurableItemRenderer;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    public $magentoPdfTools;

    /**
     * @var \Magetrend\PdfTemplates\Helper\QRcode
     */
    public $qrCodeHelper;

    public $objectManager;

    public $moduleHelper;

    public $attributes = null;

    public $registry;

    public $decorator;

    public $typeManager;

    private $fillColor = '#000000';

    public $currencySymbol = null;

    /**
     * Element constructor.
     *
     * @param \Magetrend\PdfTemplates\Model\Config\Source\Font $fontConfig
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\View\Asset\Repository $repository
     * @param \Magento\Sales\Model\Order\Pdf\Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param Filter\Invoice $invoiceFilter
     * @param \Magento\Tax\Helper\Data $taxData
     * @param Element\Items\Invoice\DefaultRenderer $defaultItemRenderer
     * @param Element\Items\Invoice\Bundle $bundleItemRenderer
     * @param \Magento\Sales\Model\Order\Pdf\Invoice $magentoPdfTools
     * @param \Magetrend\PdfTemplates\Helper\QRcode $qrCodeHelper
     */
    public function __construct(
        \Magetrend\PdfTemplates\Model\Config\Source\Font $fontConfig,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\View\Asset\Repository $repository,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Tax\Helper\Data $taxData,
        \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\DefaultRenderer $defaultItemRenderer,
        \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\Bundle $bundleItemRenderer,
        \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\Configurable $configurableItemRenderer,
        \Magento\Sales\Model\Order\Pdf\Invoice $magentoPdfTools,
        \Magetrend\PdfTemplates\Helper\QRcode $qrCodeHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magento\Framework\Registry $registry,
        \Magetrend\PdfTemplates\Model\Pdf\Decorator $decorator,
        \Magetrend\PdfTemplates\Model\TypeManager $typeManager
    ) {
        $this->fontConfig = $fontConfig;
        $this->fileSystem = $fileSystem;
        $this->readFactory = $readFactory;
        $this->repository = $repository;
        $this->pdfConfig = $pdfConfig;
        $this->pdfTotalFactory = $pdfTotalFactory;
        $this->taxData = $taxData;
        $this->defaultItemRenderer = $defaultItemRenderer;
        $this->bundleItemRenderer = $bundleItemRenderer;
        $this->configurableItemRenderer = $configurableItemRenderer;
        $this->magentoPdfTools = $magentoPdfTools;
        $this->qrCodeHelper = $qrCodeHelper;
        $this->objectManager = $objectManager;
        $this->moduleHelper = $moduleHelper;
        $this->registry = $registry;
        $this->decorator = $decorator;
        $this->typeManager = $typeManager;
    }

    /**
     * Draw Element
     *
     * @param $pdf
     * @param $elemetData
     * @param $source
     * @param $template
     * @param $elements
     * @param $currentPage
     */
    public function draw($pdf, $elemetData, $source, $template, $elements, $currentPage)
    {
        $this->invoice = $source;
        $this->source = $source;
        $this->template = $template;
        $this->pdf = $pdf;
        $this->elementData = $elemetData;
        $this->elements = $elements;
        $this->attributes = null;
        $this->ppi = $template->getPpi();
        $this->pageHeight = $this->pdf->getPageHeight();
        $this->pageWidth = $this->pdf->getPageWidth();
        $this->currentPage = $currentPage;
        $this->order = $source->getOrder();
        $this->currencySymbol = $this->moduleHelper->getCurrencySymbol($this->getSource()->getStoreId());
    }

    /**
     * Returns element attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->attributes == null) {
            $this->attributes = $this->moduleHelper->removePx($this->elementData['attributes'], $this->getFieldListToRemovePx());
        }

        return $this->attributes;
    }

    /**
     * Resets page text format
     */
    public function resetLines()
    {

    }

    /**
     * Convert px to points
     *
     * @param $px
     * @return float|int
     */
    public function toPoint($px)
    {
        $px = str_replace('px', '', $px);
        $px = ((int)$px) * 72 / $this->ppi;
        return $px;
    }

    /**
     * Convert px to points
     *
     * @param $px
     * @return float|int
     */
    public function toPx($point)
    {
        $px = ($point * $this->ppi ) / 72;
        return (int)$px;
    }

    /**
     * Invert Y to bottom to top cordinates
     *
     * @param $point
     * @return int
     */
    public function invertY($point)
    {
        return $point;
    }

    /**
     * Returns pdf font name by font code
     *
     * @param $fontCode
     * @return string
     */
    public function getPdfFont($fontCode)
    {
        if (!isset($this->fonts[$fontCode])) {
            $fontList = $this->fontConfig->getFontList();
            foreach ($fontList as $font) {
                if ($fontCode == $font['code']) {
                    $this->fonts[$fontCode] = \TCPDF_FONTS::addTTFfont($font['path'], 'TrueTypeUnicode', '', 96);
                }
            }
        }

        if (isset($this->fonts[$fontCode])) {
            return $this->fonts[$fontCode];
        }

        return \TCPDF_FONTS::addTTFfont($fontList[0]['path'], 'TrueTypeUnicode', '', 96);
    }

    /**
     * Removes all hidden char from string
     *
     * @param $string
     * @return mixed|string
     */
    public function cleanString($string)
    {
        $s = trim($string);
        $encoding = mb_detect_encoding($s, mb_detect_order(), false);
        if ($encoding == "UTF-8") {
            $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        }
        $s = iconv(mb_detect_encoding($s, mb_detect_order(), false), "UTF-8//IGNORE", $s);
        $s = preg_replace(
            '/(?>[\x00-\x1F]|\xC2[\x80-\x9F]|\xE2[\x80-\x8F]{2}|\xE2\x80[\xA4-\xA8]|\xE2\x81[\x9F-\xAF])/',
            ' ',
            $s
        );
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    /**
     * Calculate element top-left corner position
     *
     * @param $top
     * @param $left
     * @return array
     */
    public function getTopPosition($top, $left)
    {
        $y = $this->toPoint($top);
        $x = $this->toPoint($left);
        return ['x' => $x, 'y' => $y];
    }

    /**
     * Calculate element bottom-right corner position
     *
     * @param $top
     * @param $left
     * @return array
     */
    public function getBottomPosition($top, $left, $width, $height)
    {
        $topPosition = $this->getTopPosition($top, $left);
        $x = $topPosition['x'] + $this->toPoint($width);
        $y = $topPosition['y'] + $this->toPoint($height);
        return ['x' => $x, 'y' => $y];
    }

    /**
     * Draw element borders
     *
     * @param $top
     * @param $left
     * @param $width
     * @param $height
     * @param $size
     * @param $color
     * @param string $type
     */
    public function drawBorders($top, $left, $width, $height, $size, $color, $type = 'solid')
    {
        switch ($type) {
            case 'solid':
                $this->drawBordersSolid($top, $left, $width, $height, $size, $color);
                break;
            case 'dashed':
                $this->drawBordersDashed($top, $left, $width, $height, $size, $color);
                break;
        }
    }

    public function drawBordersDashed($top, $left, $width, $height, $size, $color)
    {
        $top = $this->toPoint($top);
        $left = $this->toPoint($left);
        $width = $this->toPoint($width);
        $height = $this->toPoint($height);
        $size = $this->toPoint($size);
        $color = $this->getPdfColor($color);

        if (!is_array($size)) {
            $size = [$size, $size, $size, $size];
        }

        if ($size[0] > 0) {
            $this->drawDashedLine($left, $top, $width, $size[0], $color);
        }

        if ($size[2] > 0) {
            $bPos = $top + $height - $size[2];
            $this->drawDashedLine($left, $bPos, $width, $size[2], $color);
        }

        if ($size[1] > 0) {
            $bPos = $top + $height - $size[1];
            $rPos = $left + $width - $size[1];
            $this->drawDashedLine($rPos, $top, $size[1], $height, $color);
        }

        if ($size[3] > 0) {
            $this->drawDashedLine($left, $top, $size[3], $height, $color);
        }
    }

    public function drawDashedLine($x1, $y1, $width, $height, $color)
    {
        if ($width > $height) {
            $this->drawHorizontalDashedLine($x1, $y1, $width, $height, $color);
        } else {
            $this->drawVerticalDashedLine($x1, $y1, $width, $height, $color);
        }
    }

    public function drawHorizontalDashedLine($x1, $y1, $width, $height, $color)
    {
        $x2 = $x1 + $width;
        $barWidth = ($height*2);
        for ($i = $x1; $i <= $x2; $i = $i+($height*3)) {
            if ($i + $barWidth > $x2) {
                $barWidth = $x2 - $i;
            }
            $this->pdf->Rect($i, $y1, $barWidth, $height, 'DF', ['width' => 0], $color);
        }
    }

    public function drawVerticalDashedLine($x1, $y1, $width, $height, $color)
    {
        $barHeight = $width*2;
        $y2 = $y1 + $height;
        for ($i = $y1; $i <= $y2; $i = $i + ($width*3)) {
            if ($i + $barHeight > $y2) {
                $barHeight = $y2 - $i;
            }

            $this->pdf->Rect($x1, $i, $width, $barHeight, 'DF', ['width' => 0], $color);
        }
    }

    /**
     * Draw solid borders
     *
     * @param $top
     * @param $left
     * @param $width
     * @param $height
     * @param $size
     * @param $color
     */
    public function drawBordersSolid($top, $left, $width, $height, $size, $color)
    {
        $top = $this->toPoint($top);
        $left = $this->toPoint($left);
        $width = $this->toPoint($width);
        $height = $this->toPoint($height);

        if (!is_array($size)) {
            $size = $this->toPoint($size);
            $size = [$size, $size, $size, $size];
        } else {
            $size = [
                $this->toPoint($size[0]),
                $this->toPoint($size[1]),
                $this->toPoint($size[2]),
                $this->toPoint($size[3]),
            ];
        }

        if ($size[0] > 0) {
            $this->pdf->Rect($left, $top, $width, $size[0], 'DF', ['width' => 0], $this->getPdfColor($color));
        }

        if ($size[2] > 0) {
            $bPos = $top + $height - $size[2];
            $this->pdf->Rect($left, $bPos, $width, $size[2], 'DF', ['width' => 0], $this->getPdfColor($color));
        }

        if ($size[1] > 0) {
            $bPos = $top + $height - $size[1];
            $rPos = $left + $width - $size[1];
            $this->pdf->Rect($rPos, $top, $size[1], $height, 'DF', ['width' => 0], $this->getPdfColor($color));
        }

        if ($size[3] > 0) {
            $this->pdf->Rect($left, $top, $size[3], $height, 'DF', ['width' => 0], $this->getPdfColor($color));
        }
    }

    /**
     * Calculate image position
     *
     * @param $top
     * @param $left
     * @param $width
     * @param $height
     * @return array
     */
    public function getImagePosition($top, $left, $width, $height)
    {
        $defaultTop = $this->getTopPosition($top, $left);
        $width = $this->toPoint($width);
        $height = $this->toPoint($height);
        $y1 = $defaultTop['y'] - $height;
        $y2 = $defaultTop['y'];

        $x1 = $defaultTop['x'];
        $x2 = $defaultTop['x'] + $width;

        return ['x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2];
    }

    /**
     * Remove 'px' from string or array
     *
     * @param $data
     * @param $fields
     * @return array|string
     */
    public function removePx($data, $fields = [])
    {
        if (is_array($data)) {
            if (empty($fields)) {
                foreach ($data as $key => $value) {
                    $data[$key] = (int)str_replace('px', '', $value);
                }
            } else {
                foreach ($fields as $key) {
                    $data[$key] = (int)str_replace('px', '', $data[$key]);
                }
            }
        } else {
            $data = (int)str_replace('px', '', $data);
        }
        return $data;
    }

    /**
     * Returns the total width in points of the string using the specified font and
     * size.
     *
     * @param  string $string
     * @param  string $fontName
     * @param  float $fontSize Font size in points
     * @return float
     */
    public function widthForStringUsingFontSize($string, $fontName, $fontSize)
    {
        $string = $this->cleanString($string);
        $stringWidth = $this->pdf->GetStringWidth($string, $fontName, '', $fontSize);
        return $stringWidth;
    }

    /**
     * Returns max character amount
     *
     * @return mixed
     */
    public function findMaxLenghtOfString($string, $maxWidth, $font, $fontSize)
    {
        $charNumber = strlen($string);
        if ($this->widthForStringUsingFontSize($string, $font, $fontSize) < $maxWidth) {
            return $charNumber;
        }

        $max = $charNumber;
        for ($i = 0; $i < $charNumber + 10; $i+=10) {
            $str = substr($string, 0, $i);
            $strWidth = $this->widthForStringUsingFontSize($str, $font, $fontSize);
            if ($strWidth < $maxWidth) {
                continue;
            }
            $max = $i - 10;
            break;
        }

        for ($i = $max; $i < $charNumber + 5; $i+=5) {
            $str = substr($string, 0, $i);
            $strWidth = $this->widthForStringUsingFontSize($str, $font, $fontSize);
            if ($strWidth < $maxWidth) {
                continue;
            }
            $max = $i - 5;
            break;
        }
        for ($i = $max; $i <= $charNumber; $i++) {
            $str = substr($string, 0, $i);
            $strWidth = $this->widthForStringUsingFontSize($str, $font, $fontSize);
            if ($strWidth < $maxWidth) {
                continue;
            }
            $max = $i - 1;
            break;
        }
        return $max;
    }

    /**
     * Split text to strings with fixed width
     *
     * @param $string
     * @param $maxWidth
     * @param $fontCode
     * @param $fontSize
     * @return array|float
     */
    public function splitStringToLines($string, $maxWidth, $fontCode, $fontSize)
    {
        if (empty($string)) {
            return [];
        }

        $string = $this->decorator->removeDecorators($string);
        $string = str_replace(['<br/>', '<br>', '</br>', "\n"], '{br}', $string);
        if (substr_count($string, '{br}') > 0) {
            $lines = [];
            $substrings = explode('{br}', $string);
            foreach ($substrings as $sub) {
                $subLines = $this->splitStringToLines($sub, $maxWidth, $fontCode, $fontSize);
                foreach ($subLines as $sbLine) {
                    array_push($lines, $sbLine);
                }
            }
            return $lines;
        }

        $font = $this->getPdfFont($fontCode);
        $textWidth = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        $lines = ceil($textWidth / $maxWidth);
        $maxCharInLine = $this->findMaxLenghtOfString($string, $maxWidth, $font, $fontSize);
        $string = explode(' ', $string);
        $wordList = [];

        foreach ($string as $word) {
            $wordLength = strlen($word);
            if ($wordLength > $maxCharInLine) {
                $linesCount = ceil($wordLength / $maxCharInLine);
                for ($j = 0; $j < $linesCount; $j++) {
                    $start = $j*$maxCharInLine;
                    $wordList[] = substr($word, $start, $maxCharInLine);
                }
            } else {
                $wordList[] = $word;
            }
        }

        $lines = [0 => ''];
        $i = 0;
        foreach ($wordList as $word) {
            if (strlen($lines[$i].$word) > $maxCharInLine) {
                $i++;
                $lines[$i] = '';
            }
            $lines[$i] = $lines[$i].$word.' ';
        }

        return $lines;
    }

    /**
     * Draw text lines
     *
     * @param $lines
     * @param $x1
     * @param $y1
     * @param $lineHeight
     * @param $fontSize
     * @return mixed
     */
    public function drawTextLines(
        $lines,
        $x1,
        $y1,
        $lineHeight,
        $fontSize,
        $align = null,
        $decorators = [],
        $direction = Direction::LTR,
        $boxWidth = 1000
    ) {
        if (!empty($decorators)) {
            return $this->drawTextLinesWithDecorators($lines, $x1, $y1, $lineHeight, $fontSize, $align, $decorators, $direction, $boxWidth);
        }

        $font = $this->pdf->getFontFamily();
        if ($direction == Direction::RTL) {
            $align = $this->flipAligment($align);
        }

        foreach ($lines as $line) {

            $line = preg_replace('/(\x{200e}|\x{200f})/u', '', $line);
            if ($direction == Direction::RTL) {
                $line = $this->convertText2Rtl($line);
            }

            if ($align == self::ALIGN_RIGHT && $boxWidth != null) {
                $x2 = $x1 + $boxWidth - $this->widthForStringUsingFontSize($line, $font, $fontSize);
            } else {
                $x2 = $x1;
            }

            try {
                $this->pdf->setCellMargins(0, 0, 0, 0);
                $this->pdf->setCellPaddings(0, 0, 0, 0);
                $this->pdf->setCellHeightRatio($fontSize/$lineHeight);
                $this->pdf->SetXY($x2, $y1);

                if ($this->moduleHelper->isPriceFixEnabled($this->getSource()->getStoreId())) {
                    if (strlen($line) > 15
                        || strpos($line, $this->currencySymbol) === false
                        || strpos($line, $this->currencySymbol) < 1) {
                        $this->pdf->Write($lineHeight, $line, '', false, 'L', false);
                    } else {
                        $this->drawPrice($lineHeight, $line, $font, $fontSize);
                    }
                } else {
                    $this->pdf->Write($lineHeight, $line, '', false, 'L', false);
                }
            } catch (\Exception $e) {
                $y1 = $y1 + $lineHeight;
                continue;
            }
            $y1 = $y1 + $lineHeight;
        }

        return $y1 - $lineHeight;
    }

    public function drawPrice($lineHeight, $line, $font, $fontSize)
    {
        $currencySymbol = $this->currencySymbol;
        $posOfSymb = strpos($line, $currencySymbol);
        $rawValue = str_replace($currencySymbol, '', $line);
        $this->pdf->Write($lineHeight, $currencySymbol, '', false, 'L', false);
        $this->pdf->SetXY(
            $this->pdf->getX() + $this->widthForStringUsingFontSize('0', $font, $fontSize),
            $this->pdf->getY()
        );
        $this->pdf->Write($lineHeight, $rawValue, '', false, 'L', false);
    }

    public function convertText2Rtl($part)
    {
        return $part;
    }

    public function drawTextLinesWithDecorators(
        $lines,
        $x1,
        $y1,
        $lineHeight,
        $fontSize,
        $align = self::ALIGN_LEFT,
        $decorators = [],
        $direction = Direction::LTR,
        $boxWidth = null
    ) {
        $font = $this->pdf->getFontFamily();
        $offset = 0;
        $mergedLines = implode('', $lines);
        $lineOffset = 0;

        foreach ($lines as $line) {
            $xOffset = $x1;
            $lineLenght = mb_strlen($line);
            try {
                $splitedString = [];
                foreach ($decorators as $decorator) {
                    if ($lineOffset + $lineLenght < $decorator['offset'] || $lineOffset > $decorator['offset']) {
                        continue;
                    }

                    if ($lineOffset < $decorator['offset']) {
                        $text = mb_substr($mergedLines, $offset, $decorator['offset'] - $offset);
                        $this->drawTextLines(
                            [$text],
                            $xOffset,
                            $y1,
                            $lineHeight,
                            $fontSize,
                            $align,
                            [],
                            $direction,
                            $boxWidth
                        );
                        if ($direction == Direction::RTL) {
                            $xOffset -= $this->widthForStringUsingFontSize($text, $font, $fontSize);
                            $xOffset -= 3;
                        } else {
                            $xOffset += $this->widthForStringUsingFontSize($text, $font, $fontSize);
                            $xOffset += 3;
                        }
                    }

                    $decoratedText = mb_substr($mergedLines, $decorator['offset'], $decorator['pos']);
                    $this->decorator->applyDecoration($this->pdf, $this, $decorator);
                    $this->drawTextLines(
                        [$decoratedText],
                        $xOffset,
                        $y1,
                        $lineHeight,
                        $fontSize,
                        $align,
                        [],
                        $direction,
                        $boxWidth
                    );
                    $this->decorator->resetDecoration($this->pdf, $this);

                    if ($direction == Direction::RTL) {
                        $xOffset -= $this->widthForStringUsingFontSize($decoratedText, $font, $fontSize);
                    } else {
                        $xOffset += $this->widthForStringUsingFontSize($decoratedText, $font, $fontSize);
                    }

                    $offset = $decorator['offset'] + $decorator['pos'];
                }
                $lineOffset += $lineLenght;
            } catch (\Exception $e) {
                $y1 = $y1 + $lineHeight;
                continue;
            }
            $y1 = $y1 + $lineHeight;
        }

        return $y1 - $lineHeight;
    }

    /**
     * Returns element bottom line Y
     *
     * @return bool
     */
    public function getBottomY()
    {
        return $this->bottomY;
    }

    /**
     * Returns element information for next element
     *
     * @return array
     */
    public function getInfo($elementData)
    {
        $attributes = $this->getAttributes();
        $info =  [
            'design_top' => $this->removePx($elementData['attributes']['top']),
            'design_left' => $this->removePx($elementData['attributes']['left']),
            'design_width' => $this->removePx($elementData['attributes']['width']),
            'design_height' => $this->removePx($elementData['attributes']['height']),
            'pdf_top' => $this->removePx($attributes['top']),
            'pdf_left' => $this->removePx($attributes['left']),
            'pdf_width' => $this->removePx($attributes['width']),
            'pdf_height' => $this->removePx($attributes['height']),
        ];

        if (isset($attributes['depends_on']) && !empty($attributes['depends_on'])) {
            $info['depends'] = $attributes['depends_on'];
        }
        return $info;
    }

    /**
     * Returns name
     *
     * @return mixed|string
     */
    public function getName()
    {
        $attributes = $this->getAttributes();
        if (isset($attributes['name']) && !empty($attributes['name'])) {
            return $attributes['name'];
        }

        return 'element_'.$this->elementData['entity_id'];
    }

    /**
     * Returns Element ID
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->elementData['entity_id'];
    }

    /**
     * Returns element unique id
     *
     * @return mixed
     */
    public function getUid()
    {
        return $this->elementData['uid'];
    }

    /**
     * Replace variables to data
     *
     * @param $string
     * @return mixed
     */
    public function processFilters($string)
    {
        $source = $this->getSource();
        $filter = $this->typeManager
            ->getAdapter()
            ->getFilter();

        return $filter->processFilter($this->getSource(), $string);
    }

    /**
     * Returns Element Config
     *
     * @return array
     */
    public function getConfig()
    {
        if (empty($this->configClassName)) {
            return [];
        }

        return $this->objectManager->get($this->configClassName)->getConfig();
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getOrder()
    {
        if ($this->order == null) {
            $source = $this->getSource();
            if ($source instanceof \Magento\Sales\Model\Order) {
                $this->order = $source;
            } else {
                $this->order = $source->getOrder();
            }
        }

        return $this->order;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getFieldListToRemovePx()
    {
        return ['top', 'left', 'width', 'height'];
    }

    public function getOrderItem($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            return $item;
        }

        if ($item instanceof \Magento\Quote\Model\Quote\Item) {
            return $item;
        }

        return $item->getOrderItem();
    }

    public function flipAligment($align = null)
    {
        if ($align == self::ALIGN_RIGHT) {
            return self::ALIGN_LEFT;
        } elseif ($align == self::ALIGN_LEFT || $align === null) {
            return self::ALIGN_RIGHT;
        }

        return $align;
    }

    /**
     * Returns pdf color by color code
     *
     * @param $color
     * @return []
     */
    public function getPdfColor($color)
    {
        if (substr_count($color, 'rgba') == 1) {
            list($r, $g, $b, $a) = sscanf($color, "rgba(%d, %d, %d, %f)");
            return [$r, $g, $b, $a];
        } elseif (substr_count($color, 'rgb') == 1) {
            list($r, $g, $b) = sscanf($color, "rgb(%d, %d, %d)");
            return [$r, $g, $b];
        }

        return [0, 0, 0, 0];
    }

    /**
     * @param $type (string) Type of object affected by this color: ('draw', 'fill', 'text').
     * @param $color
     */
    public function setColor($type, $color)
    {
        $color = $this->getPdfColor($color);
        if (!isset($color[3])) {
            $color[3] = -1;
        }
        $this->fillColor = $color;
        $this->pdf->setColor($type, $color[0],  $color[1],  $color[2],  $color[3]);
    }

    public function setTextColor($color)
    {
        return $this->setColor('text', $color);
    }

    public function getFillColor()
    {
        return $this->fillColor;
    }

    public function resolveAlign($align)
    {
        switch ($align) {
            case 'right':
                return 'R';
            case 'center':
                return 'C';
            case 'justify':
                return 'J';
        }

        return 'L';
    }
}
