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

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Track\Column;

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
     * DefaultRenderer constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magetrend\PdfTemplates\Model\Pdf\Element $element
     * @param array $data
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\Pdf\Element $element,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->element = $element;
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

        $rowHeight = $padding[0] +  $lineHeight + $padding[2];
        $value = $this->element->splitStringToLines(
            $this->getRowValue(),
            $columnWidth,
            $fontCode,
            $this->moduleHelper->toPoint($fontSize)
        );

        return [
            'height' => $rowHeight,
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
    public function getItemOptions($item)
    {
        $result = [];
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        if (empty($result)) {
            return '';
        }
        $optionsString = '';
        foreach ($result as $option) {
            $optionsString.= $option['label'].': '.$option['value'].', ';
        }

        return rtrim($optionsString, ', ');
    }
}
