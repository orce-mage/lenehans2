<?php
namespace MageBig\Shopbybrand\Model\ResourceModel;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;

class Attribute extends \Magento\Eav\Model\ResourceModel\Entity\Attribute
{
    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var LockValidatorInterface
     */
    protected $attrLockValidator;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Entity\Type $eavEntityType,
        \Magento\Eav\Model\Config $eavConfig,
        $connectionName = null
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $storeManager, $eavEntityType, $connectionName);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_clearUselessAttributeValues($object);
        return parent::_afterSave($object);
    }

    protected function _clearUselessAttributeValues(\Magento\Framework\Model\AbstractModel $object)
    {
        $origData = $object->getOrigData();

        if ($object->isScopeGlobal() && isset(
            $origData['is_global']
        ) && \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL != $origData['is_global']
        ) {
            $attributeStoreIds = array_keys($this->_storeManager->getStores());
            if (!empty($attributeStoreIds)) {
                $delCondition = [
                    'attribute_id = ?' => $object->getId(),
                    'store_id IN(?)' => $attributeStoreIds,
                ];
                $this->getConnection()->delete($object->getBackendTable(), $delCondition);
            }
        }

        return $this;
    }

    /**
    * Delete entity
    *
    * @param \Magento\Framework\Model\AbstractModel $object
    * @return $this
    * @throws \Magento\Framework\Exception\LocalizedException
    */
    public function deleteEntity(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $select = $this->getConnection()->select()->from(
            $this->getTable('eav_entity_attribute')
        )->where(
            'entity_attribute_id = ?',
            (int)$object->getEntityAttributeId()
        );
        $result = $this->getConnection()->fetchRow($select);

        if ($result) {
            $attribute = $this->_eavConfig->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $result['attribute_id']
            );

            try {
                $this->attrLockValidator->validate($attribute, $result['attribute_set_id']);
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attribute \'%1\' is locked. %2', $attribute->getAttributeCode(), $exception->getMessage())
                );
            }

            $backendTable = $attribute->getBackend()->getTable();
            if ($backendTable) {
                $select = $this->getConnection()->select()->from(
                    $attribute->getEntity()->getEntityTable(),
                    'entity_id'
                )->where(
                    'attribute_set_id = ?',
                    $result['attribute_set_id']
                );

                $clearCondition = [
                    'attribute_id =?' => $attribute->getId(),
                    'entity_id IN (?)' => $select,
                ];
                $this->getConnection()->delete($backendTable, $clearCondition);
            }
        }

        $condition = ['entity_attribute_id = ?' => $object->getEntityAttributeId()];
        $this->getConnection()->delete($this->getTable('eav_entity_attribute'), $condition);

        return $this;
    }
}
