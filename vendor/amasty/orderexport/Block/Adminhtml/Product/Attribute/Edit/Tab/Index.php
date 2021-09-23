<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml\Product\Attribute\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;

class Index extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_attributeFactory;

    public function __construct(

        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Amasty\Orderexport\Model\AttributeFactory $attributeFactory,
        array $data = []
    ) {
        $this->_attributeFactory = $attributeFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $eavData,
            $yesnoFactory,
            $inputTypeFactory,
            $propertyLocker,
            $data
        );
    }

    /**
     * Tab settings
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Efficient Order Export');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Efficient Order Export');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $catalogAttributeObject = $this->getAttributeObject();

        $attributeObject = $this->_attributeFactory->create()->load($catalogAttributeObject->getId(), 'attribute_id');

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'amasty_orderexport_index_fieldset',
            ['legend' => __('Efficient Order Export'), 'collapsable' => true]
        );

        $yesno = $this->_yesnoFactory->create()->toOptionArray();

        $fieldset->addField(
            'amasty_orderexport_use_in_index',
            'select',
            [
                'name' => 'amasty_orderexport_use_in_index',
                'label' => __('Add to Options'),
                'title' => __('Add to Options'),
                'note' => __('Select "Yes" to add this attribute to the list of options in the efficient order export.'),
                'values' => $yesno,
                'value' => $attributeObject->getId() ? 1 : 0
            ]
        );

        $this->setForm($form);

        return $this;
    }

    public function getAfter()
    {
        return 'front';
    }
}