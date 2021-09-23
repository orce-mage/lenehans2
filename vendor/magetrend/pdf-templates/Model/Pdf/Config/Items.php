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
 * Pdf element items config class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Items extends \Magetrend\PdfTemplates\Model\Pdf\ConfigAbstract
{
    /**
     * Returns items pdf element configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'icon' => 'glyphicon glyphicon-list-alt',
            'label' => 'Items',
            'attributes' => [
                'top' => [
                    'label' => 'Top',
                    'group' => 'general',
                    'input' => 'text',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_items',
                    'attribute' => 'top'
                ],

                'left' => [
                    'label' => 'Left',
                    'group' => 'general',
                    'input' => 'text',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_items',
                    'attribute' => 'left'
                ],

                'direction' => [
                    'label' => 'Direction',
                    'group' => 'additional',
                    'input' => 'select',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_items',
                    'attribute' => 'direction',
                    'options' => $this->direction->toArray()
                ],

                'table_width' => [
                    'group' => 'general',
                    'label' => 'Table Width',
                    'input' => 'text',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_items',
                    'attribute' => 'width'
                ],
                'table_height' => [
                    'group' => 'hidden',
                    'format' => ['remove_px'],
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'className' => '.element_items',
                    'attribute' => 'height'
                ],

                'table_header_height' => [
                    'label' => 'Table Header Height',
                    'group' => 'general',
                    'input' => 'text',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header',
                    'attribute' => 'height'
                ],

                'table_row_height' => [
                    'label' => 'Table Row Height',
                    'input' => 'text',
                    'group' => 'general',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item',
                    'attribute' => 'height'
                ],

                'table_header_background' => [
                    'label' => 'Table Header Background',
                    'input' => 'color',
                    'group' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header',
                    'attribute' => 'background-color',
                    'colorGroup' => 'bg',
                ],

                'table_header_border_inside_right_size' => [
                    'label' => 'Header Vertical Border',
                    'group' => 'border_inside',
                    'input' => 'text',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.setHeaderVerticalBorders',
                    'onLoad' => 'pdfElement.getInsideBorders',
                    'onSave' => 'pdfElement.getInsideBorders',
                    'onChange' => 'pdfElement.setHeaderVerticalBorders',
                    'className' => '.pdf-table-header th',
                    'attribute' => 'border-right-width'
                ],

                'table_header_border_inside_bottom_size' => [
                    'label' => 'Header Horizontal Border',
                    'group' => 'border_inside',
                    'input' => 'text',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.setHeaderHorizontalBorders',
                    'onLoad' => 'pdfElement.getInsideBorders',
                    'onSave' => 'pdfElement.getInsideBorders',
                    'onChange' => 'pdfElement.setHeaderHorizontalBorders',
                    'className' => '.pdf-table-header th',
                    'attribute' => 'border-bottom-width'
                ],

                'table_header_border_inside_color' => [
                    'label' => 'Header Border Color',
                    'group' => 'border_inside',
                    'input' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header th',
                    'attribute' => 'border-color',
                    'colorGroup' => 'border',
                ],

                'table_header_border_inside_style' => [
                    'label' => 'Header Border Style',
                    'input' => 'select',
                    'group' => 'border_inside',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header th',
                    'attribute' => 'border-style',
                    'options' => [
                        'solid' => (string)__('Solid'),
                        'dashed' => (string)__('Dashed')
                    ]
                ],

                'table_row_border_inside_vertical_size' => [
                    'label' => 'Row Vertical Border',
                    'group' => 'border_inside',
                    'input' => 'text',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.setVerticalBorders',
                    'onLoad' => 'pdfElement.getInsideBorders',
                    'onSave' => 'pdfElement.getInsideBorders',
                    'onChange' => 'pdfElement.setVerticalBorders',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-right-width'
                ],

                'table_row_border_inside_horizontal_size' => [
                    'label' => 'Row Horizontal Border',
                    'group' => 'border_inside',
                    'input' => 'text',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.setHorizontalBorders',
                    'onLoad' => 'pdfElement.getInsideBorders',
                    'onSave' => 'pdfElement.getInsideBorders',
                    'onChange' => 'pdfElement.setHorizontalBorders',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-bottom-width'
                ],

                'table_row_border_inside_color' => [
                    'label' => 'Row Border Color',
                    'group' => 'border_inside',
                    'input' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-color',
                    'colorGroup' => 'border',
                ],

                'table_row_border_inside_style' => [
                    'label' => 'Row Border Style',
                    'input' => 'select',
                    'group' => 'border_inside',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'border-style',
                    'options' => [
                        'solid' => (string)__('Solid'),
                        'dashed' => (string)__('Dashed')
                    ]
                ],

                'table_row_1_background' => [
                    'label' => 'Table Row 1 Background',
                    'input' => 'color',
                    'group' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item.line-1',
                    'attribute' => 'background-color',
                    'colorGroup' => 'bg',
                ],

                'table_row_2_background' => [
                    'label' => 'Table Row 2 Background',
                    'input' => 'color',
                    'group' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item.line-2',
                    'attribute' => 'background-color',
                    'colorGroup' => 'bg',
                ],

                'table_border_color' => [
                    'label' => 'Table Border Color',
                    'input' => 'color',
                    'group' => 'border',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-color',
                    'colorGroup' => 'border',
                ],

                'table_top_border_size' => [
                    'label' => 'Table Top Border Size',
                    'input' => 'text',
                    'group' => 'border',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-top-width'
                ],

                'table_right_border_size' => [
                    'label' => 'Table Right Border Size',
                    'input' => 'text',
                    'group' => 'border',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-right-width'
                ],

                'table_bottom_border_size' => [
                    'label' => 'Table Bottom Border Size',
                    'input' => 'text',
                    'group' => 'border',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-bottom-width'
                ],

                'table_left_border_size' => [
                    'label' => 'Table Left Border Size',
                    'input' => 'text',
                    'group' => 'border',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-left-width'
                ],

                'table_border_style' => [
                    'label' => 'Border Style',
                    'input' => 'select',
                    'group' => 'border',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table',
                    'attribute' => 'border-style',
                    'options' => [
                        'solid' => (string)__('Solid'),
                        'dashed' => (string)__('Dashed')
                    ]
                ],

                'table_header_font' => [
                    'label' => 'Header Font',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header',
                    'attribute' => 'font-family',
                    'options' => $this->fontConfig->toArray()
                ],

                'table_header_font_size' => [
                    'label' => 'Header Font Size',
                    'input' => 'text',
                    'group' => 'font',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header',
                    'attribute' => 'font-size',
                ],

                'table_header_line_height' => [
                    'input' => 'text',
                    'group' => 'hidden',
                    'format' => ['remove_px'],
                    'onSave' => 'pdfElement.elementGetCss',
                    'className' => '.pdf-table-item',
                    'attribute' => 'line-height',
                ],

                'table_header_font_color' => [
                    'label' => 'Header Font Color',
                    'input' => 'color',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header',
                    'attribute' => 'color',
                    'colorGroup' => 'font',
                ],


                'table_row_font' => [
                    'label' => 'Row Font',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item',
                    'attribute' => 'font-family',
                    'options' => $this->fontConfig->toArray()
                ],

                'table_row_font_size' => [
                    'label' => 'Row Font Size',
                    'input' => 'text',
                    'group' => 'font',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item',
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
                    'className' => '.pdf-table-item',
                    'attribute' => 'color',
                    'colorGroup' => 'font',
                ],

                'table_row_line_height' => [
                    'input' => 'text',
                    'group' => 'hidden',
                    'format' => ['remove_px'],
                    'onSave' => 'pdfElement.elementGetCss',
                    'className' => '.pdf-table-item',
                    'attribute' => 'line-height',
                ],

                'table_row_product_line_1_font' => [
                    'label' => 'Product Name Font',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.product-name-line-1',
                    'attribute' => 'font-family',
                    'options' => $this->fontConfig->toArray()
                ],

                'table_row_product_line_1_size' => [
                    'label' => 'Product Name Font Size',
                    'input' => 'text',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.product-name-line-1',
                    'attribute' => 'font-size',
                ],

                'table_row_product_line_1_color' => [
                    'label' => 'Product Name Font Color',
                    'input' => 'color',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.product-name-line-1',
                    'attribute' => 'color',
                    'colorGroup' => 'font',
                ],

                'table_row_product_line_1_line_height' => [
                    'input' => 'text',
                    'group' => 'hidden',
                    'format' => ['remove_px'],
                    'onSave' => 'pdfElement.elementGetCss',
                    'className' => '.product-name-line-1',
                    'attribute' => 'line-height',
                ],
                'table_row_product_line_2_line_height' => [
                    'input' => 'text',
                    'group' => 'hidden',
                    'format' => ['remove_px'],
                    'onSave' => 'pdfElement.elementGetCss',
                    'className' => '.product-name-line-2',
                    'attribute' => 'line-height',

                ],

                'table_row_product_line_2_font' => [
                    'label' => 'Product Options Font',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.product-name-line-2',
                    'attribute' => 'font-family',
                    'options' => $this->fontConfig->toArray()
                ],

                'table_row_product_line_2_size' => [
                    'label' => 'Product Options Font Size',
                    'input' => 'text',
                    'group' => 'font',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.product-name-line-2',
                    'attribute' => 'font-size',
                ],

                'table_row_product_line_2_color' => [
                    'label' => 'Product Options Font Color',
                    'input' => 'color',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.product-name-line-2',
                    'attribute' => 'color',
                    'colorGroup' => 'font',
                ],

                'table_row_product_line_2_value_color' => [
                    'label' => 'Options Value Font Color',
                    'input' => 'color',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.product-name-line-2 span',
                    'attribute' => 'color',
                    'colorGroup' => 'font',
                ],

                'price_align' => [
                    'label' => 'Price Align',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.column-price',
                    'attribute' => 'text-align',
                    'options' => $this->align->toArray()
                ],

                'subtotal_align' => [
                    'label' => 'Subtotal Align',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.column-subtotal',
                    'attribute' => 'text-align',
                    'options' => $this->align->toArray()
                ],

                'tax_align' => [
                    'label' => 'Tax Align',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.column-tax',
                    'attribute' => 'text-align',
                    'options' => $this->align->toArray()
                ],

                'qty_align' => [
                    'label' => 'Qty Align',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.column-qty',
                    'attribute' => 'text-align',
                    'options' => $this->align->toArray()
                ],

                'lock' => [
                    'group' => 'action',
                    'label' => 'Lock Element',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_items',
                    'attribute' => 'lock'
                ],

                'show_image' => [
                    'group' => 'action',
                    'label' => 'Show Image',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.initProductImage',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.toggleProductImage',
                    'className' => '.element_items',
                    'attribute' => 'show_image'
                ],

                'image_width' => [
                    'label' => 'Width',
                    'input' => 'text',
                    'group' => 'image',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.imageUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.imageUpdateCss',
                    'className' => '.product-image',
                    'attribute' => 'width'
                ],

                'image_margin_top' => [
                    'label' => 'Margin Top',
                    'input' => 'text',
                    'group' => 'image',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.imageUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.imageUpdateCss',
                    'className' => '.product-image',
                    'attribute' => 'margin-top'
                ],

                'image_margin_right' => [
                    'label' => 'Margin Right',
                    'input' => 'text',
                    'group' => 'image',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.imageUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.imageUpdateCss',
                    'className' => '.product-image',
                    'attribute' => 'margin-right'
                ],

                'image_margin_bottom' => [
                    'label' => 'Margin Bottom',
                    'input' => 'text',
                    'group' => 'image',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.imageUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.imageUpdateCss',
                    'className' => '.product-image',
                    'attribute' => 'margin-bottom'
                ],

                'image_margin_left' => [
                    'label' => 'Margin Left',
                    'input' => 'text',
                    'group' => 'image',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.imageUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.imageUpdateCss',
                    'className' => '.product-image',
                    'attribute' => 'margin-left'
                ],

                'table_header_padding_left' => [
                    'label' => 'Header Padding Left',
                    'input' => 'text',
                    'group' => 'padding',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header th',
                    'attribute' => 'padding-left'
                ],

                'table_header_padding_right' => [
                    'label' => 'Header Padding Right',
                    'input' => 'text',
                    'group' => 'padding',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header th',
                    'attribute' => 'padding-right'
                ],

                'table_header_padding_top' => [
                    'label' => 'Header Padding Top',
                    'input' => 'text',
                    'group' => 'padding',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-header th',
                    'attribute' => 'padding-top'
                ],

                'table_row_cell_padding_left' => [
                    'label' => 'Row Padding Left',
                    'input' => 'text',
                    'group' => 'padding',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'padding-left'
                ],

                'table_row_cell_padding_right' => [
                    'label' => 'Row Padding Right',
                    'input' => 'text',
                    'group' => 'padding',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'padding-right'
                ],

                'table_row_cell_padding_top' => [
                    'label' => 'Row Padding Top',
                    'input' => 'text',
                    'group' => 'padding',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'padding-top'
                ],

                'table_row_cell_padding_bottom' => [
                    'label' => 'Row Padding Bottom',
                    'input' => 'text',
                    'group' => 'padding',
                    'format' => ['remove_px'],
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-table-item td',
                    'attribute' => 'padding-bottom'
                ],


            ]
        ];



        $config = $this->addColumns($config);

        $config['attributes']['z-index'] = [
            'label' => 'Layer Sort Order',
            'group' => 'general',
            'input' => 'text',
            'initEvent' => 'pdfElement.elementUpdateCss',
            'onLoad' => 'pdfElement.elementGetCss',
            'onSave' => 'pdfElement.elementGetCss',
            'onChange' => 'pdfElement.elementUpdateCss',
            'className' => '.element_items',
            'attribute' => 'z-index'
        ];

        return $config;
    }

    public function addColumns($config)
    {
        $template = $this->coreRegistry->registry('current_pdf_template');
        $columnConfig = $this->moduleHelper->getColumnConfig($template->getType());
        if (empty($columnConfig)) {
            return $config;
        }

        foreach ($columnConfig as $key => $column) {
            $config['attributes']['translate_'.$key] = [
                'group' => 'contenteditable',
                'onLoad' => '',
                'initEvent' => 'pdfElement.setTextByClass',
                'onSave' => 'pdfElement.getTextByClass',
                'className' => '.pdf-translate-'.$key,
                'attribute' => 'text'
            ];

            $config['attributes']['table_header_'.$key.'_column_width'] = [
                'label' => $column['label'].' Column Width',
                'input' => 'text',
                'group' => 'general',
                'format' => ['remove_px'],
                'initEvent' => 'pdfElement.elementUpdateCss',
                'onLoad' => 'pdfElement.elementGetCss',
                'onSave' => 'pdfElement.elementGetCss',
                'onChange' => 'pdfElement.elementUpdateCss',
                'className' => '.pdf-table-header th.pdf-translate-'.$key,
                'attribute' => 'width'
            ];

            $config['attributes']['hide_column_'.$key] = [
                'group' => 'action',
                'label' => 'Hide '.$column['label'].' Column',
                'input' => 'checkbox',
                'initEvent' => 'pdfElement.toggleTableColumn',
                'onLoad' => 'pdfElement.getDataAttribute',
                'onSave' => 'pdfElement.getDataAttribute',
                'onChange' => 'pdfElement.toggleTableColumn',
                'className' => '.element_items',
                'attribute' => 'hide-'.$key
            ];
        }
        return $config;
    }
}
