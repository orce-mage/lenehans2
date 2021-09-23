<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Model\Indexer\Attribute\Action;

class IndexIterator implements \Iterator
{
    protected $_current;
    protected $_key;
    protected $_valid = true;
    protected $_items = [];
    protected $_fields;
    protected $_staticFields;
    protected $_dataProvider;
    protected $_lastItemId = 0;
    protected $_itemAttributes = [];

    public function __construct(
        DataProvider $dataProvider,
        $itemsIds,
        $staticFields,
        array $fields,
        Full $actionFull
    ) {
        $this->_dataProvider = $dataProvider;
        $this->_dataProvider->setActionFull($actionFull);
        $this->_itemsIds = $itemsIds;
        $this->_fields = $fields;
        $this->_staticFields = $staticFields;
    }

    public function current()
    {
        return $this->_current;
    }

    public function next()
    {
        \next($this->_items);
        if (\key($this->_items) === null) {

            $this->_items = $this->_dataProvider->getSearchableItems(
                $this->_staticFields,
                $this->_itemsIds,
                $this->_lastItemId
            );

            if (!count($this->_items)) {
                $this->_valid = false;
                return;
            }

            $productsItems = [];

            foreach ($this->_items as $itemData) {
                $this->_lastItemId = $itemData['item_id'];

                if (!array_key_exists($itemData['store_id'], $productsItems)){
                    $productsItems[$itemData['store_id']] = [];
                }

                if (!array_key_exists($itemData['product_id'], $productsItems[$itemData['store_id']])){
                    $productsItems[$itemData['store_id']][$itemData['product_id']] = [];
                }

                $productsItems[$itemData['store_id']][$itemData['product_id']][] = $itemData['item_id'];
            }

            \reset($this->_items);

            $this->_itemAttributes = $this->_dataProvider->getItemAttributes(
                $productsItems,
                $this->_fields
            );
        }

        $itemData = \current($this->_items);

        foreach($this->_staticFields as $attributeId => $attributeCode){
            if (array_key_exists($attributeCode, $itemData)){
                if (!array_key_exists($itemData['item_id'], $this->_itemAttributes)){
                    $this->_itemAttributes[$itemData['item_id']] = [];
                }

                $this->_itemAttributes[$itemData['item_id']][$attributeId] = $itemData[$attributeCode];
            }
        }

        if (!isset($this->_itemAttributes[$itemData['item_id']])) {
            $this->next();
            return;
        }

        $itemAttr = $this->_itemAttributes[$itemData['item_id']];

        $itemIndex = [$itemData['item_id'] => $itemAttr];

        $index = $this->_dataProvider->prepareItemIndex(
            $itemIndex,
            $itemData
        );

        $this->_current = $index;
        $this->_key = $itemData['item_id'];
    }

    public function key()
    {
        return $this->_key;
    }

    public function valid()
    {
        return $this->_valid;
    }

    public function rewind()
    {
        $this->_lastItemId = 0;
        $this->_key = null;
        $this->_current = null;
        unset($this->_items);
        $this->_items = [];
        $this->next();
    }
}
