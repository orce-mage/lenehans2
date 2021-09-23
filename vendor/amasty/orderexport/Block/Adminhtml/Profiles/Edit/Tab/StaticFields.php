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

class StaticFields extends Generic implements TabInterface
{
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Static Fields');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Static Fields');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_orderexport');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('profile_');

        $fieldset = $form->addFieldset('static_fields_fieldset', ['legend' => __('Static Fields')]);

        $staticFields = $this
            ->getLayout()
            ->createBlock(\Amasty\Orderexport\Block\Adminhtml\Profiles\Edit\Options\StaticFields::class);

        if ($staticFieldsData = $model->getStaticFields()) {
            $staticFields->setData(\Zend_Json::decode($staticFieldsData));
        }

        $fieldset->addField('export_static_fields_options', 'note', ['text' => $staticFields->toHtml()]);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
