<?php

namespace Amasty\Faq\Model\ResourceModel;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\OptionSource\Category\Status;
use Amasty\Faq\Model\OptionSource\Question\Visibility;
use Amasty\Faq\Model\ResourceModel\Tag\CollectionFactory;
use Amasty\Faq\Setup\Operation;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Helper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Question extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Helper
     */
    private $dbHelper;

    /**
     * @var DataObject
     */
    private $associatedQuestionEntityMap;

    /**
     * @var CollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var Tag
     */
    private $tagResource;

    public function __construct(
        Context $context,
        Helper $dbHelper,
        DataObject $associatedQuestionEntityMap,
        CollectionFactory $tagCollectionFactory,
        Tag $tagResource,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->associatedQuestionEntityMap = $associatedQuestionEntityMap;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->tagResource = $tagResource;
        $this->dbHelper = $dbHelper;
    }

    public function _construct()
    {
        $this->_init(Operation\CreateQuestionTable::TABLE_NAME, QuestionInterface::QUESTION_ID);
    }

    /**
     * @param string $entityType
     * @return array
     */
    public function getReferenceConfig($entityType = '')
    {
        return $this->associatedQuestionEntityMap->getData($entityType);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        foreach ($this->getReferenceConfig() as $entityType => $referenceConfig) {
            $select = $this->joinRelationTable($select, $entityType);
        }

        return $select;
    }

    /**
     * @param int $questionId
     *
     * @return \Amasty\Faq\Api\Data\TagInterface[]
     */
    public function getTagsForQuestion($questionId)
    {
        $tagCollection = $this->tagCollectionFactory->create();
        $tagCollection->addFieldToFilter('question_id', $questionId);
        $tagCollection->getSelect()->joinLeft(
            ['question_tag' => $this->getTable(Operation\CreateQuestionTagTable::TABLE_NAME)],
            'question_tag.tag_id = main_table.tag_id',
            []
        );

        return $tagCollection->getItems();
    }

    /**
     * Join relation table
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $entityType
     * @param bool $group
     * @return \Magento\Framework\DB\Select
     */
    protected function joinRelationTable($select, $entityType, $group = true)
    {
        $referenceConfig = $this->getReferenceConfig($entityType);
        $alias = $referenceConfig['table'];
        $fromPart = $select->getPart(\Zend_Db_Select::FROM);
        if (isset($fromPart[$alias])) {
            return $select;
        }
        $questionTable = $this->getTable(Operation\CreateQuestionTable::TABLE_NAME);
        if (!in_array($questionTable, array_keys($select->getPart(\Zend_Db_Select::FROM)))) {
            $questionTable = 'main_table';
        }
        $select->joinLeft(
            [$alias => $this->getTable($alias)],
            $questionTable . '.question_id = ' . $alias . '.question_id',
            []
        );
        if (!$group) {
            return $select;
        }
        $this->dbHelper->addGroupConcatColumn(
            $select,
            $entityType,
            'DISTINCT ' . $alias . '.' . $referenceConfig['column']
        );

        return $select;
    }

    /**
     * @param string $urlKey
     * @param int[]|int|null $storeIds
     *
     * @return \Magento\Framework\DB\Select
     */
    private function getLoadByUrlKeySelect($urlKey, $storeIds = null)
    {
        $select = $this->getConnection()->select()
            ->from(['fq' => $this->getMainTable()])
            ->join(
                ['fqs' => $this->getTable(Operation\CreateQuestionStoreTable::TABLE_NAME)],
                'fq.question_id = fqs.question_id',
                []
            )
            ->where('fq.url_key = ?', $urlKey)
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('fq.question_id')
            ->order('fqs.store_id DESC');

        if ($storeIds != null) {
            if (!is_array($storeIds)) {
                $storeIds = [(int) $storeIds];
            }
            $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $select->where('fqs.store_id IN (?)', $storeIds)->limit(1);
        }

        return $select;
    }

    public function getVisibleQuestionsByUrlKey(string $urlKey, bool $isLoggedIn = false, int $storeId = null)
    {
        $select = $this->getLoadByUrlKeySelect($urlKey, $storeId);
        $select->columns('fqs.store_id');
        $select->where('fq.status = ?', Status::STATUS_ENABLED);

        if ($isLoggedIn) {
            $visibilityCondition = 'fq.visibility != ' . Visibility::VISIBILITY_NONE;
        } else {
            $visibilityCondition = 'fq.visibility = ' . Visibility::VISIBILITY_PUBLIC;
        }

        $select->where($visibilityCondition);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param string $urlKey
     * @param int[]|int $storeIds
     * @param int $questionId
     *
     * @return bool
     */
    public function checkForDuplicateUrlKey($urlKey, $storeIds, $questionId)
    {
        $select = $this->getLoadByUrlKeySelect($urlKey, $storeIds);
        if ($questionId) {
            $select->where('fq.question_id <> ?', $questionId);
        }

        return (bool) $this->getConnection()->fetchOne($select);
    }

    /**
     * @param \Amasty\Faq\Model\Question $object
     *
     * @return bool
     */
    private function isValidUrlKey(\Amasty\Faq\Model\Question $object)
    {
        return (bool) preg_match('/^[a-z0-9_-]+(\.[a-z0-9_-]+)?$/', $object->getUrlKey());
    }

    /**
     * @param \Amasty\Faq\Model\Question $object
     *
     * @return bool
     */
    private function isDuplicateUrlKey(\Amasty\Faq\Model\Question $object)
    {
        return $this->checkForDuplicateUrlKey(
            $object->getUrlKey(),
            (array) $object->getData('store_ids'),
            $object->getQuestionId()
        );
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     * @throws \Amasty\Faq\Exceptions\DuplicateUrlKeyException
     * @throws \Amasty\Faq\Exceptions\InvalidUrlKeyException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!empty($object->getUrlKey())) {
            if ($object->isShowFullAnswer()) {
                $object->setUrlKey('');
            } else {
                if (!$this->isValidUrlKey($object)) {
                    throw new \Amasty\Faq\Exceptions\InvalidUrlKeyException();
                }
                if ($this->isDuplicateUrlKey($object)) {
                    throw new \Amasty\Faq\Exceptions\DuplicateUrlKeyException();
                }
            }
        }
        $object->setTotalRating(
            (int)$object->getPositiveRating() + (int)$object->getNegativeRating()
        );

        return $this;
    }

    /**
     * @param int $questionId
     * @param string $entityType
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getReferenceSelect($questionId, $entityType)
    {
        $connection = $this->getConnection();
        $config = $this->getReferenceConfig();
        $referenceConfig = $config[$entityType];
        $select = $connection->select()
            ->from($this->getTable($referenceConfig['table']), [$referenceConfig['column']])
            ->where('question_id = ?', $questionId);

        return $select;
    }

    /**
     * Perform actions after object save
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $questionId = $object->getId();
        $tagIds = $this->tagResource->saveNoExistTags($object->getTags());
        foreach ($this->getReferenceConfig() as $entityType => $referenceConfig) {
            $table = $this->getTable($referenceConfig['table']);
            $select = $this->getReferenceSelect($questionId, $entityType);
            $oldData = $connection->fetchCol($select);
            $newData = $object->getData($entityType);
            if (is_string($newData)) {
                $newData = explode(',', $newData);
            }
            if ($entityType == 'tag_ids') {
                $newData = $tagIds;
            }
            if (is_array($newData)) {
                $toDelete = array_diff($oldData, $newData);
                $toInsert = array_diff($newData, $oldData);
                $toInsert = array_diff($toInsert, ['']);
            } else {
                $toDelete = $oldData;
                $toInsert = null;
            }

            if (!empty($toDelete)) {
                $deleteSelect = clone $select;
                $deleteSelect->where($referenceConfig['column'] . ' IN (?)', $toDelete);
                $query = $connection->deleteFromSelect($deleteSelect, $table);
                $connection->query($query);
            }
            if (!empty($toInsert)) {
                $insertArray = [];
                foreach ($toInsert as $value) {
                    $insertArray[] = ['question_id' => $questionId, $referenceConfig['column'] => $value];
                }
                $connection->insertMultiple($table, $insertArray);
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param string $entityType
     *
     * @return string
     */
    public function getColumnFromReference($select, $entityType)
    {
        $this->joinRelationTable($select, $entityType, false);
        $referenceConfig = $this->getReferenceConfig($entityType);

        return sprintf('%s.%s', $referenceConfig['table'], $referenceConfig['column']);
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int[]|int $entityIds
     * @param string $entityType
     *
     * @return $this
     */
    public function addRelationFilter(\Magento\Framework\DB\Select $select, $entityIds, $entityType)
    {
        $column = $this->getColumnFromReference($select, $entityType);
        if (is_array($entityIds)) {
            $select->where($column . ' IN (?)', $entityIds);
        } else {
            $select->where($column . ' = ?', $entityIds);
        }

        return $this;
    }

    public function addMultipleRelationFilter(\Magento\Framework\DB\Select $select, $entities)
    {
        $wherePart = [];
        foreach ($entities as $entity) {
            $column = $this->getColumnFromReference($select, $entity['entityType']);
            $wherePart[] = $this->getConnection()->prepareSqlCondition($column, $entity['condition']);
        }

        if (!empty($wherePart)) {
            $select->where(implode(' OR ', $wherePart));
        }

        $select->distinct();

        return $this;
    }

    /**
     * @param int $questionId
     *
     * @return array
     */
    public function getProductIds($questionId = 0)
    {
        $referenceConfig = $this->getReferenceConfig('product_ids');

        $select = $this->getConnection()->select()
            ->from(['product' => $this->getTable($referenceConfig['table'])])
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('product.' . $referenceConfig['column'])
            ->where('product.' . $this->getIdFieldName() . ' = ?', (int) $questionId);

        return $this->getConnection()->fetchCol($select);
    }
}
