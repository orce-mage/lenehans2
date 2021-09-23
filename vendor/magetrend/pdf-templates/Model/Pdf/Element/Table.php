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
 * Draw pdf element items
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Table extends \Magetrend\PdfTemplates\Model\Pdf\Element
{

    /**
     * Draw table header
     */
    public function drawHeader()
    {
        $this->drawHeaderBackground();
        $this->drawInsideHeaderBorders();
        $this->writeHeaderText();
    }

    /**
     * Draw header background
     */
    public function drawHeaderBackground()
    {
        $attributes = $this->getAttributes();
        if (!isset($attributes['table_top_border_size'])) {
            $attributes['table_top_border_size'] = 0;
        }

        if (!isset($attributes['table_left_border_size'])) {
            $attributes['table_left_border_size'] = 0;
        }

        if (!isset($attributes['table_right_border_size'])) {
            $attributes['table_right_border_size'] = 0;
        }

        $attributes['top'] = $attributes['top'] + $attributes['table_top_border_size'];
        $attributes['left'] = $attributes['left'] + $attributes['table_left_border_size'];
        $attributes['table_width'] = $attributes['table_width']
            - ($attributes['table_left_border_size']+$attributes['table_right_border_size']);

        $topPos = $this->getTopPosition($attributes['top'], $attributes['left']);
        $bottom = $this->getBottomPosition(
            $attributes['top'],
            $attributes['left'],
            $attributes['table_width'],
            $this->getHeaderHeight()
        );

        if (isset($attributes['table_header_background'])) {
            if ($attributes['table_header_background'] == 'rgba(0, 0, 0, 0)') {
                return;
            }

            $this->pdfPage->setLineWidth(0);
            $this->pdfPage->setFillColor($this->getPdfColor($attributes['table_header_background']));
            $this->pdfPage->drawRectangle(
                $topPos['x'],
                $topPos['y'],
                $bottom['x'],
                $bottom['y'],
                \Zend_Pdf_Page::SHAPE_DRAW_FILL
            );
        }
    }

    /**
     * Draw table borders
     */
    public function drawTableBorders()
    {
        $attributes = $this->getAttributes();
        if (!isset($attributes['table_top_border_size'])) {
            $attributes['table_top_border_size'] = 0;
        }

        if (!isset($attributes['table_right_border_size'])) {
            $attributes['table_right_border_size'] = 0;
        }

        if (!isset($attributes['table_bottom_border_size'])) {
            $attributes['table_bottom_border_size'] = 0;
        }

        if (!isset($attributes['table_left_border_size'])) {
            $attributes['table_left_border_size'] = 0;
        }

        $info = $this->getInfo($this->elementData);
        $this->drawBorders(
            $info['pdf_top'],
            $info['pdf_left'],
            $info['pdf_width'],
            $info['pdf_height'] + $attributes['table_top_border_size'],
            [
                $attributes['table_top_border_size'],
                $attributes['table_right_border_size'],
                $attributes['table_bottom_border_size'],
                $attributes['table_left_border_size'],
            ],
            $attributes['table_border_color'],
            $attributes['table_border_style']
        );
    }

    /**
     * Draw borders inside
     */
    public function drawInsideHeaderBorders()
    {
        $attributes = $this->getAttributes();
        $columnConfig = $this->getColumnConfig();
        $lastColumnKey = '';

        if (!isset($attributes['table_top_border_size'])) {
            $attributes['table_top_border_size'] = 0;
        }

        if (!isset($attributes['table_header_border_inside_top_size'])) {
            $attributes['table_header_border_inside_top_size'] = 0;
        }

        if (!isset($attributes['table_header_border_inside_bottom_size'])) {
            $attributes['table_header_border_inside_bottom_size'] = 0;
        }

        if (!isset($attributes['table_header_border_inside_left_size'])) {
            $attributes['table_header_border_inside_left_size'] = 0;
        }

        if (!isset($attributes['table_header_border_inside_right_size'])) {
            $attributes['table_header_border_inside_right_size'] = 0;
        }

        foreach ($columnConfig as $key => $column) {
            if ($this->isColumnHidden($key)) {
                continue;
            }
            $lastColumnKey = $key;
        }

        $isFirst = true;
        foreach ($columnConfig as $key => $column) {
            if ($this->isColumnHidden($key)) {
                continue;
            }

            $leftBorderSize = $attributes['table_header_border_inside_left_size'];
            $rightBorderSize = $attributes['table_header_border_inside_right_size'];

            if ($key == $lastColumnKey) {
                $rightBorderSize = 0;
            }

            if ($isFirst) {
                $leftBorderSize = 0;
                $isFirst = false;
            }

            $this->drawBorders(
                $this->removePx($attributes['top']) + $this->removePx($attributes['table_top_border_size']),
                $this->getColumnLeft($key)*96/72,
                $attributes['table_header_'.$key.'_column_width'],
                $attributes['table_header_height'],
                [
                    $attributes['table_header_border_inside_top_size'],
                    $rightBorderSize,
                    $attributes['table_header_border_inside_bottom_size'],
                    $leftBorderSize,
                ],
                $attributes['table_header_border_inside_color'],
                $attributes['table_border_style']
            );
        }
    }

    /**
     * Write column title
     */
    public function writeHeaderText()
    {
        $attributes = $this->getAttributes();
        $attributes['table_header_color'] = '#000000';
        $fontSize = $this->toPoint($attributes['table_header_font_size']);
        $lineHeight = $this->toPoint(
            isset($attributes['table_header_line_height'])?$attributes['table_header_line_height']
                :($attributes['table_header_font_size']*1.5)
        );
        $font = $this->getPdfFont($attributes['table_header_font']);
        $color = $this->getPdfColor($attributes['table_header_font_color']);
        $this->pdfPage->setFont($font, $fontSize)
            ->setFillColor($color);

        if (!isset($attributes['table_top_border_size'])) {
            $attributes['table_top_border_size'] = 0;
        }

        $attributes['top'] = $attributes['top'] + $attributes['table_top_border_size'];

        $columnConfig = $this->getColumnConfig();
        foreach ($columnConfig as $key => $column) {
            if ($this->isColumnHidden($key)) {
                continue;
            }

            $align = isset($column['align'])?$column['align']:'left';
            if (isset($attributes[$key.'_align']) && !empty($attributes[$key.'_align'])) {
                $align = $attributes[$key.'_align'];
            }

            $textLine = $this->cleanString($attributes['translate_'.$key]);

            $x = $this->getColumnTextLeft($key);
            $columnWidth = $this->getColumnWidth($key);

            $headerText = $this->splitStringToLines(
                $textLine,
                $columnWidth,
                $attributes['table_header_font'],
                $fontSize
            );

            $lineCount = count($headerText);
            $blockHeight = $lineCount * $lineHeight;
            $y1 = $this->toPoint($attributes['top'])
                + ($this->toPoint($attributes['table_header_height'])/2 - $blockHeight/2);

            $this->drawTextLines(
                $headerText,
                $x,
                $y1,
                $lineHeight,
                $fontSize,
                $align,
                [],
                isset($attributes['direction'])?$attributes['direction']:Direction::LTR,
                $columnWidth
            );
        }
    }

    /**
     * Draw item texts
     * @param $columnsData
     * @param $topY
     */
    public function drawRowText($columnsData, $topY)
    {
        $attributes = $this->getAttributes();
        $columnConfig = $this->getColumnConfig();
        $direction = isset($attributes['direction'])?$attributes['direction']:Direction::LTR;

        foreach ($columnConfig as $columnName => $column) {
            if ($this->isColumnHidden($columnName) || !isset($columnsData[$columnName])) {
                continue;
            }

            $columnData = $columnsData[$columnName];
            $y1 = $topY + $columnData['padding'][0];
            $align = isset($column['align'])?$column['align']:'left';
            if (isset($attributes[$columnName.'_align']) && !empty($attributes[$columnName.'_align'])) {
                $align = $attributes[$columnName.'_align'];
            }

            $columnWidth = $this->getColumnWidth($columnName);

            foreach ($columnData['text'] as $lineKey => $textRow) {
                $fontSize = $textRow['font_size'];
                $lineHeight = $textRow['line_height'];
                $this->pdfPage->setFont($this->getPdfFont($textRow['font']), $this->toPoint($fontSize));
                $this->setFillColor($textRow['color']);
                $x = $this->getColumnTextLeft($columnName);

                if (isset($columnData['image'])) {
                    $imageConfig = $columnData['image'];
                    if ($direction == Direction::RTL) {
                        $x = $x - $this->toPoint($imageConfig['width'])
                            - $this->toPoint($imageConfig['right']) - $this->toPoint($imageConfig['left']);
                    } else {
                        $x = $x + $this->toPoint($imageConfig['width']) + $this->toPoint($imageConfig['left'])
                            + $this->toPoint($imageConfig['right']);
                    }
                }

                $this->drawTextLines(
                    $textRow['text'],
                    $x,
                    $this->toPoint($y1),
                    $this->toPoint($lineHeight),
                    $this->toPoint($fontSize),
                    $align,
                    isset($textRow['decorators'])?$textRow['decorators']:[],
                    $direction,
                    $columnWidth
                );

                $y1 = $y1 + count($textRow['text']) * $lineHeight;
            }
        }
    }

    /**
     * Draw row background
     *
     * @param $index
     * @param $topY
     * @param $height
     */
    public function drawRowBackground($index, $topY, $height)
    {
        $this->resetLines();
        $attributes = $this->getAttributes();
        $headerHeight = $this->getHeaderHeight();

        if (!isset($attributes['table_left_border_size'])) {
            $attributes['table_left_border_size'] = 0;
        }

        if (!isset($attributes['table_right_border_size'])) {
            $attributes['table_right_border_size'] = 0;
        }

        $tableWidth = $attributes['table_width']
            - ($attributes['table_left_border_size'] + $attributes['table_right_border_size']);
        $attributes['left'] = $attributes['left'] + $attributes['table_left_border_size'];

        if (($index%2) == 0) {
            $rowColor = $attributes['table_row_1_background'];
        } else {
            $rowColor = $attributes['table_row_2_background'];
        }

        if ($rowColor == 'rgba(0, 0, 0, 0)') {
            return;
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

    public function getHeaderHeight()
    {
        $attributes = $this->getAttributes();
        if (!isset($attributes['table_top_border_size'])) {
            $attributes['table_top_border_size'] = 0;
        }

        return ((int)$this->removePx($attributes['table_header_height'])) + ((int)$this->removePx($attributes['table_top_border_size']));
    }

    public function isColumnHidden($key)
    {
        $attributes = $this->getAttributes();
        $hideAttribute = isset($attributes['hide_column_'.$key])?$attributes['hide_column_'.$key]:false;
        if ($hideAttribute == 'true' || $hideAttribute == 1) {
            return true;
        }

        return false;
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
            $attributes['table_row_cell_padding_top'],
            $attributes['table_row_cell_padding_right'],
            $attributes['table_row_cell_padding_bottom'],
            $attributes['table_row_cell_padding_left']
        ];
    }

    /**
     * Returns element results
     *
     * @return array
     */
    public function getInfo($elementData)
    {
        $attributes = $this->getAttributes();
        return [
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
    }

    /**
     * Returns how much space needs for elements in last page
     *
     * @return float|int|null
     */
    public function getLastPageFooterHeight()
    {
        if ($this->lastPageFooterHeight == null) {
            $fotterHeight = $this->moduleHelper->removePx($this->template->getFooterHeight());
            $elementGroupHeight = $this->registry->registry('space_prediction_last_page');
            $fotterHeight = !empty($fotterHeight)?$fotterHeight:0;
            $this->lastPageFooterHeight = $fotterHeight + $elementGroupHeight;
        }
        return $this->lastPageFooterHeight;
    }

    /**
     * Check is there space for invoice item
     *
     * @param $rowHeight
     * @param $currentY
     * @param $isLastItem
     * @return bool
     */
    public function isEnoughSpaceForItem($rowHeight, $currentY, $isLastItem)
    {
        $rowHeight = $this->toPoint($rowHeight);
        $currentY = $this->toPoint($currentY);
        $pageHeight = $this->pdfPage->getHeight();
        $footerHeight = $this->toPoint($this->template->getFooterHeight());

        if (!$isLastItem && $currentY + $rowHeight + $footerHeight <= $pageHeight) {
            return true;
        }

        $lastPageFooterHeight = $this->toPoint($this->getLastPageFooterHeight());
        if ($isLastItem && $currentY + $rowHeight + $lastPageFooterHeight <= $pageHeight) {
            return true;
        }

        return false;
    }

    /**
     * Returns last item Y possition
     * @return int
     */
    public function getLastItemY()
    {
        return $this->lastItemY;
    }

    /**
     * Set flag: is all items processed
     *
     * @param $cond
     */
    public function setIsFinished($cond)
    {
        $this->isFinished = $cond;
    }

    /**
     * Returns flag: is all items processed
     *
     * @param $cond
     */
    public function getIsFinished()
    {
        return $this->isFinished;
    }

    /**
     * Returns possition where start write column text
     *
     * @param $columnName
     * @return float|int
     */
    public function getColumnTextLeft($columnName)
    {
        $attributes = $this->getAttributes();
        $left = $this->getColumnLeft($columnName);
        $left += $this->toPoint($attributes['table_header_padding_left']);
        return $left;
    }

    public function getColumnTextRight($columnName)
    {
        $attributes = $this->getAttributes();
        $right = $this->getColumnLeft($columnName);
        if (isset($attributes['table_header_padding_right'])) {
            $right -= $this->toPoint($attributes['table_header_padding_right']);
        }
        $right += $this->toPoint($attributes['table_header_'.$columnName.'_column_width']);
        return $right;
    }

    public function getColumnWidth($columnName, $leftPadding = false, $rightPadding = false)
    {
        $attributes = $this->getAttributes();
        $width = $attributes['table_header_'.$columnName.'_column_width'];
        if (!$leftPadding && isset($attributes['table_header_padding_left'])) {
            $width -= $attributes['table_header_padding_left'];
        }

        if (!$rightPadding && isset($attributes['table_header_padding_right'])) {
            $width -= $attributes['table_header_padding_right'];
        }

        return $this->toPoint($width);
    }

    /**
     * Returns current left border X
     *
     * @param $columnName
     * @return float|int
     */
    public function getColumnLeft($columnName)
    {
        $columnConfig = $this->getColumnConfig();
        $attributes = $this->getAttributes();

        if (!isset($attributes['table_left_border_size'])) {
            $attributes['table_left_border_size'] = 0;
        }

        $left = $attributes['left'] + $attributes['table_left_border_size'];

        $dependsOn = [];
        foreach ($columnConfig as $key => $column) {
            if ($this->isColumnHidden($key)) {
                continue;
            }
            if ($key == $columnName) {
                break;
            }

            $dependsOn[] = $key;
        }

        if (!empty($dependsOn)) {
            foreach ($dependsOn as $key) {
                $columnWidth = (int)str_replace('px', '', $attributes['table_header_'.$key.'_column_width']);
                $left+=$columnWidth;
            }
        }

        return $this->toPoint($left);
    }

    /**
     * Calculate item row height
     *
     * @param $columnData
     * @return array|string
     */
    public function getRowHeight($columnData)
    {
        $attributes = $this->getAttributes();
        $maxHeight = $this->removePx($attributes['table_row_height']);
        foreach ($columnData as $column) {
            if (isset($column['hide']) && $column['hide']) {
                continue;
            }

            if ($maxHeight < $column['height']) {
                $maxHeight = $column['height'];
            }
        }

        return $maxHeight;
    }

    public function drawRowHorizontalBorder($index, $topY, $height)
    {
        $attributes = $this->getAttributes();
        if ($index == 0 || !isset($attributes['table_row_border_inside_horizontal_size'])) {
            return;
        }

        $tableWidth = $attributes['table_width']
            - ($attributes['table_left_border_size'] + $attributes['table_right_border_size']);

        $left = $attributes['left'] + $attributes['table_left_border_size'];

        $this->drawBorders(
            $topY,
            $left,
            $tableWidth,
            $height,
            [
                $attributes['table_row_border_inside_horizontal_size'],
                0,
                0,
                0,
            ],
            $attributes['table_row_border_inside_color'],
            $attributes['table_row_border_inside_style']
        );
    }

    public function drawRowVerticalBorder($topY, $height)
    {
        $attributes = $this->getAttributes();
        if (!isset($attributes['table_row_border_inside_vertical_size'])) {
            return;
        }
        $columnConfig = $this->getColumnConfig();

        $lastItemKey = '';
        foreach ($columnConfig as $columnName => $column) {
            if ($this->isColumnHidden($columnName)) {
                continue;
            }

            $lastItemKey = $columnName;
        }
        foreach ($columnConfig as $columnName => $column) {
            if ($this->isColumnHidden($columnName)) {
                continue;
            }

            if ($lastItemKey == $columnName) {
                break;
            }

            $columnWidth = $this->getColumnWidth($columnName);
            $left = $this->getColumnLeft($columnName);

            $this->drawBorders(
                $topY,
                $left*96/72,
                $attributes['table_header_'.$columnName.'_column_width'],
                $height,
                [
                    0,
                    $attributes['table_row_border_inside_vertical_size'],
                    0,
                    0,
                ],
                $attributes['table_row_border_inside_color'],
                $attributes['table_row_border_inside_style']
            );
        }
    }
}
