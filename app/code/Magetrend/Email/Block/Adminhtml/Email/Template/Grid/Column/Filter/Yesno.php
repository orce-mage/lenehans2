<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Adminhtml\Email\Template\Grid\Column\Filter;

class Yesno extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{

    public $types = [
        null => null,
        1 => 'Yes',
        0 => 'No',
    ];

    /**
     * Get options
     *
     * @return array
     */
    protected function _getOptions()
    {
        $result = [];
        foreach ($this->types as $code => $label) {
            $result[] = ['value' => $code, 'label' => __($label)];
        }

        return $result;
    }

    /**
     * Get condition
     *
     * @return array|null
     */
    public function getCondition()
    {
        if ($this->getValue() === null) {
            return null;
        }

        return ['eq' => $this->getValue()];
    }
}
