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
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;

class SpvFactor implements FactorInterface
{
    const ZERO_POINT = 'zero_point';

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
        return 'SPV';
    }

    public function getDescription(): string
    {
        return 'SPV = Sales Pre View = Number of Sales / Number of Product Page Views';
    }

    public function getUiComponent(): ?string
    {
        return 'sorting_factor_spv';
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $zeroPoint = $rankingFactor->getConfigData(self::ZERO_POINT, 365);

        $date = date('Y-m-d', strtotime('-' . $zeroPoint . ' day', time()));

        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select()->from($resource->getTableName('report_viewed_product_index'), [
            'product_id',
            'value' => new \Zend_Db_Expr('COUNT(index_id)'),
        ])
            ->where('added_at >= ?', $date)
            ->group('product_id');

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        $views = $connection->fetchPairs($select);

        $select = $connection->select()->from($resource->getTableName('sales_order_item'), [
            'product_id',
            'value' => new \Zend_Db_Expr('SUM(qty_ordered)'),
        ])
            ->where('created_at >= ?', $date)
            ->group('product_id');

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        $sales = $connection->fetchPairs($select);

        $this->indexer->process($rankingFactor, $productIds, function () use ($views, $sales, $zeroPoint) {
            foreach ($sales as $productId => $nSales) {
                $nViews = isset($views[$productId]) ? $views[$productId] : 0;

                if ($nViews > $nSales) {
                    $score = $nSales / $nViews * IndexInterface::MAX;
                } else {
                    $score = 0;
                }

                $this->indexer->add($productId, $score, "$nSales ($nViews); Zero Point: $zeroPoint");
            }
        });
    }
}
