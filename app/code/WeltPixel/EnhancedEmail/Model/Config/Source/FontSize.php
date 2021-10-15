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
 * Class FontSize
 * @package WeltPixel\EnhancedEmail\Model\Config\Source
 */
class FontSize implements ArrayInterface
{
    /**
     * @var array
     */
    protected $_sizes = array(
        'medium' => 'Medium (default)',
        'xx-small' => 'XX-Small',
        'x-small' => 'X-Small',
        'small' => 'Small',
        'large' => 'Large',
        'x-large' => 'X-Large',
        'xx-large' => 'XX-Large',
        'smaller' => 'Smaller (smaller than parent)',
        'larger' => 'Larger (larger than parent)',
        'inherit' => 'Inherit (from its parent)',
        'initial' => 'Initial (default value)',
        '1px'  => '1px' ,
        '2px'  => '2px' ,
        '3px'  => '3px' ,
        '4px'  => '4px' ,
        '5px'  => '5px' ,
        '6px'  => '6px' ,
        '7px'  => '7px' ,
        '8px'  => '8px' ,
        '9px'  => '9px' ,
        '10px' => '10px',
        '11px' => '11px',
        '12px' => '12px',
        '13px' => '13px',
        '14px' => '14px',
        '15px' => '15px',
        '16px' => '16px',
        '17px' => '17px',
        '18px' => '18px',
        '19px' => '19px',
        '20px' => '20px',
        '21px' => '21px',
        '22px' => '22px',
        '23px' => '23px',
        '24px' => '24px',
        '25px' => '25px',
        '26px' => '26px',
        '27px' => '27px',
        '28px' => '28px',
        '29px' => '29px',
        '30px' => '30px',
        '31px' => '31px',
        '32px' => '32px',
        '33px' => '33px',
        '34px' => '34px',
        '35px' => '35px',
        '36px' => '36px',
        '37px' => '37px',
        '38px' => '38px',
        '39px' => '39px',
        '40px' => '40px'


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