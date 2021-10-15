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
 * Class LetterSpacing
 * @package WeltPixel\EnhancedEmail\Model\Config\Source
 */
class LetterSpacing implements ArrayInterface
{
    /**
     * @var array
     */
    protected $_sizes = array(
        'normal' => 'Normal (no extra space)',
        'inherit' => 'Inherit (from its parent)',
        'initial' => 'Initial (default value)',
        '-3px'  => '-3px',
        '-2px'  => '-2px',
        '-1px'  => '-1px',
        '0.1px'  => '0.1px',
        '0.2px'  => '0.2px',
        '0.3px'  => '0.3px',
        '0.4px'  => '0.4px',
        '0.5px'  => '0.5px',
        '0.6px'  => '0.6px',
        '0.7px'  => '0.7px',
        '0.8px'  => '0.8px',
        '0.9px'  => '0.9px',
        '1px'  => '1px',
        '2px'  => '2px',
        '3px'  => '3px',
        '4px'  => '4px',
        '5px'  => '5px',
        '6px'  => '6px',
        '7px'  => '7px',
        '8px'  => '8px',
        '9px'  => '9px',
        '10px' => '10px',
        '11px' => '11px',
        '12px' => '12px',
        '13px' => '13px',
        '14px' => '14px',
        '15px' => '15px',

    );
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        foreach ($this->_sizes as $id => $size) :
            $options[] = array(
                'value' => $id,
                'label' => $size
            );
        endforeach;
        return $options;
    }
}