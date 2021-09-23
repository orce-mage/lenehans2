<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Rule;

use Amasty\Feed\Model\InventoryResolver;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DB\Select;
use Magento\Rule\Model\Condition\Sql\Builder;

class GetValidFeedProducts
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Builder
     */
    protected $sqlBuilder;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    public function __construct(
        RuleFactory $ruleFactory,
        CollectionFactory $productCollectionFactory,
        Builder $sqlBuilder,
        InventoryResolver $inventoryResolver
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->sqlBuilder = $sqlBuilder;
        $this->inventoryResolver = $inventoryResolver;
    }

    /**
     * @param \Amasty\Feed\Model\Feed $model
     * @param array $ids
     *
     * @return array
     */
    public function execute(\Amasty\Feed\Model\Feed $model, array $ids = [])
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditionsSerialized($model->getConditionsSerialized());
        $rule->setStoreId($model->getStoreId());
        $model->setRule($rule);
        $this->updateIndex($model, $ids);
    }

    public function updateIndex(\Amasty\Feed\Model\Feed $model, array $ids = [])
    {
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->prepareCollection($model, $ids);
        $this->productIds = [];

        $conditions = $model->getRule()->getConditions();
        $conditions->collectValidatedAttributes($productCollection);
        $this->sqlBuilder->attachConditionToCollection($productCollection, $conditions);
        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect product attribute matches
         * several allowed values from condition simultaneously
         */
        $productCollection->distinct(true);
        $productCollection->getSelect()->reset(Select::COLUMNS);
        $select = $productCollection->getSelect()->columns(
            [
                'entity_id' => new \Zend_Db_Expr('null'),
                'feed_id' => new \Zend_Db_Expr((int)$model->getEntityId()),
                'valid_product_id' => 'e.' . $productCollection->getEntity()->getIdFieldName()
            ]
        );
        //fix for magento 2.3.2 for big number of products
        $select->reset(Select::ORDER);

        $query = $select->insertFromSelect($productCollection->getResource()->getTable(ValidProduct::TABLE_NAME));
        $productCollection->getConnection()->query($query);
    }

    /**
     * @param \Amasty\Feed\Model\Feed $model
     * @param array $ids
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function prepareCollection(\Amasty\Feed\Model\Feed $model, $ids = [])
    {
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($model->getStoreId());

        if ($ids) {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $ids]);
        }

        // DBEST-1250
        if ($model->getExcludeDisabled()) {
            $productCollection->addAttributeToFilter(
                'status',
                ['eq' => Status::STATUS_ENABLED]
            );
        }
        if ($model->getExcludeNotVisible()) {
            $productCollection->addAttributeToFilter(
                'visibility',
                ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]
            );
        }
        if ($model->getExcludeOutOfStock()) {
            $outOfStockProductIds = $this->inventoryResolver->getOutOfStockProductIds();

            if (!empty($outOfStockProductIds)) {
                $productCollection->addFieldToFilter(
                    'entity_id',
                    ['nin' => $outOfStockProductIds]
                );
            }
        }

        $model->getRule()->getConditions()->collectValidatedAttributes($productCollection);

        return $productCollection;
    }
}
