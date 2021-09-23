<?php

namespace MageBig\WidgetPlus\Block\Adminhtml\Widget\Form\Element;

use MageBig\WidgetPlus\Model\Widget\Source\TabMode;
use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Collection extends Template implements RendererInterface
{
    protected $_element;

    protected $_modelMode;

    /**
     * @var string
     */
    protected $_template = 'MageBig_WidgetPlus::widget/form/element/collection.phtml';

    public function getElement()
    {
        return $this->_element;
    }

    public function setElement(AbstractElement $element)
    {
        return $this->_element = $element;
    }

    public function render(AbstractElement $element)
    {
        $this->_element = $element;

        return $this->toHtml();
    }

    public function getOptions()
    {
        $output = [];
        $values = $this->getElement()->getValue();

        if (!is_array($values)) {
            $values = explode(',', $values);
        }
        //$sourceModel = $this->_objectManager->create('MageBig\WidgetPlus\Widget\Source\Tab\Mode');
        $this->_modelMode = new TabMode();
        $options = $this->_modelMode->toOptionArray();

        foreach ($values as $value) {
            foreach ($options as $option) {
                if ($option['value'] == $value) {
                    array_push($output, [
                        'value' => $option['value'],
                        'label' => $option['label'],
                        'selected' => true,
                    ]);
                }
            }
        }

        foreach ($options as $option) {
            if (!in_array($option['value'], $values)) {
                array_push($output, [
                    'value' => $option['value'],
                    'label' => $option['label'],
                    'selected' => false,
                ]);
            }
        }

        return $output;
    }
}
