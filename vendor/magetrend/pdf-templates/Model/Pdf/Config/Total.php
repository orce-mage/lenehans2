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

namespace Magetrend\PdfTemplates\Model\Pdf\Config;

/**
 * Pdf element totals config class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Total extends \Magetrend\PdfTemplates\Model\Pdf\ConfigAbstract
{
    /**
     * Returns totals pdf element configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'icon' => 'glyphicon glyphicon-tasks',
            'label' => 'Totals',
            'attributes' => [
                'top' => [
                    'label' => 'Top',
                    'group' => 'general',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_total',
                    'attribute' => 'top'
                ],

                'left' => [
                    'label' => 'Left',
                    'group' => 'general',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_total',
                    'attribute' => 'left'
                ],

                'table_width' => [
                    'group' => 'general',
                    'label' => 'Table Width',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_total',
                    'attribute' => 'width'
                ],
                'table_height' => [
                    'group' => 'hidden',
                    'label' => 'Table Height',
                    'input' => 'text',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'className' => '.element_total',
                    'attribute' => 'height'
                ],

                'table_row_height' => [
                    'label' => 'Table Row Height',
                    'input' => 'text',
                    'group' => 'general',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item',
                    'attribute' => 'height'
                ],

                'table_column_label_width' => [
                    'label' => 'Label Column Width',
                    'input' => 'text',
                    'group' => 'general',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-label',
                    'attribute' => 'width'
                ],

                'table_column_value_width' => [
                    'label' => 'Value Column Width',
                    'input' => 'text',
                    'group' => 'general',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-value',
                    'attribute' => 'width'
                ],

                'table_row_1_background' => [
                    'label' => 'Total Background',
                    'input' => 'color',
                    'group' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-total',
                    'attribute' => 'background-color',
                    'colorGroup' => 'bg',
                ],

                'table_row_2_background' => [
                    'label' => 'Grand Total Background',
                    'input' => 'color',
                    'group' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-grand-total',
                    'attribute' => 'background-color',
                    'colorGroup' => 'bg',
                ],

                'table_row_font' => [
                    'label' => 'Row Font',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-total',
                    'attribute' => 'font-family',
                    'options' => $this->fontConfig->toArray()
                ],

                'table_row_font_size' => [
                    'label' => 'Row Font Size',
                    'input' => 'text',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-total',
                    'attribute' => 'font-size',
                ],

                'table_row_font_color' => [
                    'label' => 'Row Font Color',
                    'input' => 'color',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-total',
                    'attribute' => 'color',
                    'colorGroup' => 'font',
                ],

                'table_row_grand_total_font' => [
                    'label' => 'Grand Total Font',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-grand-total',
                    'attribute' => 'font-family',
                    'options' => $this->fontConfig->toArray()
                ],

                'table_row_grand_total_font_size' => [
                    'label' => 'Grand Total Font Size',
                    'input' => 'text',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-grand-total',
                    'attribute' => 'font-size',
                ],

                'table_row_grand_total_font_color' => [
                    'label' => 'Grant Total Font Color',
                    'input' => 'color',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-grand-total',
                    'attribute' => 'color',
                    'colorGroup' => 'font',
                ],

                'lock' => [
                    'group' => 'action',
                    'label' => 'Lock Element',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_total',
                    'attribute' => 'lock'
                ],

                'table_column_label_padding_left' => [
                    'label' => 'Label Padding Left',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-label',
                    'attribute' => 'padding-left'
                ],

                'table_column_label_padding_right' => [
                    'label' => 'Label Padding Right',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-label',
                    'attribute' => 'padding-right'
                ],

                'table_column_label_padding_top' => [
                    'label' => 'Label Padding Top',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-label',
                    'attribute' => 'padding-top'
                ],
                'table_column_label_padding_bottom' => [
                    'label' => 'Label Padding Bottom',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-label',
                    'attribute' => 'padding-bottom'
                ],

                'table_column_value_padding_left' => [
                    'label' => 'Value Padding Left',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-value',
                    'attribute' => 'padding-left'
                ],

                'table_column_value_padding_right' => [
                    'label' => 'Value Padding right',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-value',
                    'attribute' => 'padding-right'
                ],

                'table_column_value_padding_top' => [
                    'label' => 'Value Padding Top',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-value',
                    'attribute' => 'padding-top'
                ],

                'table_column_value_padding_bottom' => [
                    'label' => 'Value Padding Bottom',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-value',
                    'attribute' => 'padding-bottom'
                ],

                'table_row_line_height' => [
                    'label' => 'Total Line Height',
                    'input' => 'text',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-total td',
                    'attribute' => 'line-height'
                ],

                'table_row_grand_total_line_height' => [
                    'label' => 'Grand Total Line Height',
                    'input' => 'text',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item-grand-total td',
                    'attribute' => 'line-height'
                ],

                'value_align' => [
                    'label' => 'Value Align',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-value',
                    'attribute' => 'text-align',
                    'options' => $this->align->toArray()
                ],

                'translate_grand_total' => [
                    'group' => 'contenteditable',
                    'onLoad' => '',
                    'initEvent' => 'pdfElement.setTextByClass',
                    'onSave' => 'pdfElement.getTextByClass',
                    'className' => '.translate_grand_total',
                    'attribute' => 'text'
                ],

                'translate_tax' => [
                    'group' => 'contenteditable',
                    'onLoad' => '',
                    'initEvent' => 'pdfElement.setTextByClass',
                    'onSave' => 'pdfElement.getTextByClass',
                    'className' => '.translate_tax',
                    'attribute' => 'text'
                ],

                'translate_subtotal' => [
                    'group' => 'contenteditable',
                    'onLoad' => '',
                    'initEvent' => 'pdfElement.setTextByClass',
                    'onSave' => 'pdfElement.getTextByClass',
                    'className' => '.translate_subtotal',
                    'attribute' => 'text'
                ],

                'last_page_only' => [
                    'group' => 'action',
                    'label' => 'Last Page Only',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_total',
                    'attribute' => 'last-page-only'
                ],

                'after_order_items' => [
                    'group' => 'action',
                    'label' => 'After Order Items',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_total',
                    'attribute' => 'after-order-items'
                ],

                'table_border_inside_left_size' => [
                    'label' => 'Border Left',
                    'group' => 'border_inside',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.setInsideBorders',
                    'onLoad' => 'pdfElement.getInsideBorders',
                    'onSave' => 'pdfElement.getInsideBorders',
                    'onChange' => 'pdfElement.setInsideBorders',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-left-width'
                ],

                'table_border_inside_right_size' => [
                    'label' => 'Border Right',
                    'group' => 'border_inside',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.setInsideBorders',
                    'onLoad' => 'pdfElement.getInsideBorders',
                    'onSave' => 'pdfElement.getInsideBorders',
                    'onChange' => 'pdfElement.setInsideBorders',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-right-width'
                ],

                'table_border_inside_top_size' => [
                    'label' => 'Border Top',
                    'group' => 'border_inside',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.setInsideBorders',
                    'onLoad' => 'pdfElement.getInsideBorders',
                    'onSave' => 'pdfElement.getInsideBorders',
                    'onChange' => 'pdfElement.setInsideBorders',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-top-width'
                ],

                'table_border_inside_bottom_size' => [
                    'label' => 'Border Bottom',
                    'group' => 'border_inside',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.setInsideBorders',
                    'onLoad' => 'pdfElement.getInsideBorders',
                    'onSave' => 'pdfElement.getInsideBorders',
                    'onChange' => 'pdfElement.setInsideBorders',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-bottom-width'
                ],

                'table_border_inside_color' => [
                    'label' => 'Border Color',
                    'group' => 'border_inside',
                    'input' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCssBorder',
                    'onSave' => 'pdfElement.elementGetCssBorder',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-color',
                    'colorGroup' => 'border',

                ],

                'table_border_inside_style' => [
                    'label' => 'Border Style',
                    'input' => 'select',
                    'group' => 'border_inside',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCssBorder',
                    'onSave' => 'pdfElement.elementGetCssBorder',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-style',
                    'options' => [
                        'solid' => (string)__('Solid'),
                        'dashed' => (string)__('Dashed')
                    ]
                ],

                'table_border_outside_left_size' => [
                    'label' => 'Border Left',
                    'group' => 'border',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-left-width'
                ],

                'table_border_outside_right_size' => [
                    'label' => 'Border Right',
                    'group' => 'border',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-right-width'
                ],

                'table_border_outside_top_size' => [
                    'label' => 'Border Top',
                    'group' => 'border',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-top-width'
                ],

                'table_border_outside_bottom_size' => [
                    'label' => 'Border Bottom',
                    'group' => 'border',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-bottom-width'
                ],

                'table_border_outside_color' => [
                    'label' => 'Border Color',
                    'group' => 'border',
                    'input' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCssBorder',
                    'onSave' => 'pdfElement.elementGetCssBorder',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-color',
                    'colorGroup' => 'border',

                ],

                'table_border_outside_style' => [
                    'label' => 'Border Style',
                    'input' => 'select',
                    'group' => 'border',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCssBorder',
                    'onSave' => 'pdfElement.elementGetCssBorder',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-style',
                    'options' => [
                        'solid' => (string)__('Solid'),
                        'dashed' => (string)__('Dashed')
                    ]
                ],

                'direction' => [
                    'label' => 'Direction',
                    'group' => 'additional',
                    'input' => 'select',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_total',
                    'attribute' => 'direction',
                    'options' => $this->direction->toArray()
                ],

                'z-index' => [
                    'label' => 'Layer Sort Order',
                    'group' => 'additional',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_total',
                    'attribute' => 'z-index'
                ],

                'name' => [
                    'label' => 'Element Name',
                    'group' => 'additional',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttributeName',
                    'onSave' => 'pdfElement.getDataAttributeName',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_total',
                    'attribute' => 'name'
                ],

                'depends_on' => [
                    'label' => 'Depends on Element',
                    'group' => 'additional',
                    'input' => 'select',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onLoadOptions' => 'pdfElement.getElementNameOptions',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_total',
                    'attribute' => 'depends-on'
                ],
            ]
        ];

        return $config;
    }
}
