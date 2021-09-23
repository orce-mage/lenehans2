<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model\Indexer\Attribute\Action;

class Full
{
    protected $_searchableAttributes;
    protected $_productAttributeCollectionFactory;
    protected $_iteratorFactory;
    protected $_eavConfig;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory,
        \Amasty\Orderexport\Model\Indexer\Attribute\Action\IndexIteratorFactory $indexIteratorFactory,
        \Magento\Eav\Model\Config $eavConfig
    ){
        $this->_productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->_iteratorFactory = $indexIteratorFactory;
        $this->_eavConfig = $eavConfig;
    }

    public function getSearchableAttribute($attribute)
    {
        $attributes = $this->getSearchableAttributes();
        if (is_numeric($attribute)) {
            if (isset($attributes[$attribute])) {
                return $attributes[$attribute];
            }
        } elseif (is_string($attribute)) {
            foreach ($attributes as $attributeModel) {
                if ($attributeModel->getAttributeCode() == $attribute) {
                    return $attributeModel;
                }
            }
        }

        return $this->getEavConfig()->getAttribute(\Magento\Catalog\Model\Category::ENTITY, $attribute);
    }

    public function getFilteredSearchableAttributes(array $attributesHash, $backendType)
    {
        $attributes = [];
        foreach ($this->getSearchableAttributes() as $attributeId => $attribute) {
            if (in_array($attribute->getAttributeCode(), $attributesHash) &&
                $attribute->getBackendType() == $backendType) {
                $attributes[$attributeId] = $attribute;
            }
        }

        return $attributes;
    }

    public function getSearchableAttributes()
    {
        if (null === $this->_searchableAttributes) {
            $this->_searchableAttributes = [];

            $attributesCollection = $this->_productAttributeCollectionFactory->create();
            $attributesCollection->join(
                    ['orderexport_attribute' => $attributesCollection->getTable('amasty_amorderexport_attribute')],
                    'orderexport_attribute.attribute_id = main_table.attribute_id',
                    'orderexport_attribute.entity_id as orderexport_attribute_entity_id'
                );

            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $attributesCollection->getItems();

            $entity = $this->getEavConfig()->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->_searchableAttributes = $attributes;
        }

        return $this->_searchableAttributes;
    }

    protected function getEavConfig()
    {
        return $this->_eavConfig;
    }
    
    public function rebuildIndex(array $attributesHash, $itemsIds = null)
    {
        $staticFields = [];
        foreach ($this->getFilteredSearchableAttributes($attributesHash, 'static') as $attribute) {
            $staticFields[$attribute->getId()] = $attribute->getAttributeCode();
        }

        return $this->_iteratorFactory->create([
            'itemsIds' => $itemsIds,
            'staticFields' => $staticFields,
            'fields' => [
                'int' => array_keys($this->getFilteredSearchableAttributes($attributesHash, 'int')),
                'varchar' => array_keys($this->getFilteredSearchableAttributes($attributesHash, 'varchar')),
                'text' => array_keys($this->getFilteredSearchableAttributes($attributesHash, 'text')),
                'decimal' => array_keys($this->getFilteredSearchableAttributes($attributesHash, 'decimal')),
                'datetime' => array_keys($this->getFilteredSearchableAttributes($attributesHash, 'datetime')),
            ]
        ]);
    }
}