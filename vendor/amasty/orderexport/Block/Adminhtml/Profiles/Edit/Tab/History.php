<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Block\Adminhtml\Profiles\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class History extends Generic implements TabInterface
{

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $profile = $this->_coreRegistry->registry('current_amasty_orderexport');
        if ($profile && $profile->getId()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Run History');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Run History');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('profile_');

        $fieldset = $form->addFieldset('amrorderexport_history_tab', ['legend' => __('Run History')]);

        $grid = $this->getChildBlock('amorderexport.history.grid');

        $fieldset->addField('history_list', 'hidden', [
            'after_element_html' => "<div>{$grid->toHtml()}</div>",
        ]);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
