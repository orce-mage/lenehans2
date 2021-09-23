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

use Magetrend\PdfTemplates\Model\Config\Source\Direction;

/**
 * Draw pdf element totals
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Total extends \Magetrend\PdfTemplates\Model\Pdf\Element
{
    public $configClassName = 'Magetrend\PdfTemplates\Model\Pdf\Config\Total';

    public $columnConfig = [
        'label' => [
            'width' => 'table_column_label_width',
        ],

        'value' => [
            'width' => 'table_column_value_width',
        ],
    ];

    private $lines = [];

    public $startFromItem = 0;

    public $lastPageFooterHeight = null;

    public $lastItemY = 0;

    public function draw($pdfPage, $elemetData, $source, $template, $elements, $currentPage)
    {
        parent::draw($pdfPage, $elemetData, $source, $template, $elements, $currentPage);

        $attributes = $this->getAttributes();
        $topY = $this->removePx($attributes['top']);
        if (empty($attributes)) {
            return $this;
        }

        if ($this->getSource() instanceof \Magento\Quote\Model\Quote) {
            $totalData = $this->getQuoteTotalData();
        } else {
            $totalData = $this->getOrderTotalData();
        }

        $i = 1;
        $rowCount = count($totalData);
        foreach ($totalData as $total) {
            $isGrandTotal = $total['is_grand_total'];
            $rowHeight = $this->getRowHeight($total);
            $this->drawRowBackground($topY, $rowHeight, $isGrandTotal);
            $this->drawInsideBorders($topY, $rowHeight, $i === 1, $i==$rowCount);
            $this->drawLabel($total, $topY, $isGrandTotal);
            $this->drawValue($total, $topY, $isGrandTotal);
            $topY += $rowHeight;
            $this->lastItemY = $topY;
            $i++;
        }
        $this->drawBox();

        return $this;
    }

    public function getOrderTotalData()
    {
        $order = $this->getOrder();
        $source = $this->getSource();
        $totals = $this->getTotalsList();
        $totalsConfig = $this->pdfConfig->getTotals();
        $grandTotalModel = $totalsConfig['grand_total']['model'];

        $collectedTotals = [];
        foreach ($totals as $total) {
            $total->setOrder($order)->setSource($source);
            if ($total->canDisplay()) {
                foreach ($total->getTotalsForDisplay() as $i => $totalData) {
                    if (!$this->canDisplay($totalData)) {
                        continue;
                    }

                    if ($total instanceof \Magento\Tax\Model\Sales\Pdf\Tax) {
                        if ($this->moduleHelper->isTaxSummaryEnabled($order->getStoreId())
                            && !isset($totalData['tax_amount'])) {
                            continue;
                        }
                    }

                    $totalData['source_field'] = $total['source_field'].'_'.$i;
                    $isGrandTotal = $total instanceof $grandTotalModel;
                    if (isset($totalData['tax_amount']) && isset($totalData['title'])
                        && isset($totalData['percent'])) {
                        $precision = 2;
                        if (number_format($totalData['percent'], 2) == number_format($totalData['percent'], 0)) {
                            $precision = 0;
                        }
                        $totalData['label'] = (string)__(
                            $totalData['title'].' (%1%)',
                            number_format($totalData['percent'], $precision)
                        );
                    } else {
                        $totalData['label'] = (string)__($totalData['label']);
                    }
                    $totalData['is_grand_total'] = $isGrandTotal;
                    $collectedTotals[] = $totalData;
                }
            }
        }

        $collectedTotals = $this->applyCustomSortOrder($collectedTotals);
        usort($collectedTotals, [$this, 'sortTotalsList']);
        return $collectedTotals;
    }

    public function getQuoteTotalData()
    {
        $quote = $this->getSource();
        $quote->collectTotals();
        $totals = $quote->getTotals();
        $totalData = [];

        if (empty($totals)) {
            return [];
        }

        $currencyCode = $this->moduleHelper->getCurrencyCode($quote->getStoreId());

        foreach ($totals as $total) {
            $total->setQuote($quote)->setSource($quote);
            if ($total->getValue() == 0) {
                continue;
            }

            $totalData[] = [
                'amount' => $this->moduleHelper->formatPrice($currencyCode, $total->getValue()),
                'label' => $total->getTitle(),
                'code' => $total->getCode(),
                'is_grand_total' => ($total->getCode() == 'grand_total'?1:0),
                'source_field' => $total->getSourceField(),
            ];
        }

        $totalData = $this->applyCustomSortOrder($totalData);
        usort($totalData, [$this, 'sortTotalsList']);

        return $totalData;
    }

    public function canDisplay($totalData)
    {
        return true;
    }

    public function getRowHeight($totalData)
    {
        $attributes = $this->getAttributes();
        $fontSize = $this->removePx($attributes['table_row_font_size']);
        $lineHeight = $this->removePx($attributes['table_row_line_height']);
        $fontCode = $attributes['table_row_font'];
        $color = $attributes['table_row_font_color'];
        $rowHeight = $this->removePx($attributes['table_row_height']);

        $padding = $this->getRowPadding('label');
        $columnWidth = $this->toPoint(
            $this->removePx($attributes['table_column_label_width']) - $padding[3] - $padding[1]
        );

        $label = $this->splitStringToLines(
            $totalData['label'],
            $columnWidth,
            $fontCode,
            $this->toPoint($fontSize)
        );

        $rowHeight = $rowHeight + ((count($label)-1) * $lineHeight);
        return $rowHeight;
    }

    public function drawInsideBorders($topY, $rowHeight, $isFirst, $isLast)
    {
        $attributes = $this->getAttributes();
        $i = 1;
        $columnCount = count($this->columnConfig);
        foreach ($this->columnConfig as $key => $column) {
            if ($isFirst) {
                $topBorderSize = 0;
            } else {
                $topBorderSize = $attributes['table_border_inside_top_size'];
            }

            if ($isLast) {
                $bottomSize = 0;
            } else {
                $bottomSize = $attributes['table_border_inside_bottom_size'];
            }

            if ($i == 1) {
                $leftBorderSize = 0;
            } else {
                $leftBorderSize = $attributes['table_border_inside_left_size'];
            }

            if ($i == $columnCount) {
                $rightBorderSize = 0;
            } else {
                $rightBorderSize = $attributes['table_border_inside_right_size'];
            }

            $this->drawBordersSolid(
                $topY,
                $this->getColumnLeft($key),
                $attributes[$column['width']],
                $rowHeight,
                [$topBorderSize, $rightBorderSize, $bottomSize, $leftBorderSize],
                $attributes['table_border_inside_color']
            );
            $i++;
        }
    }

    public function getColumnLeft($columnName)
    {
        $attributes = $this->getAttributes();
        if ($columnName == 'label') {
            if (isset($attributes['direction']) && $attributes['direction'] == Direction::RTL) {
                return $this->removePx($attributes['left'])
                    + $this->removePx($attributes['table_column_value_width']);
            } else {
                return $this->removePx($attributes['left']);
            }
        }
        if ($columnName == 'value') {
            if (isset($attributes['direction']) && $attributes['direction'] == Direction::RTL) {
                return $this->removePx($attributes['left']);
            } else {
                return $this->removePx($attributes['left'])
                    + $this->removePx($attributes['table_column_label_width']);
            }
        }
    }

    public function drawBox()
    {
        $attributes = $this->getAttributes();
        $size = [
            isset($attributes['table_border_outside_top_size'])?$attributes['table_border_outside_top_size']:0,
            isset($attributes['table_border_outside_right_size'])?$attributes['table_border_outside_right_size']:0,
            isset($attributes['table_border_outside_bottom_size'])?$attributes['table_border_outside_bottom_size']:0,
            isset($attributes['table_border_outside_left_size'])?$attributes['table_border_outside_left_size']:0,
        ];

        $color = isset($attributes['table_border_outside_color'])?$attributes['table_border_outside_color']:'#ffffff';
        $type = isset($attributes['table_border_outside_style'])?$attributes['table_border_outside_style']:'solid';

        $y1 = $this->removePx($attributes['top']);
        $x1 = $this->removePx($attributes['left']);
        $height = $this->lastItemY - $y1;
        $width = $this->removePx($attributes['table_width']);
        $this->drawBorders($y1, $x1, $width, $height, $size, $color, $type);
    }

    public function drawLabel($totalData, $y1, $isGrandTotal)
    {
        $attributes = $this->getAttributes();
        $direction = isset($attributes['direction'])?$attributes['direction']:Direction::LTR;

        if ($isGrandTotal) {
            $fontSize = $this->removePx($attributes['table_row_grand_total_font_size']);
            $lineHeight = $this->removePx($attributes['table_row_grand_total_line_height']);
            $fontCode = $attributes['table_row_grand_total_font'];
            $color = $attributes['table_row_grand_total_font_color'];
        } else {
            $fontSize = $this->removePx($attributes['table_row_font_size']);
            $lineHeight = $this->removePx($attributes['table_row_line_height']);
            $fontCode = $attributes['table_row_font'];
            $color = $attributes['table_row_font_color'];
        }

        $padding = $this->getRowPadding('label');
        $x1 = $this->getColumnLeft('label');
        $columnWidth = $this->getColumnWidth('label');

        if ($direction == Direction::RTL) {
            $columnWidth = $columnWidth - $padding[1];
        } else {
            $x1 += $padding[3];
        }

        $columnWidth = $this->toPoint($columnWidth);
        $y1 += $padding[0] + $this->removePx($attributes['table_border_inside_top_size']);
        $this->pdfPage->setFont($this->getPdfFont($fontCode), $this->toPoint($fontSize))
            ->setFillColor($this->getPdfColor($color));

        $label = $this->splitStringToLines(
            $totalData['label'],
            $columnWidth,
            $fontCode,
            $this->toPoint($fontSize)
        );

        $this->drawTextLines(
            $label,
            $this->toPoint($x1),
            $this->toPoint($y1),
            $this->toPoint($lineHeight),
            $this->toPoint($fontSize),
            null,
            [],
            $direction,
            $columnWidth
        );
    }

    public function drawValue($totalData, $y1, $isGrandTotal)
    {
        $attributes = $this->getAttributes();
        $align = isset($attributes['value_align'])?$attributes['value_align']:'left';
        $direction = isset($attributes['direction'])?$attributes['direction']:Direction::LTR;

        if ($isGrandTotal) {
            $fontSize = $this->removePx($attributes['table_row_grand_total_font_size']);
            $lineHeight = $this->removePx($attributes['table_row_grand_total_line_height']);
            $fontCode = $attributes['table_row_grand_total_font'];
            $color = $attributes['table_row_grand_total_font_color'];
        } else {
            $fontSize = $this->removePx($attributes['table_row_font_size']);
            $lineHeight = $this->removePx($attributes['table_row_line_height']);
            $fontCode = $attributes['table_row_font'];
            $color = $attributes['table_row_font_color'];
        }

        $padding = $this->getRowPadding('value');
        $x1 = $this->getColumnLeft('value');
        $columnWidth = $this->getColumnWidth('value');

        if ($direction == Direction::RTL) {
            $columnWidth = $columnWidth - $padding[3];
        } else {
            if ($align == 'right') {
                $columnWidth = $columnWidth - $padding[1];
            } else {
                $x1 += $padding[3];
                $columnWidth = $columnWidth - $padding[1];
            }
        }

        $y1 += $padding[0] + $this->removePx($attributes['table_border_inside_top_size']);

        $columnWidth = $this->toPoint($columnWidth);

        $this->pdfPage->setFont($this->getPdfFont($fontCode), $this->toPoint($fontSize))
            ->setFillColor($this->getPdfColor($color));

        $value = $this->splitStringToLines(
            str_replace('&nbsp;', ' ', $totalData['amount']),
            $columnWidth,
            $fontCode,
            $this->toPoint($fontSize)
        );

        $this->drawTextLines(
            $value,
            $this->toPoint($x1),
            $this->toPoint($y1),
            $this->toPoint($lineHeight),
            $this->toPoint($fontSize),
            $align,
            [],
            $direction,
            $columnWidth
        );
    }

    public function drawRowBackground($topY, $height, $isGrandTotal)
    {
        $this->resetLines();
        $attributes = $this->getAttributes();
        $tableWidth = $attributes['table_width'];
        if ($isGrandTotal) {
            $rowColor = $attributes['table_row_2_background'];
        } else {
            $rowColor = $attributes['table_row_1_background'];
        }

        $top = $this->getTopPosition(
            $topY,
            $attributes['left']
        );

        $bottom = $this->getBottomPosition(
            $topY,
            $attributes['left'],
            $tableWidth,
            $height
        );

        $this->pdfPage->setFillColor($this->getPdfColor($rowColor));
        $this->pdfPage->drawRectangle(
            $top['x'],
            $top['y'],
            $bottom['x'],
            $bottom['y'],
            \Zend_Pdf_Page::SHAPE_DRAW_FILL
        );
    }

    public function getColumnTextLeft($columnName)
    {
        $attributes = $this->getAttributes();
        $left = $this->getColumnLeft($columnName);
        if (isset($attributes['table_header_padding_left'])) {
            $left+= $this->toPoint($attributes['table_header_padding_left']);
        }

        return $left;
    }

    public function getColumnWidth($columnName)
    {
        $attributes = $this->getAttributes();
        return $this->removePx($attributes['table_column_'.$columnName.'_width']);
    }

    public function getInvoiceItems()
    {
        return $this->invoiceItems;
    }

    /**
     * Returns row cell padding options
     *
     * @return array
     */
    public function getRowPadding($columnName = 'label')
    {
        $attributes = $this->getAttributes();
        return [
            $this->removePx($attributes['table_column_'.$columnName.'_padding_top']),
            $this->removePx($attributes['table_column_'.$columnName.'_padding_right']),
            $this->removePx($attributes['table_column_'.$columnName.'_padding_bottom']),
            $this->removePx($attributes['table_column_'.$columnName.'_padding_left'])
        ];
    }

    public function getTotalsList()
    {
        $totals = $this->pdfConfig->getTotals();
        $totalModels = [];
        foreach ($totals as $totalInfo) {
            $class = empty($totalInfo['model']) ? null : $totalInfo['model'];
            $totalModel = $this->pdfTotalFactory->create($class);
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }

        return $totalModels;
    }

    /**
     * Returns last item bottom Y line
     *
     * @return int
     */
    public function getLastItemY()
    {
        return $this->lastItemY;
    }

    /**
     * Sort totals list
     *
     * @param  array $a
     * @param  array $b
     * @return int
     */
    public function sortTotalsList($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }

        return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
    }

    /**
     * Returns information about element position and sizes
     *
     * @param $elementData
     * @return array
     */
    public function getInfo($elementData)
    {
        $attributes = $this->getAttributes();
        $info = [
            'design_top' => $this->removePx($elementData['attributes']['top']),
            'design_left' => $this->removePx($elementData['attributes']['left']),
            'design_width' => $this->removePx($elementData['attributes']['table_width']),
            'design_height' => $this->removePx($elementData['attributes']['table_height']),
            'pdf_top' => $this->removePx($attributes['top']),
            'pdf_left' => $this->removePx($attributes['left']),
            'pdf_width' => $this->removePx($attributes['table_width']),
            'pdf_height' => ($this->getLastItemY() - $attributes['top']),
            'pdf_bottom_line' => $this->getLastItemY()
        ];

        if (isset($attributes['depends_on']) && !empty($attributes['depends_on'])) {
            $info['depends'] = $attributes['depends_on'];
        }

        return $info;
    }

    public function applyCustomSortOrder($totals)
    {
        $customSortOrder = $this->moduleHelper->getTotalsSorting();
        if (empty($totals) || empty($customSortOrder)) {
            return $totals;
        }

        $i = 1;
        foreach ($totals as $key => $total) {
            if (isset($totals[$key]['sort_order'])) {
                continue;
            }
            $totals[$key]['sort_order'] = $i++;
        }

        foreach ($totals as $key => $total) {
            if (!isset($total['source_field'])) {
                continue;
            }

            $sourceField = $total['source_field'];
            if (!isset($customSortOrder[$sourceField])) {
                continue;
            }

            $totals[$key]['sort_order'] = $customSortOrder[$sourceField];
        }

        return $totals;
    }
}
