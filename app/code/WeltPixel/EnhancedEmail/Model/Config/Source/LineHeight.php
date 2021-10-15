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
 * Class LineHeight
 * @package WeltPixel\EnhancedEmail\Model\Config\Source
 */
class LineHeight implements ArrayInterface
{
    /**
     * @var array
     */
    protected $_heights = array(
        'normal' => 'Normal (no extra space)',
        'inherit' => 'Inherit (from its parent)',
        'initial' => 'Initial (default value)',
        '0.5' => '0.5',
        '0.6' => '0.6',
        '0.7' => '0.7',
        '0.8' => '0.8',
        '0.9' => '0.9',
        '1' => '1',
        '1.1' => '1.1',
        '1.2' => '1.2',
        '1.3' => '1.3',
        '1.4' => '1.4',
        '1.5' => '1.5',
        '1.6' => '1.6',
        '1.7' => '1.7',
        '1.8' => '1.8',
        '1.9' => '1.9',
        '2' => '2',
        '2.2' => '2.1',
        '2.3' => '2.3',
        '2.4' => '2.4',
        '2.5' => '2.5',
        '2.6' => '2.6',
        '2.7' => '2.7',
        '2.8' => '2.8',
        '2.9' => '2.9',
        '3' => '3',


    );

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        foreach ($this->_heights as $id => $height) :
            $options[] = array(
                'value' => $id,
                'label' => $height
            );
        endforeach;
        return $options;
    }
}