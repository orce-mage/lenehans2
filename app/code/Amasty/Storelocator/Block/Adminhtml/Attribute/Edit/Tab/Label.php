<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

/**
 * Class Label
 */
class Label extends Generic implements TabInterface
{
    /** @var Yesno  */
    protected $yesno;

    protected $inputTypeFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesno
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesno,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        array $data = []
    ) {
        $this->yesno = $yesno;
        $this->inputTypeFactory = $inputTypeFactory;

        return parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Manage Labels');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

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
    public function isHidden()
    {
        return false;
    }
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $this->setForm($form);

        $fieldset =  $form->addField(
            'label',
            'text',
            [
                'name' => 'label',
                'label' => __('Label'),
                'title' => __('Label')
            ]
        );

        $form->getElement(
            'label'
        )->setRenderer(
            $this->getLayout()
                ->createBlock(\Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Label\Field::class)
        );

        return parent::_prepareForm();
    }

    public function getAttributeObject()
    {
        return $this->_coreRegistry->registry('current_amasty_storelocator_attribute');
    }
}
