<?php

namespace MageBig\WidgetPlus\Block\Adminhtml\Widget\Renderer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\ObjectManagerInterface;

class Category extends Template implements RendererInterface
{

    protected $_serialize;

    protected $_element;

    protected $_template = 'MageBig_WidgetPlus::widget/category.phtml';

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Json $serialize,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_serialize = $serialize;
        $this->_objectManager = $objectManager;
    }

    public function render(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function getCategoriesTree()
    {
        $categories = $this->_objectManager->create(
            'Magento\Catalog\Ui\Component\Product\Form\Categories\Options'
        )->toOptionArray();

        return $this->_serialize->serialize($categories);
    }

    public function getValueArr() {
        $value = $this->getElement()->getValue();

        if ($value) {
            $value = explode(',', $value);
        } else {
            $value = [];
        }

        return $this->_serialize->serialize($value);
    }
}
