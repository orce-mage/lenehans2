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

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Bundle item pdf renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class DefaultRenderer extends \Magento\Framework\DataObject
{
    /**
     * @var \Magetrend\PdfTemplates\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magetrend\PdfTemplates\Model\Pdf\Element
     */
    public $element;

    /**
     * @var \Magetrend\PdfTemplates\Model\Pdf\Decorator
     */
    public $decorator;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * DefaultRenderer constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magetrend\PdfTemplates\Model\Pdf\Element $element
     * @param \Magetrend\PdfTemplates\Model\Pdf\Decorator $decorator
     * @param array $data
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\Pdf\Element $element,
        \Magetrend\PdfTemplates\Model\Pdf\Decorator $decorator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->element = $element;
        $this->decorator = $decorator;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($data);
    }

    /**
     * Returns formated subtotal value
     *
     * @return string
     */
    public function getPdfData()
    {
        $columnName = $this->getColumn();
        $attributes = $this->getAttributes();
        $fontSize = $this->moduleHelper->removePx($attributes['table_row_font_size']);
        $lineHeight = $this->moduleHelper->removePx($attributes['table_row_line_height']);
        $fontCode = $attributes['table_row_font'];
        $color = $attributes['table_row_font_color'];

        $padding = $this->getRowPadding();
        $columnWidth = $this->moduleHelper->toPoint(
            $this->moduleHelper->removePx(
                $attributes['table_header_'.$columnName.'_column_width']
            ) - $padding[3] - $padding[1]
        );

        $value = $this->element->splitStringToLines(
            $this->getRowValue(),
            $columnWidth,
            $fontCode,
            $this->moduleHelper->toPoint($fontSize)
        );

        $data = [
            'padding' => $padding,
            'text' => [
                $columnName => [
                    'text' => $value,
                    'font' => $fontCode,
                    'font_size' => $fontSize,
                    'line_height' => $lineHeight,
                    'color' => $color
                ],
            ]
        ];
        $data['height'] = $this->getColumnHeight($data);
        return $data;
    }

    public function getColumnHeight($data)
    {
        if (empty($data['text'])) {
            return 0;
        }

        $padding = $this->getRowPadding();
        $rowHeight = $padding[0] + $padding[2];

        foreach ($data['text'] as $text) {
            $rowHeight = $rowHeight + (count($text['text'])*$text['line_height']);
        }
        return $rowHeight;
    }

    /**
     * Returns row cell padding options
     *
     * @return array
     */
    public function getRowPadding()
    {
        $attributes = $this->getAttributes();
        return [
            $this->moduleHelper->removePx($attributes['table_row_cell_padding_top']),
            $this->moduleHelper->removePx($attributes['table_row_cell_padding_right']),
            $this->moduleHelper->removePx($attributes['table_row_cell_padding_bottom']),
            $this->moduleHelper->removePx($attributes['table_row_cell_padding_left'])
        ];
    }

    public function getRowValue()
    {
        return $this->getItem()->getData($this->getColumn());
    }

    /**
     * Returns item options
     *
     * @param $item
     * @return string
     */
    public function getItemOptions()
    {
        return $this->getItemRenderer()->getFormatedItemOptions();
    }

    public function getItemImage()
    {
        return $this->getItemRenderer()->getItemImage($this->getAttributes());
    }

    public function showWeee()
    {
        $isActive = $showWeee = $this->scopeConfig->getValue(
            \Magento\Weee\Model\Config::XML_PATH_FPT_ENABLED,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        return $isActive;
    }
}
