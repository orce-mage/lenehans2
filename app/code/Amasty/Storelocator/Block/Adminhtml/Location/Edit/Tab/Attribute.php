<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Attribute extends Generic
{
    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

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

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Base\Model\Serializer $serializer,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->attributeCollection = $attributeCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('attributes', ['legend' => __('Location Attributes')]);

        $widget = $this->_coreRegistry->registry('current_widget_instance');
        if (is_object($widget)) {
            $widgetParams = $this->serializer->unserialize($widget->getData('widget_parameters'));
        }

        foreach ($this->attributeCollection->preparedAttributes() as $attribute) {
            if ($attribute['frontend_input'] == 'boolean' || $attribute['frontend_input'] == 'select') {
                array_unshift($attribute['options'], ['label' => __('Please Select'), 'value' => -1]);
                $attribute['frontend_input'] = 'select';
            }

            $fieldset->addField(
                $attribute['attribute_id'],
                $attribute['frontend_input'],
                [
                    'name' => 'parameters[' . $attribute['attribute_id'] . ']',
                    'label' => $attribute['label'],
                    'values' => $attribute['options'],
                    'value' => isset($widgetParams[$attribute['attribute_id']]) ? $widgetParams[$attribute['attribute_id']] : null
                ]
            );
        }

        $element->setData('after_element_html', $fieldset->getHtml());

        return $element;
    }
}
