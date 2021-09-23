<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model\Indexer\Attribute\Action;

use Magento\Framework\App\ResourceConnection;

class DataProvider
{
    protected $_resource;
    protected $_connection;
    protected $_actionFull;
    protected $_separator = ', ';

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection();
    }

    public function getSearchableItems(
        array $staticFields,
        $itemsIds = null,
        $lastItemId = 0,
        $limit = 100
    ){
        $select = $this->_connection->select()
            ->from(
                ['e' => $this->getTable('sales_order_item')],
                ['item_id', 'product_id', 'store_id']
            );

        if (count($staticFields) > 0){
            $select->join(
                ['product' => $this->getTable('catalog_product_entity')],
                'e.product_id = product.entity_id',
                $staticFields
            );
        }

        if ($itemsIds !== null) {
            $select->where('e.item_id IN (?)', $itemsIds);
        }

        $select->where('e.item_id > ?', $lastItemId)->limit($limit)->order('e.item_id');

        $result = $this->_connection->fetchAll($select);

        return $result;
    }

    public function setActionFull(Full $actionFull)
    {
        $this->_actionFull = $actionFull;
    }

    public function getTable($table)
    {
        return $this->_resource->getTableName($table);
    }

    protected function _unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->_connection->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }

    public function getItemAttributes(
        array $productsItems,
        array $attributeTypes
    ){
        $ifStoreValue = $this->_connection->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        $result = [];
        foreach($productsItems as $storeId => $products) {
            $productIds = array_keys($products);

            $selects = [];
            foreach ($attributeTypes as $backendType => $attributeIds) {
                if ($attributeIds) {
                    $tableName = $this->getTable('catalog_product_entity_' . $backendType);
                    $tableFields = $this->_connection->describeTable($this->getTable($tableName));
                    $idField = array_key_exists('row_id', $tableFields) ? 'row_id' : 'entity_id';
                    $selects[] = $this->_connection->select()->from(
                        ['t_default' => $tableName],
                        [$idField, 'attribute_id']
                    )->joinLeft(
                        ['t_store' => $tableName],
                        $this->_connection->quoteInto(
                            't_default.' . $idField . '=t_store.' . $idField .
                            ' AND t_default.attribute_id=t_store.attribute_id' .
                            ' AND t_store.store_id = ?',
                            $storeId
                        ),
                        ['value' => $this->_unifyField($ifStoreValue, $backendType)]
                    )->where(
                        't_default.store_id = ?',
                        0
                    )->where(
                        't_default.attribute_id IN (?)',
                        $attributeIds
                    )->where(
                        't_default.' . $idField . ' IN (?)',
                        $productIds
                    );
                }
            }

            if ($selects) {

                $select = $this->_connection->select()->union($selects, \Magento\Framework\DB\Select::SQL_UNION_ALL);

                $query = $this->_connection->query($select);
                while ($row = $query->fetch()) {
                    if (array_key_exists($row[$idField], $products))
                    {
                        foreach($products[$row[$idField]] as $itemId){
                            $result[$itemId][$row['attribute_id']] = $row['value'];
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function processAttributeValue($attribute, $value)
    {
        $result = false;

        if (in_array($attribute->getFrontendInput(), ['select', 'multiselect'])){
            $result = '';
        } else {
            $result = $value;
        }

        return $result;
    }

    public function prepareItemIndex($indexData, $itemData)
    {
        $storeId = $itemData['store_id'];
        $index = [];

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {

                $value = $this->_getAttributeValue($attributeId, $attributeValue, $storeId);

                if (!empty($value)) {
                    if (isset($index[$attributeId])) {
                        $index[$attributeId][$entityId] = $value;
                    } else {
                        $index[$attributeId] = [$entityId => $value];
                    }
                }
            }
        }

        return $this->_prepareEntityIndex($index, $this->_separator);
    }

    protected function _prepareEntityIndex($index, $separator = ' ')
    {
        $indexData = [];
        foreach ($index as $attributeId => $value) {
            $indexData[$attributeId] = is_array($value) ? implode($separator, $value) : $value;
        }
        return $indexData;
    }

    protected function _getAttributeValue($attributeId, $valueId, $storeId)
    {
        $attribute = $this->_actionFull->getSearchableAttribute($attributeId);

        $value = $this->processAttributeValue($attribute, $valueId);

        if ( $value !== false && $attribute->usesSource()) {
            $attribute->setStoreId($storeId);
            $valueText = (array) $attribute->getSource()->getIndexOptionText($valueId);
            $value = implode($this->_separator, $valueText);
        }

        $value = preg_replace('/\\s+/siu', ' ', trim(strip_tags($value)));

        return $value;
    }
}