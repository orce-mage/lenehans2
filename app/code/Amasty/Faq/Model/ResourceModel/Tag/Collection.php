<?php

namespace Amasty\Faq\Model\ResourceModel\Tag;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\Data\TagInterface;
use Amasty\Faq\Model\OptionSource\Category\Status;
use Amasty\Faq\Model\OptionSource\Question\Visibility;
use Amasty\Faq\Setup\Operation;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

/**
 * @method \Amasty\Faq\Model\Tag[] getItems()
 */
class Collection extends AbstractCollection
{
    const CACHE_TAG = 'amfaq_tags';
    const QUESTION_ALIAS = 'question_entity';
    const QUESTION_TAG_ALIAS = 'question_tag';
    const QUESTION_STORE_ALIAS = 'question_store';
    const QUESTION_STORE_ALL_ALIAS = 'question_store_all';

    private $visibilityFilterApplied = false;
    private $questionTagRelationTableJoined = false;
    private $questionTableJoined = false;
    private $questionStoreTableJoined = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Faq\Model\Tag::class, \Amasty\Faq\Model\ResourceModel\Tag::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param int $limit
     * @param int|null $storeId
     *
     * @return \Amasty\Faq\Model\Tag[]
     */
    public function getTagsSortedByCount($limit = 0, $storeId = null)
    {
        $this->joinQuestionTagRelationTable();
        $this->getSelect()
            ->group('main_table.' . TagInterface::TAG_ID)
            ->order('count DESC');

        if ($limit) {
            $this->getSelect()->limit($limit);
        }

        if ($storeId) {
            $this->addStoreFilter((int)$storeId);
        }

        return $this->getItems();
    }

    public function addStoreFilter(int $storeId)
    {
        $this->joinQuestionStoreTable($storeId);
        $this->getSelect()->where(
            new \Zend_Db_Expr(
                sprintf(
                    'COALESCE(%s, %s) IN (%s)',
                    self::QUESTION_STORE_ALIAS . '.store_id',
                    self::QUESTION_STORE_ALL_ALIAS . '.store_id',
                    implode(',', [Store::DEFAULT_STORE_ID, $storeId])
                )
            )
        );
    }

    /**
     * Add visibility and status filters
     */
    public function addVisibilityFilter(bool $isLoggedIn = false)
    {
        if (!$this->visibilityFilterApplied) {
            $this->visibilityFilterApplied = true;
            $this->joinQuestionsTable();

            if ($isLoggedIn) {
                $filterCondition = ['neq' => Visibility::VISIBILITY_NONE];
            } else {
                $filterCondition = Visibility::VISIBILITY_PUBLIC;
            }

            $this->addFieldToFilter(self::QUESTION_ALIAS . '.' . QuestionInterface::VISIBILITY, $filterCondition);
            $this->addFieldToFilter(self::QUESTION_ALIAS . '.' . QuestionInterface::STATUS, Status::STATUS_ENABLED);
        }

        return $this;
    }

    public function joinQuestionStoreTable(int $storeId)
    {
        if (!$this->questionStoreTableJoined) {
            $this->questionStoreTableJoined = true;
            $this->joinQuestionsTable();

            $this->getSelect()->joinLeft(
                [self::QUESTION_STORE_ALL_ALIAS => $this->getTable(Operation\CreateQuestionStoreTable::TABLE_NAME)],
                self::QUESTION_ALIAS . '.' . QuestionInterface::QUESTION_ID . ' = '
                . self::QUESTION_STORE_ALL_ALIAS . '.' . QuestionInterface::QUESTION_ID
                . ' AND ' . self::QUESTION_STORE_ALL_ALIAS . '.store_id = ' . Store::DEFAULT_STORE_ID,
                []
            );
            $this->getSelect()->joinLeft(
                [self::QUESTION_STORE_ALIAS => $this->getTable(Operation\CreateQuestionStoreTable::TABLE_NAME)],
                self::QUESTION_ALIAS . '.' . QuestionInterface::QUESTION_ID . ' = '
                . self::QUESTION_STORE_ALIAS . '.' . QuestionInterface::QUESTION_ID
                . ' AND ' . self::QUESTION_STORE_ALIAS . '.store_id = ' . $storeId,
                []
            );
        }

        return $this;
    }

    public function joinQuestionsTable()
    {
        if (!$this->questionTagRelationTableJoined) {
            $this->questionTagRelationTableJoined = true;
            $this->joinQuestionTagRelationTable();

            $this->getSelect()->joinLeft(
                [self::QUESTION_ALIAS => $this->getTable(Operation\CreateQuestionTable::TABLE_NAME)],
                self::QUESTION_TAG_ALIAS . '.' . QuestionInterface::QUESTION_ID . ' = '
                . self::QUESTION_ALIAS . '.' . QuestionInterface::QUESTION_ID,
                []
            );
        }

        return $this;
    }

    private function joinQuestionTagRelationTable()
    {
        if (!$this->questionTableJoined) {
            $this->questionTableJoined = true;
            $this->getSelect()->joinLeft(
                [self::QUESTION_TAG_ALIAS => $this->getTable(Operation\CreateQuestionTagTable::TABLE_NAME)],
                self::QUESTION_TAG_ALIAS . '.' . TagInterface::TAG_ID . ' = main_table.' . TagInterface::TAG_ID,
                ['count' => 'COUNT(' . self::QUESTION_TAG_ALIAS . '.' . TagInterface::TAG_ID . ')']
            );
        }

        return $this;
    }
}
