<?php

namespace Wyomind\MassProductImport\Block\Adminhtml\Profiles;

class Edit extends \Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit
{

    public $module = "MassProductImport";

    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'Wyomind_MassProductImport';
    }
}
