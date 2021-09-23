<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\AjaxFilter\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\ObjectManager;

class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    private $tagFilter;

    protected $objectManager;

    protected $helper;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );
        $this->tagFilter = $tagFilter;
        $this->objectManager = ObjectManager::getInstance();
        $this->helper = $this->objectManager->get('MageBig\AjaxFilter\Helper\Data');
    }

    public function applyToCollection($productCollection, $request, $requestVar)
    {
        $attributeValue = $request->getParam($requestVar);
        if ($attributeValue) {
            $attributeValuesArray = explode(',', (string)$attributeValue);
            $productCollection->addFieldToFilter($requestVar, $attributeValuesArray);
        }
        return $productCollection;
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);

        if (empty($attributeValue) && !is_numeric($attributeValue) || is_array($attributeValue)) {
            return $this;
        }

        $productCollection = $this->getLayer()->getProductCollection();
        $attribute = $this->getAttributeModel();
        $attributeCode = $attribute->getAttributeCode();

        $this->setBeforeApplyFacetedData($this->helper->getBeforeApplyFacetedData($productCollection, $attributeCode));

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */

        $attributeValuesArray = explode(',', (string)$attributeValue);

        $productCollection->addFieldToFilter($attributeCode, $attributeValuesArray);

        $label = $this->getOptionText($attributeValue);
        $this->getLayer()
            ->getState()
            ->addFilter($this->_createItem($label, $attributeValue));

        if (!count($this->getAttributeModel()->getFrontend()->getSelectOptions())) {
            $this->setItems([]);
        }

        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $attributeCode = $attribute->getAttributeCode();

        if (!$productCollection = $this->getBeforeApplyFacetedData()) {
            $productCollection = $this->getLayer()->getProductCollection();
        }

        $optionsFacetedData = $productCollection->getFacetedData($attributeCode);
        $isAttributeFilterable =
            $this->getAttributeIsFilterable($attribute) === static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS;

        if (count($optionsFacetedData) === 0 && !$isAttributeFilterable) {
            return $this->itemDataBuilder->build();
        }

        $productSize = $productCollection->getSize();

        $options = $attribute->getFrontend()
            ->getSelectOptions();
        foreach ($options as $option) {
            $this->buildOptionData($option, $isAttributeFilterable, $optionsFacetedData, $productSize);
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * Build option data
     *
     * @param array $option
     * @param boolean $isAttributeFilterable
     * @param array $optionsFacetedData
     * @param int $productSize
     * @return void
     */
    private function buildOptionData($option, $isAttributeFilterable, $optionsFacetedData, $productSize)
    {
        $value = $this->getOptionValue($option);

        if ($value === false) {
            return;
        }

        $count = $this->getOptionCount($value, $optionsFacetedData);

        if ($isAttributeFilterable && ($count === 0)) {
            return;
        }

        $this->itemDataBuilder->addItemData(
            $this->tagFilter->filter($option['label']),
            $value,
            $count
        );
    }

    /**
     * Retrieve option value if it exists
     *
     * @param array $option
     * @return bool|string
     */
    private function getOptionValue($option)
    {
        if (empty($option['value']) && !is_numeric($option['value'])) {
            return false;
        }
        return $option['value'];
    }

    /**
     * Retrieve count of the options
     *
     * @param int|string $value
     * @param array $optionsFacetedData
     * @return int
     */
    private function getOptionCount($value, $optionsFacetedData)
    {
        return isset($optionsFacetedData[$value]['count'])
            ? (int)$optionsFacetedData[$value]['count']
            : 0;
    }
}
