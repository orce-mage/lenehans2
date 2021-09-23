<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use \Magento\Catalog\Model\ResourceModel\ConfigFactory;
use \Magento\Eav\Model\Config as EavConfig;

class IndexStructure implements IndexStructureInterface
{
    protected $_resource;
    protected $_indexScopeResolver;
    protected $_entityTypeId;
    protected $_configFactory;
    protected $_eavConfig;
    protected $_attributes;
    protected $_columns;
    protected $_indexes;
    protected $_logger;

    protected $_staticColumns = [
        'entity_id', 'order_item_id'
    ];

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ResourceConnection $resource,
        IndexScopeResolver $indexScopeResolver,
        ConfigFactory $configFactory,
        EavConfig $eavConfig
    ) {
        $this->_resource = $resource;
        $this->_indexScopeResolver = $indexScopeResolver;
        $this->_eavConfig = $eavConfig;
        $this->_logger = $context->getLogger();
    }

    public function delete($index, array $dimensions = [])
    {
        $tableName = $this->_indexScopeResolver->resolve($index, $dimensions);
        if ($this->_resource->getConnection()->isTableExists($tableName)) {
            $this->_resource->getConnection()->dropTable($tableName);
        }
    }

    public function getEntityTypeId()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = $this->_configFactory->create()->getEntityTypeId();
        }
        return $this->_entityTypeId;
    }

    public function getEntityType()
    {
        return \Magento\Catalog\Model\Product::ENTITY;
    }

    public function getAttributes(array $attributeCodes)
    {
        if ($this->_attributes === null) {
            $this->_attributes = [];

            $entity = $this->_eavConfig->getEntityType($this->getEntityType())->getEntity();

            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->_eavConfig->getAttribute(
                    $this->getEntityType(),
                    $attributeCode
                )->setEntity(
                    $entity
                );

                if ($attribute->getId()){
                    try {
                        // check if exists source and backend model.
                        // To prevent exception when some module was disabled
                        $attribute->usesSource() && $attribute->getSource();
                        $attribute->getBackend();

                        $attribute
                            ->setFlatAddFilterableAttributes(true)
                            ->setIsFilterable(true);

                        if (in_array($attribute->getFrontendInput(), ['select', 'multiselect', 'boolean'])){
                            $attribute->setFrontendInput('text');
                            $attribute->setBackendType('varchar');

                        }

                        if ($attribute->getData('source_model') != ''){
                            $attribute->setData('source_model', '');
                        }

                        $this->_attributes[$attributeCode] = $attribute;
                    } catch (\Exception $e) {
                        $this->_logger->critical($e);
                    }
                }
            }
        }
        return $this->_attributes;
    }


    public function getAttributesFlatColumns(array $attributeCodes)
    {
        if ($this->_columns === null) {
            $this->_columns = [];
            foreach ($this->getAttributes($attributeCodes) as $attribute) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $columns = $attribute->getFlatColumns();

                if ($columns !== null) {
                    $this->_columns = array_merge($this->_columns, $columns);
                }
            }
        }

        return $this->_columns;
    }

    public function getAttributesFlatIndexes(array $attributeCodes)
    {
        if ($this->_indexes === null){
            $this->_indexes = [];

            foreach ($this->getAttributes($attributeCodes) as $attribute) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $indexes = $attribute
                    ->getFlatIndexes();

                if ($indexes !== null) {
                    $this->_indexes = array_merge($this->_indexes, $indexes);
                }
            }
        }

        return $this->_indexes;
    }

    public function create($index, array $fields, array $dimensions = [])
    {
        $tableName = $this->_indexScopeResolver->resolve($index, $dimensions);

        $attributesFlatColumns = $this->getAttributesFlatColumns($fields);
        $attributesFlatIndexes = $this->getAttributesFlatIndexes($fields);

        $columns = $this->_resource->getConnection()->describeTable($tableName);

        foreach($columns as $columnCode => $columnSchema){
            if (!in_array($columnCode, array_merge($this->_staticColumns, $fields))){
                $this->_resource->getConnection()->dropColumn($tableName, $columnCode);
            }
        }

        foreach ($attributesFlatColumns as $fieldName => $fieldProp) {
            $columnDefinition = [
                'type' => $fieldProp['type'],
                'length' => isset($fieldProp['length']) ? $fieldProp['length'] : null,
                'nullable' => isset($fieldProp['nullable']) ? (bool)$fieldProp['nullable'] : false,
                'unsigned' => isset($fieldProp['unsigned']) ? (bool)$fieldProp['unsigned'] : false,
                'default' => isset($fieldProp['default']) ? $fieldProp['default'] : false,
                'primary' => false,
                'comment' => isset($fieldProp['comment']) ? $fieldProp['comment'] : $fieldName
            ];

            $this->_resource->getConnection()
                ->addColumn($tableName, $fieldName, $columnDefinition);
        }

        foreach ($attributesFlatIndexes as $indexProp) {
            $indexName = $this->_resource->getConnection()->getIndexName(
                $tableName,
                $indexProp['fields'],
                $indexProp['type']
            );

            $this->_resource->getConnection()->addIndex(
                $tableName,
                $indexName,
                $indexProp['fields'],
                strtolower($indexProp['type'])
            );
        }
    }

    public function getNoneIndexedAttributes($index, array $attributesHash, array $dimensions = [])
    {
        $tableName = $this->_indexScopeResolver->resolve($index, $dimensions);

        $columns = $this->_resource->getConnection()->describeTable($tableName);

        foreach($attributesHash as $attributeId => $attributeCode){
            if (array_key_exists($attributeCode, $columns)){
                unset($attributesHash[$attributeId]);
            }
        }

        return $attributesHash;
    }

    public function getIndexedAttributes($index, array $attributesHash, array $dimensions = [])
    {
        $tableName = $this->_indexScopeResolver->resolve($index, $dimensions);

        $columns = $this->_resource->getConnection()->describeTable($tableName);

        foreach($attributesHash as $attributeId => $attributeCode){
            if (!array_key_exists($attributeCode, $columns)){
                unset($attributesHash[$attributeId]);
            }
        }

        return $attributesHash;
    }
}
