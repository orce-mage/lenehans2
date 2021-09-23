<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Model\ResourceModel;

class BrandEntity extends AbstractResource
{
    protected $_storeId;

    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageBig\Shopbybrand\Model\Factory $modelFactory,
        $data = []
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $modelFactory,
            $data
        );
        $this->connectionName  = 'product_brand';
    }

    /**
     * Re-declare attribute model
     *
     * @return string
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            $this->setType('magebig_product_brand_entity');
        }
        return parent::getEntityType();
    }
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }
    protected function _beforeDelete(\Magento\Framework\DataObject $object)
    {
        parent::_beforeDelete($object);
    }

    public function verifyIds(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'entity_id'
        )->where(
            'entity_id IN(?)',
            $ids
        );

        return $this->getConnection()->fetchCol($select);
    }
    public function getIsActiveAttributeId()
    {
        if ($this->_isActiveAttributeId === null) {
            $this->_isActiveAttributeId = (int)$this->_eavConfig
                ->getAttribute($this->getEntityType(), 'is_active')
                ->getAttributeId();
        }
        return $this->_isActiveAttributeId;
    }

    public function findWhereAttributeIs($entityIdsFilter, $attribute, $expectedValue)
    {
        $bind = ['attribute_id' => $attribute->getId(), 'value' => $expectedValue];
        $select = $this->getConnection()->select()->from(
            $attribute->getBackend()->getTable(),
            ['entity_id']
        )->where(
            'attribute_id = :attribute_id'
        )->where(
            'value = :value'
        )->where(
            'entity_id IN(?)',
            $entityIdsFilter
        );

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * Retrieve default entity attributes
     *
     * @return string[]
     */
    protected function _getDefaultAttributes()
    {
        return ['is_active','option_id','identifier', 'attribute_id'];
    }

    public function load($object, $entityId, $attributes = [])
    {

        //$this->_attributes = [];

        \Magento\Framework\Profiler::start('EAV:load_entity');
        /**
         * Load object base row data
         */

        $select = $this->_getLoadRowSelect($object, $entityId, $attributes);

        $mainTable = $this->getTable('magebig_product_brand_entity');
        $optionValueTable = $this->getTable('eav_attribute_option_value');

        $storeId = $object->getStoreId() ? $object->getStoreId() : \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $optionId = $object->getOptionId();
        if ($optionId) {
            $check = $this->getConnection()->fetchRow(
                'SELECT value FROM ' . $optionValueTable .
                ' WHERE option_id = ' . $object->getOptionId() . ' AND store_id = ' . $storeId
            );
            if (!$check) {
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }
        }

        if ((!$entityId) && $optionId) {
            $select = $this->getConnection()->select();
            $select->from($mainTable);
            $select->where($mainTable . '.option_id = ' . $object->getOptionId());

            if (!is_array($this->getConnection()->fetchRow($select))) {
                $select = $this->getConnection()->select();
                $select->from($optionValueTable, ['option_id', 'store_id', 'brand_label' => 'value']);
                $select->where($optionValueTable . '.option_id = ' . $object->getOptionId());
                $select->where($optionValueTable . '.store_id = ' . $storeId);
            } else {
                $select->joinLeft(
                    ['eaov' => $optionValueTable],
                    'eaov.option_id = ' . $mainTable . '.option_id',
                    ['brand_label' => 'value']
                )
                    ->where('eaov.store_id = ' . $storeId);
            }
        } else {
            $select->joinLeft(
                ['eaov' => $optionValueTable],
                'eaov.option_id = ' . $mainTable . '.option_id',
                ['brand_label' => 'value']
            )
                ->where('eaov.store_id = ' . $storeId);
        }
        $row = $this->getConnection()->fetchRow($select);
        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        $this->loadAttributesMetadata($attributes);

        $this->_loadModelAttributes($object);

        $object->setOrigData();

        $this->_afterLoad($object);

        \Magento\Framework\Profiler::stop('EAV:load_entity');
        return $this;
    }
}
