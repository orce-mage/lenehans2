<?php

namespace Wyomind\MassProductImport\Block\Adminhtml;

class Profiles extends \Wyomind\MassStockUpdate\Block\Adminhtml\Profiles
{

    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'Wyomind_MassProductImport';
    }
}
