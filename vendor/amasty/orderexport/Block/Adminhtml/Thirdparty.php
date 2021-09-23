<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml;

class Thirdparty extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller     = 'thirdparty';
        $this->_headerText     = __('Amasty Orders Export Thirdparty');
        $this->_addButtonLabel = __('Add New Thirdparty Profile');
        parent::_construct();
    }
}
