<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;

class DiscountFactor implements FactorInterface
{
    private $context;

    private $indexer;

    public function __construct(
        Context $context,
        FactorIndexer $indexer
    ) {
        $this->context = $context;
        $this->indexer = $indexer;
    }

    public function getName(): string
    {
        return 'Discount';
    }

    public function getDescription(): string
    {
        return "Calculation: The difference between regular price and special prices.";
    }

    public function getUiComponent(): ?string
    {
        return null;
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        /**
         * Regular product with regular price=32 and special price=24 has:
         * price=32, final_price=24, min_price=24, max_price=24
         * Configurable product with max regular price=50 and min special price=1 has:
         * price=0, final_price=0, min_price=1, max_price=50
         * Grouped product has:
         * price=NULL, final_price=NULL
         */
        $select = $connection->select();
        $select->from(['index' => $resource->getTableName('catalog_product_index_price')], [
            'entity_id',
            'value' => new \Zend_Db_Expr('(GREATEST(max_price, price) - GREATEST(final_price, min_price) ) / GREATEST(max_price, price) * 100'),
        ])->joinLeft(
            ['e' => $resource->getTableName('catalog_product_entity')],
            'e.entity_id = index.entity_id',
            []
        )
            ->where('e.type_id = ?', 'simple')
            ->where('index.price > 0')
            ->group('index.entity_id');

        if ($productIds) {
            $select->where('index.entity_id IN (?)', $productIds);
        }

        $rows = $connection->fetchPairs($select);

        if (!count($rows)) {
            return;
        }

        $max = max(array_values($rows));

        if ($max == 0) {
            return;
        }

        $this->indexer->process($rankingFactor, $productIds, function () use ($rows, $max, $connection, $resource, $rankingFactor) {
            foreach ($rows as $productId => $value) {
                $score = $value / $max * IndexInterface::MAX;

                $this->indexer->add($productId, $score, $value);
            }

            // calculate discount factor based on child discounts
            // catalog_product_super_link - contains relations between configurable, bundled, grouped to simple
            $select = $connection->select();
            $select->from(['link' => $resource->getTableName('catalog_product_super_link')], [
                'parent_id',
            ])->joinLeft(
                ['index' => $resource->getTableName(IndexInterface::TABLE_NAME)],
                'link.product_id = index.product_id',
                [new \Zend_Db_Expr('MAX(' . Indexer::getScoreColumn($rankingFactor) . ')'),]
            )->group('link.parent_id');

            $rows = $connection->fetchPairs($select);

            foreach ($rows as $productId => $value) {
                $score = (float)$value;
                $this->indexer->add($productId, $score, $value);
            }
        });
    }
}
