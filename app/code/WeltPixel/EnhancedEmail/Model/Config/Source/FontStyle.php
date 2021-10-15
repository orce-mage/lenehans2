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
 * Class FontStyle
 * @package WeltPixel\EnhancedEmail\Model\Config\Source
 */
class FontStyle implements ArrayInterface
{
    /**
     * @var array
     */
    protected $_styles = array(
        'inherit' => 'Inherit (from its parent)',
        'normal' => 'Normal',
        'initial' => 'Initial',
        'italic' => 'Italic',
        'oblique' => 'Oblique'
    );

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        foreach ($this->_styles as $id => $style) :
            $options[] = array(
                'value' => $id,
                'label' => $style
            );
        endforeach;
        return $options;
    }
}