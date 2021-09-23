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

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\Renderer;

/**
 * Bundle item pdf renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Subtotal extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\DefaultRenderer
{
    public function getRowValue()
    {
        $itemPrice = $this->getItemRenderer()->getFormatedSubtotal();
        return $itemPrice;
    }

    public function getWeeeTax()
    {
        if (!$this->showWeee()) {
            return 0;
        }

        return $this->getItem()->getData('weee_tax_applied_row_amount');
    }

    /**
     * Returns formated subtotal value
     *
     * @return string
     */
    public function getPdfData()
    {
        $attributes = $this->getAttributes();
        $columnName = $this->getColumn();

        $fontSize = $this->moduleHelper->removePx($attributes['table_row_product_line_1_size']);
        $lineHeight = $this->moduleHelper->removePx($attributes['table_row_product_line_1_line_height']);
        $fontCode = $attributes['table_row_product_line_1_font'];
        $color = $attributes['table_row_product_line_1_color'];

        $fontSize2 = $this->moduleHelper->removePx($attributes['table_row_product_line_2_size']);
        $fontCode2 = $attributes['table_row_product_line_2_font'];
        $color2 = $attributes['table_row_product_line_2_color'];
        $lineHeight2 = $this->moduleHelper->removePx($attributes['table_row_product_line_2_line_height']);

        $padding = $this->getRowPadding();
        $columnWidth = $this->moduleHelper->removePx($attributes['table_header_'.$columnName.'_column_width'])
            - $padding[3] - $padding[1];

        $columnWidth = $this->moduleHelper->toPoint($columnWidth);

        $subtotalTxt = $this->element->splitStringToLines(
            $this->getRowValue(),
            $columnWidth,
            $fontCode,
            $this->moduleHelper->toPoint($fontSize)
        );

        $weeTax = $this->getWeeeTax();
        $fptText = [];
        if ($weeTax > 0) {
            $fptPrice = $this->moduleHelper->formatPrice(
                $this->moduleHelper->getCurrencyCode($this->getItem()->getStoreId()),
                $weeTax
            );

            $fptText = $this->element->splitStringToLines(
                __('[FPT: %1]', $fptPrice),
                $columnWidth,
                $fontCode2,
                $this->moduleHelper->toPoint($fontSize2)
            );
        }

        $rowHeight = $padding[0] + count($subtotalTxt) * $lineHeight
            + count($fptText) * $lineHeight2 + $padding[2];

        $data =  [
            'height' => $rowHeight,
            'padding' => $padding,
            'text' => [
                'subtotal' => [
                    'text' => $subtotalTxt,
                    'font' => $fontCode,
                    'font_size' => $fontSize,
                    'line_height' => $lineHeight,
                    'color' => $color
                ],
            ]
        ];

        $weeTax = $this->getWeeeTax();
        if (!empty($fptText)) {
            $data['text']['fpt'] = [
                'text' => $fptText,
                'font' => $fontCode2,
                'font_size' => $fontSize2,
                'line_height' => $lineHeight2,
                'color' => $color2
            ];
        }

        return $data;
    }
}
