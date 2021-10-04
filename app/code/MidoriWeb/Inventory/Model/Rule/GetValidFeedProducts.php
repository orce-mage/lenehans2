<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace MidoriWeb\Inventory\Model\Rule;

use Amasty\Feed\Model\InventoryResolver;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DB\Select;
use Magento\Rule\Model\Condition\Sql\Builder;

class GetValidFeedProducts extends \Amasty\Feed\Model\Rule\GetValidFeedProducts
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
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->sqlBuilder = $sqlBuilder;
        $this->inventoryResolver = $inventoryResolver;

        parent::__construct($ruleFactory, $productCollectionFactory, $sqlBuilder, $inventoryResolver);
    }

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
            $outOfStockProductIds = $this->inventoryResolver->getOutOfStockProductIds( $model->getStoreId() );

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
