<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Indexer\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Api\RuleRepositoryInterface;
use Amasty\Stockstatus\Model\Indexer\Rule\Resources\TableWorker;
use Amasty\Stockstatus\Model\ResourceModel\RuleIndex;
use Amasty\Stockstatus\Model\Rule;
use Amasty\Stockstatus\Model\Rule\Condition as RuleCondition;
use Amasty\Stockstatus\Model\Rule\ConditionFactory as RuleConditionFactory;
use Amasty\Stockstatus\Model\Source\Status;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\ActionInterface as IndexerInterface;
use Magento\Framework\Mview\ActionInterface as MviewInterface;

abstract class AbstractIndexer implements IndexerInterface, MviewInterface
{
    /**
     * @var RuleConditionFactory
     */
    private $ruleConditionFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * @var int
     */
    private $batchCacheCount;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var TableWorker
     */
    private $tableWorker;

    public function __construct(
        TableWorker $tableWorker,
        CacheContext $cacheContext,
        RuleConditionFactory $ruleConditionFactory,
        RuleRepositoryInterface $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ManagerInterface $eventManager,
        int $batchCount = 1000,
        int $batchCacheCount = 100
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->ruleConditionFactory = $ruleConditionFactory;
        $this->batchCount = $batchCount;
        $this->batchCacheCount = $batchCacheCount;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->tableWorker = $tableWorker;
    }

    abstract protected function getType(): string;

    /**
     * @param array|null $ids
     * @throws Exception
     */
    protected function doReindex(?array $ids = null): void
    {
        $rows = [];
        $count = 0;

        $ruleIds = $this->getType() === RuleIndexer::TYPE ? $ids : null;
        $productIds = $this->getType() === ProductIndexer::TYPE ? $ids : null;

        /** @var RuleCondition $ruleCondition */
        $ruleCondition = $this->ruleConditionFactory->create();

        foreach ($this->getRules($ruleIds) as $rule) {
            if ($rule->getStores() && $rule->getConditionsSerialized()) {
                $ruleCondition->clearResult();

                if ($productIds !== null) {
                    $ruleCondition->setProductsFilter($productIds);
                }
                $ruleCondition->setStores($rule->getStores());
                $ruleCondition->setConditionsSerialized($rule->getConditionsSerialized());

                $matchedProducts = $ruleCondition->getMatchingProductIdsForRule();

                foreach ($matchedProducts as $productId => $storeIds) {
                    while ($storeIds) {
                        $rows[] = [
                            RuleIndex::PRODUCT_ID => $productId,
                            RuleIndex::STORE_ID => array_shift($storeIds),
                            RuleIndex::RULE_ID => $rule->getId()
                        ];
                        if (++$count > $this->batchCount) {
                            $this->tableWorker->insert($rows);
                            $count = 0;
                            $rows = [];
                        }
                    }
                    $this->registerEntities(Product::CACHE_TAG, [$productId]);
                }
                $this->registerEntities(Rule::CACHE_TAG, [$rule->getId()]);
            }
        }

        $this->tableWorker->insert($rows);
        $this->cleanCache();
    }

    /**
     * @param array|null $ids
     * @return RuleInterface[]
     */
    protected function getRules(?array $ids = null): array
    {
        $filters = [];

        if ($ids !== null) {
            $filters[] = $this->filterBuilder->setField(RuleInterface::ID)
                ->setValue($ids)
                ->setConditionType('in')
                ->create();
        }
        $filters[] = $this->filterBuilder->setField(RuleInterface::STATUS)
            ->setValue(Status::ACTIVE)
            ->setConditionType('eq')
            ->create();

        $this->searchCriteriaBuilder->addFilters($filters);

        try {
            $rules = $this->ruleRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        } catch (NoSuchEntityException $e) {
            $rules = [];
        }

        return $rules;
    }

    protected function registerEntities($cacheTag, $ids)
    {
        $this->cacheContext->registerEntities($cacheTag, $ids);
        if ($this->cacheContext->getSize() > $this->batchCacheCount) {
            $this->cleanCache();
            $this->cacheContext->flush();
        }
    }

    protected function cleanCache()
    {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }

    /**
     * @throws Exception
     */
    public function executeFull()
    {
        $this->tableWorker->clearReplica();
        $this->tableWorker->createTemporaryTable();

        $this->doReindex();

        $this->tableWorker->syncDataFull();
        $this->tableWorker->switchTables();
    }

    /**
     * @param array $ids
     * @throws Exception
     */
    public function executePartial(array $ids)
    {
        $this->tableWorker->createTemporaryTable();

        $this->doReindex($ids);

        $fieldName = $this->getType() === RuleIndexer::TYPE ? RuleIndex::RULE_ID : RuleIndex::PRODUCT_ID;
        $this->tableWorker->syncDataPartial([
            sprintf('%s IN (?)', $fieldName) => $ids
        ]);
    }

    /**
     * @param array $ids
     * @throws Exception
     */
    public function executeList(array $ids)
    {
        $this->executePartial($ids);
    }

    /**
     * @param int $id
     * @throws Exception
     */
    public function executeRow($id)
    {
        $this->executePartial([$id]);
    }

    /**
     * @param int[] $ids
     * @throws Exception
     */
    public function execute($ids)
    {
        $this->executePartial($ids);
    }
}
