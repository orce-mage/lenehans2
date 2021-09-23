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
 * Pdf element image config class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Image extends \Magetrend\PdfTemplates\Model\Pdf\ConfigAbstract
{

    /**
     * Returns image pdf element configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'attributes' => [
                'width' => [
                    'label' => 'Width',
                    'group' => 'general',
                    'input' => 'text',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
                    'attribute' => 'left'
                ],

                'background_color' => [
                    'label' => 'Background Color',
                    'input' => 'color',
                    'group' => 'color',
                    'colorGroup' => 'bg',
                    'initEvent' => 'pdfElement.elementUpdateCss',
                    'onLoad' => 'pdfElement.elementGetCss',
                    'onSave' => 'pdfElement.elementGetCss',
                    'onChange' => 'pdfElement.elementUpdateCss',
                    'className' => '.element_image',
                    'attribute' => 'background-color'
                ],

                'src' => [
                    'label' => 'css_attributes Color',
                    'type' => 'string',
                    'initEvent' => 'pdfElement.elementUpdateAttribute',
                    'onLoad' => 'pdfElement.elementGetAttribute',
                    'onSave' => 'pdfElement.elementGetAttribute',
                    'onChange' => 'pdfElement.elementUpdateAttribute',
                    'className' => 'img',
                    'attribute' => 'src'
                ],

                'lock' => [
                    'group' => 'action',
                    'label' => 'Lock Element',
                    'input' => 'checkbox',
                    'initEvent' => 'pdfElement.setDataAttribute',
                    'onLoad' => 'pdfElement.getDataAttribute',
                    'onSave' => 'pdfElement.getDataAttribute',
                    'onChange' => 'pdfElement.setDataAttribute',
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
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
                    'className' => '.element_image',
                    'attribute' => 'ifempty'
                ],
            ]
        ];

        return $config;
    }
}
