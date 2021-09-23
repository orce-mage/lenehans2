<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml\Profiles;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = NULL;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_controller = 'adminhtml_profiles';
        $this->_blockGroup = 'Amasty_Orderexport';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class'          => 'save',
                'label'          => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );
        $this->buttonList->add(
            'run_profile_now',
            [
                'label'   => __('Run Profile Now'),
                'onclick' => "location.href='" . $this->getUrl('amasty_orderexport/*/run/id/' . $this->getRequest()->getParam('id') . '/') . "'",
                'class'   => 'apply'
            ],
            20
        );
    }
}
