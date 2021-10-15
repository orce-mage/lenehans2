<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class WebsafeFonts
 * @package WeltPixel\EnhancedEmail\Model\Config\Source
 */
class WebsafeFonts implements ArrayInterface
{
    /**
     * use '+' for ' ' and '_' for ', '
     * @var array
     */
    protected $_ssFonts = [
        'Arial_sans-serif' => 'Arial',
        'Helvetica_sans-serif' => 'Helvetica',
        'Trebuchet+MS_sans-serif' => 'Trebuchet MS',
        'Comic+Sans+MS_sans-serif' => 'Comic Sans MS',
        'Lucida+Grande_sans-serif' => 'Lucida Grande',
        'Verdana_sans-serif' => 'Verdana',


    ];

    /**
     * use '+' for ' ' and '_' for ', '
     * @var array
     */
    protected $_sFonts = [
        'Courier_serif' => 'Courier',
        'Georgia_serif' => 'Georgia',
        'Times+New+Roman_serif' => 'Times New Roman'
    ];

    /**
     * Return list Websafe Fonts
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {

        $firstOption = [
            'value' => 'inherit',
            'label' => __('Inherit (from its parent)')
        ];
        foreach ($this->_ssFonts as $v => $label) {
            $ssOptions[] = [
                'value' => $v,
                'label' => $label
            ];
        }
        $ssoptions = [
            'value' => $ssOptions,
            'label' => __('sans-serif')
        ];

        foreach ($this->_sFonts as $v => $label) {

            $sOptions[] = [
                'value' => $v,
                'label' => $label
            ];
        }
        $soptions = [
            'value' => $sOptions,
            'label' => __('serif')
        ];

        return [$firstOption, $ssoptions, $soptions];
    }
}