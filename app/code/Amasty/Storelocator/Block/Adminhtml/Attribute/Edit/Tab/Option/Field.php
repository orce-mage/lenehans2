<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Option;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

/**
 * Class Field
 */
class Field extends Widget implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'attribute/options.phtml';

    /** @var Registry  */
    protected $registry;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Amasty\Base\Model\Serializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @return array|mixed
     */
    public function getStoresSortedBySortOrder()
    {
        $stores = $this->getStores();
        if (is_array($stores)) {
            usort($stores, function ($storeA, $storeB) {
                if ($storeA->getSortOrder() == $storeB->getSortOrder()) {
                    return $storeA->getId() < $storeB->getId() ? -1 : 1;
                }
                return ($storeA->getSortOrder() < $storeB->getSortOrder()) ? -1 : 1;
            });
        }
        return $stores;
    }

    /**
     * @return mixed
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }
        return $this->_getData('stores');
    }

    public function getOptionValues()
    {
        $values = $this->_getData('option_values');
        if ($values === null) {
            $values = [];

            $attribute = $this->registry->registry('current_amasty_storelocator_attribute');
            $optionCollection = $attribute->getOptions();
            if ($optionCollection) {
                $values = $this->_prepareOptionValues($attribute, $optionCollection);
            }

            $this->setData('option_values', $values);
        }

        return $values;
    }

    protected function _prepareOptionValues(
        $attribute,
        $optionCollection
    ) {
        $type = $attribute->getFrontendInput();
        if ($type === 'select' || $type === 'multiselect') {
            $defaultValues = explode(',', $attribute->getDefaultValue());
            $inputType = $type === 'select' ? 'radio' : 'checkbox';
        } else {
            $inputType = '';
        }

        $values = [];
        $defaultValues = 0;
        foreach ($optionCollection as $option) {
            if (isset($option['options_serialized'])) {
                $bunch = $this->_prepareUserDefinedAttributeOptionValues(
                    $option,
                    $inputType
                );
                foreach ($bunch as $value) {
                    $values[] = new \Magento\Framework\DataObject($value);
                }
            }
        }

        return $values;
    }

    protected function _prepareUserDefinedAttributeOptionValues($option, $inputType)
    {
        $value = [];

        $defaultValues = 0;
        if (isset($option['is_default'])) {
            $defaultValues = $option['is_default'];
        }

        $value['checked'] = $defaultValues ? 'checked="checked"' : '';
        $value['intype'] = $inputType;
        $value['id'] = $option['value_id'];
        $value['sort_order'] = $option['sort_order'];

        $options = $this->serializer->unserialize($option['options_serialized']);

        foreach ($options as $storeId => $storeValue) {
            $value['store' . $storeId] = $storeValue;
        }

        return [$value];
    }
}
