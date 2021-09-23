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

use Magetrend\PdfTemplates\Model\Config\Source\Adapter;

/**
 * Pdf element text config class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Text extends \Magetrend\PdfTemplates\Model\Pdf\ConfigAbstract
{
    /**
     * Returns text pdf element configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'icon' => 'glyphicon glyphicon-font',
            'label' => 'Text',
            'attributes' => [
                'width' => [
                    'label' => 'Width',
                    'group' => 'general',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
                    'attribute' => 'width'
                ],

                'height' => [
                    'label' => 'Height',
                    'group' => 'general',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
                    'attribute' => 'height'
                ],

                'top' => [
                    'label' => 'Top',
                    'group' => 'general',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
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
                    'className' => '.element_text',
                    'attribute' => 'left'
                ],

                'translate_content' => [
                    'group' => 'contenteditable',
                    'initEvent' => 'pdfElement.setTextByClass',
                    'onLoad' => '',
                    'onSave' => 'pdfElement.getTextByClass',
                    'className' => '.pdf-element-content',
                    'attribute' => 'text',
                ],

                'background_color' => [
                    'label' => 'Background Color',
                    'input' => 'color',
                    'group' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
                    'attribute' => 'background-color',
                    'colorGroup' => 'bg',
                ],

                'padding_left' => [
                    'label' => 'Padding Left',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-element-content',
                    'attribute' => 'padding-left'
                ],

                'padding_right' => [
                    'label' => 'Padding Right',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-element-content',
                    'attribute' => 'padding-right'
                ],

                'padding_top' => [
                    'label' => 'Padding Top',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-element-content',
                    'attribute' => 'padding-top'
                ],

                'padding_bottom' => [
                    'label' => 'Padding Bottom',
                    'input' => 'text',
                    'group' => 'padding',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-element-content',
                    'attribute' => 'padding-bottom'
                ],

                'border_size' => [
                    'label' => 'Border Size',
                    'input' => 'text',
                    'group' => 'settings',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
                    'attribute' => 'border-width'
                ],

                'border_style' => [
                    'label' => 'Border Style',
                    'input' => 'options',
                    'group' => 'settings',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
                    'attribute' => 'border-style',
                    'options' => [
                        'solid' => (string)__('Solid'),
                        'dashed' => (string)__('Dashed')
                    ]
                ],

                'font' => [
                    'label' => 'Font',
                    'input' => 'select',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
                    'attribute' => 'font-family',
                    'options' => $this->fontConfig->toArray()
                ],

                'font_size' => [
                    'label' => 'Font Size',
                    'input' => 'text',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
                    'attribute' => 'font-size',
                ],

                'text_line_height' => [
                    'label' => 'Line Height',
                    'input' => 'text',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.pdf-element-content',
                    'attribute' => 'line-height',
                ],

                'color' => [
                    'label' => 'Font Color',
                    'input' => 'color',
                    'group' => 'font',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
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
                    'className' => '.element_text',
                    'attribute' => 'lock'
                ],

                'cache' => [
                    'group' => 'action',
                    'label' => 'Cache Element',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_text',
                    'attribute' => 'cache'
                ],

                'first_page_only' => [
                    'group' => 'action',
                    'label' => 'First Page Only',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_text',
                    'attribute' => 'first-page-only'
                ],

                'last_page_only' => [
                    'group' => 'action',
                    'label' => 'Last Page Only',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_text',
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
                    'className' => '.element_text',
                    'attribute' => 'after-order-items'
                ],

                'direction' => [
                    'label' => 'Direction',
                    'group' => 'additional',
                    'input' => 'select',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_text',
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
                    'className' => '.element_text',
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
                    'className' => '.element_text',
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
                    'className' => '.element_text',
                    'attribute' => 'depends-on'
                ],

                'ifempty' => [
                    'label' => 'Hide element if variable is empty',
                    'group' => 'additional',
                    'input' => 'textarea',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttributeName',
                    'onSave' => 'pdfElement.getDataAttributeName',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_text',
                    'attribute' => 'ifempty'
                ],
            ]
        ];

        if ($this->moduleHelper->getAdapterName() == Adapter::ZEND_PDF) {
            $config['attributes']['text_align']  = [
                'label' => 'Text Align',
                'input' => 'select',
                'group' => 'font',
                'initEvent' => 'pdfElement.elementUpdateCss',
                'onLoad' => 'pdfElement.elementGetCss',
                'onSave' => 'pdfElement.elementGetCss',
                'onChange' => 'pdfElement.elementUpdateCss',
                'className' => '.element_text',
                'attribute' => 'text-align',
                'options' => $this->align->toArray()
            ];
        }

        return $config;
    }
}
