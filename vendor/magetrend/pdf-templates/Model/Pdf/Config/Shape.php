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
 * Pdf element shape config class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Shape extends \Magetrend\PdfTemplates\Model\Pdf\ConfigAbstract
{
    /**
     * Returns shape pdf element configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'icon' => 'glyphicon glyphicon-unchecked',
            'label' => 'Shape',
            'attributes' => [
                'width' => [
                    'label' => 'Width',
                    'group' => 'general',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
                    'attribute' => 'left'
                ],

                'background_color' => [
                    'label' => 'Background Color',
                    'input' => 'color',
                    'group' => 'color',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_shape',
                    'attribute' => 'background-color',
                    'colorGroup' => 'bg',
                ],

                'border_color' => [
                    'label' => 'Border Color',
                    'input' => 'color',
                    'group' => 'border',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_shape',
                    'attribute' => 'border-color',
                    'colorGroup' => 'border',
                ],

                'border_size' => [
                    'label' => 'Border Size',
                    'input' => 'text',
                    'group' => 'border',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_shape',
                    'attribute' => 'border-width'
                ],

                'border_style' => [
                    'label' => 'Border Style',
                    'input' => 'select',
                    'group' => 'border',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_shape',
                    'attribute' => 'border-style',
                    'options' => [
                        'solid' => (string)__('Solid'),
                        'dashed' => (string)__('Dashed')
                    ]
                ],
                'rotate' => [
                    'label' => 'Rotate',
                    'input' => 'text',
                    'group' => 'general',
                    'initEvent' => 'pdfElement.rotateElement',
                    'onLoad' => 'pdfElement.getRotation',
                    'onSave' => 'pdfElement.getRotation',
                    'onChange' => 'pdfElement.rotateElement',
                    'className' => '.element_shape',
                    'attribute' => 'transform'
                ],
                'lock' => [
                    'group' => 'action',
                    'label' => 'Lock Element',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
                    'attribute' => 'after-order-items'
                ],

                'z-index' => [
                    'label' => 'Layer Sort Order',
                    'group' => 'additional',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
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
                    'className' => '.element_shape',
                    'attribute' => 'ifempty'
                ],
            ],
        ];

        return $config;
    }
}
