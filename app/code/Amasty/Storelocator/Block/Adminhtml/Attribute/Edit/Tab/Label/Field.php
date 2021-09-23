<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Label;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Field
 */
class Field extends \Magento\Backend\Block\Template implements RendererInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/attribute/labels.phtml';

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Base\Model\Serializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
        $this->registry = $registry;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores());
        }
        return $this->_getData('stores');
    }

    /**
     * Retrieve frontend labels of attribute for each store
     *
     * @return array
     */
    public function getLabelValues()
    {
        $model = $this->getAttributeObject();
        $values = [];
        $storeLabels = [];
        if (isset($model['label_serialized'])) {
            $storeLabels = $this->serializer->unserialize($model['label_serialized']);
        }

        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
        }

        return $values;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @codeCoverageIgnore
     */
    private function getAttributeObject()
    {
        return $this->registry->registry('current_amasty_storelocator_attribute');
    }
}
