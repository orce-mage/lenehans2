<?php

namespace MageBig\WidgetPlus\Block\Adminhtml\Widget\Renderer;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Product extends Template implements RendererInterface
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Backend data.
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_collectionFactory;

    protected $_element;

    protected $_template = 'MageBig_WidgetPlus::widget/product.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $backendData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_jsonEncoder = $jsonEncoder;
        $this->_backendData = $backendData;
        $this->_collectionFactory = $collectionFactory;
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

    public function getProductsChooserUrl()
    {
        return $this->getUrl('widgetplus/widget/products', ['_current' => true]);
    }

    public function getRandom()
    {
        return $this->mathRandom;
    }
}
