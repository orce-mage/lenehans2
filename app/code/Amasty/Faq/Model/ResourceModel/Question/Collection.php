<?php

namespace Amasty\Faq\Model\ResourceModel\Question;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Model\Config\QuestionsSort;
use Amasty\Faq\Model\ConfigProvider;
use Amasty\Faq\Model\OptionSource\Question\Status;
use Amasty\Faq\Model\OptionSource\Question\Visibility;
use Amasty\Faq\Model\ResourceModel\Traits\CollectionTrait;
use Amasty\Faq\Setup\Operation\CreateQuestionTagTable;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;

/**
 * @method \Amasty\Faq\Model\Question[] getItems()
 * @method \Amasty\Faq\Model\ResourceModel\Question getResource()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    use CollectionTrait;

    /**
     * Limit to show autosuggest search
     */
    const AUTOSUGGEST_LIMIT = 10;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Fulltext
     */
    private $fulltext;

    /**
     * @var string
     */
    private $queryText;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        Fulltext $fulltext,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        ConfigProvider $configProvider,
        ProductRepositoryInterface $productRepository,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->configProvider = $configProvider;
        $this->fulltext = $fulltext;
        $this->productRepository = $productRepository;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Faq\Model\Question::class, \Amasty\Faq\Model\ResourceModel\Question::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    const CACHE_TAG = 'amfaq_questions';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    /**
     * @param int[]|int $entityIds
     * @param string $entityType
     *
     * @return $this
     */
    private function addFilterForQuestions($entityIds, $entityType)
    {
        $this->getResource()->addRelationFilter($this->getSelect(), $entityIds, $entityType);

        return $this;
    }

    /**
     * @param int[]|int $productIds
     *
     * @return $this
     */
    public function addProductFilter($productIds)
    {
        $categoryIds = [];
        $preparedProductIds = is_array($productIds) ? $productIds : [$productIds];

        foreach ($preparedProductIds as $productId) {
            try {
                $product = $this->productRepository->getById((int)$productId);

                foreach ($product->getCategoryIds() as $categoryId) {
                    $categoryIds[] = $categoryId;
                }
            } catch (NoSuchEntityException $e) {
                ;//If product was deleted - no actions required
            }
        }

        $this->getResource()->addMultipleRelationFilter(
            $this->getSelect(),
            [
                [
                    'entityType' => 'product_ids',
                    'condition'  => ['in' => $productIds]
                ],
                [
                    'entityType' => 'product_category_ids',
                    'condition'  => ['in' => $categoryIds]
                ]
            ]
        );

        return $this;
    }

    /**
     * @param int|null $customerGroup
     *
     * @return $this
     */
    public function addFrontendCustomerIdFilter($customerGroup = null)
    {
        $referenceConfigIdentifier = 'customer_groups';
        if ($customerGroup !== null) {
            $this->getResource()->addMultipleRelationFilter(
                $this->getSelect(),
                [
                    [
                        'entityType' => $referenceConfigIdentifier,
                        'condition'  => [$referenceConfigIdentifier => (int)$customerGroup]
                    ],
                    [
                        'entityType' => $referenceConfigIdentifier,
                        'condition'  => ['null' => null]
                    ]
                ]
            );
        }

        return $this;
    }

    /**
     * @param int[]|int $categoryIds
     *
     * @return $this
     */
    public function addCategoryFilter($categoryIds)
    {
        $this->addFilterForQuestions($categoryIds, 'category_ids');

        return $this;
    }

    /**
     * @param int[]|int $storeIds
     *
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->addFilterForQuestions($storeIds, 'store_ids');

        return $this;
    }

    /**
     * @param string $value
     *
     * @return array|bool
     */
    public function loadByQueryText($value)
    {
        if (empty($value) || !(preg_match_all('/(\w{2,})/isu', $value, $words))) {
            return false;
        }

        $words[1] = $words[1] ?? [];
        $this->getSelect()->where(
            new \Zend_Db_Expr('main_table.' . QuestionInterface::TITLE . ' REGEXP "' . implode('|', $words[1]) . '"')
        );

        return $words[1];
    }

    /**
     * @param $query
     * @return $this
     */
    public function getAutosuggestCollection($query)
    {
        $this->loadByQueryText($query);
        $this->getSelect()->joinLeft(
            ['cq' => $this->getTable(\Amasty\Faq\Setup\Operation\CreateQuestionCategoryTable::TABLE_NAME)],
            'main_table.question_id = cq.question_id',
            null
        );
        $this->getSelect()->joinLeft(
            ['category' => $this->getTable(\Amasty\Faq\Setup\Operation\CreateCategoryTable::TABLE_NAME)],
            'cq.category_id = category.category_id',
            ['category' => 'category.title']
        );
        $this->getSelect()->limit(self::AUTOSUGGEST_LIMIT);
        $this->getSelect()->group('main_table.question_id');

        return $this;
    }

    /**
     * @param bool $isLoggedIn
     * @param null|int $storeId
     * @param null|string $sort
     * @param null|int $customerGroupId
     *
     * @return $this
     */
    public function addFrontendFilters($isLoggedIn = false, $storeId = null, $sort = null, $customerGroupId = null)
    {
        $this->getSelect()->distinct();
        $this->addVisibilityFilters($isLoggedIn, $customerGroupId);
        $this->addSortFilter($sort);
        $this->addFrontendStoreFilter($storeId);

        return $this;
    }

    /**
     * @param bool $isLoggedIn
     * @param null|int $customerGroup
     *
     * @return $this
     */
    public function addVisibilityFilters($isLoggedIn = false, $customerGroup = null)
    {
        $this->addFieldToFilter('main_table.status', Status::STATUS_ANSWERED);
        if ($isLoggedIn) {
            $this->addFieldToFilter('visibility', ['neq' => Visibility::VISIBILITY_NONE]);
        } else {
            $this->addFieldToFilter('visibility', Visibility::VISIBILITY_PUBLIC);
        }
        $this->addFrontendCustomerIdFilter($customerGroup);

        return $this;
    }

    /**
     * @param string $sort
     *
     * @return $this
     */
    public function addSortFilter($sort = null)
    {
        if ($sort === null) {
            $sort = $this->configProvider->getQuestionsSort();
        }
        switch ($sort) {
            case QuestionsSort::MOST_VIEWED:
                $this->setOrder('visit_count', 'DESC');
                break;
            case QuestionsSort::SORT_BY_NAME:
                $this->setOrder('title', 'ASC');
                break;
            case QuestionsSort::SORT_BY_POSITION:
            default:
                $this->setOrder('position', 'ASC');
                break;
        }

        return $this;
    }

    /**
     * @param null $storeId
     * @return $this
     */
    public function addFrontendStoreFilter($storeId = null)
    {
        $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        if ($storeId) {
            $storeIds[] = (int) $storeId;
        }
        $this->addStoreFilter($storeIds);

        return $this;
    }

    /**
     * @param int $tagTitle
     */
    public function getQuestionsByQueryTag($tagTitle)
    {
        $this->getSelect()->joinLeft(
            ['question_tag' => $this->getTable(\Amasty\Faq\Setup\Operation\CreateQuestionTagTable::TABLE_NAME)],
            'main_table.question_id = question_tag.question_id',
            []
        )->joinLeft(
            ['tag' => $this->getTable(\Amasty\Faq\Setup\Operation\CreateTagTable::TABLE_NAME)],
            'question_tag.tag_id = tag.tag_id',
            []
        )->where('tag.title = ?', $tagTitle);
    }

    /**
     * @param array $tagIds
     */
    public function addTagIdsFilter($tagIds)
    {
        $this->getSelect()
            ->join(
                ['tags' => $this->getTable(CreateQuestionTagTable::TABLE_NAME)],
                'main_table.question_id = tags.question_id',
                []
            )->where('tags.tag_id IN (?)', $tagIds);
    }

    /**
     * @param array $ids
     *
     * @return $this
     */
    public function orderBySpecifiedIds(array $ids)
    {
        $ids = array_filter(
            array_map(function ($val) {
                return (int)$val;
            }, $ids)
        );

        if (!empty($ids)) {
            $idFieldName = $this->getIdFieldName();
            $this->getSelect()->order(new \Zend_Db_Expr("FIELD(main_table.{$idFieldName},"
                . implode(",", $ids) . ')'));
        }

        return $this;
    }
}
