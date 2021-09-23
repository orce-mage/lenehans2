<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml;

class Profiles extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller     = 'profiles';
        $this->_headerText     = __('Amasty Orders Export Profiles');
        $this->_addButtonLabel = __('Add New Profile');
        parent::_construct();
    }
}
