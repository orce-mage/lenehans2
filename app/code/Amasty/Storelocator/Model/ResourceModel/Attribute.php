<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\AbstractModel;

class Attribute extends AbstractDb
{
    const TABLE_NAME = 'amasty_amlocator_attribute';

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * Attribute constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Amasty\Base\Model\Serializer                     $serializer
     * @param null                                              $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Base\Model\Serializer $serializer,
        $connectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
        $this->serializer = $serializer;
    }

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'attribute_id');
    }

    public function saveOptions($data, $attributeId)
    {
        $default = [];
        if (isset($data['default'])) {
            $default = $data['default'];
        }
        $deleteIds = [];
        $insertData = [];
        $i = 1;
        if (isset($data['option']) && isset($data['option']['value'])) {
            $options = $data['option'];
            foreach ($options['value'] as $id => $value) {
                $entityId = strpos($id, 'option') !== false ? null : $id;
                if ($options['delete'][$id] !== '1') {
                    $insertData[] = [
                        'value_id' => $entityId,
                        'attribute_id' => $attributeId,
                        'is_default' => in_array($id, $default),
                        'options_serialized' => $this->serializer->serialize($value),
                        'sort_order' => $i++
                    ];
                } else {
                    $deleteIds[] = $entityId;
                }
            }
        }

        $table = $this->getTable(Options::TABLE_NAME);

        if (count($insertData) > 0) {
            $this->getConnection()->insertOnDuplicate($table, $insertData);
        }

        if (count($deleteIds) > 0) {
            $where = ['attribute_id = ?' => (int)$attributeId, 'value_id IN (?)' => $deleteIds];
            $this->getConnection()->delete($table, $where);
        }
    }

    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $this->loadOptions($object);
        }

        return parent::_afterLoad($object);
    }

    public function loadOptions(AbstractModel $object)
    {
        $options = $this->lookupOptionsLabel($object->getId());
        $object->setData('options', $options);
        return $object;
    }

    public function lookupOptionsLabel($attributeId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable(Options::TABLE_NAME)
        )->where(
            'attribute_id = ?',
            (int)$attributeId
        )->order("sort_order");

        $options = $connection->fetchAll($select);

        return $options;
    }
}
