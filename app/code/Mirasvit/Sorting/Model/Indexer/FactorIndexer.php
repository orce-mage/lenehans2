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

namespace Mirasvit\Sorting\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer;

class FactorIndexer
{
    private $resource;

    private $connection;

    /** @var RankingFactorInterface */
    private $rankingFactor = null;

    private $rowPool       = [];

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource   = $resource;
        $this->connection = $resource->getConnection();
    }

    public function getResource(): ResourceConnection
    {
        return $this->resource;
    }

    public function process(RankingFactorInterface $rankingFactor, array $productIds, \Closure $closure): void
    {
        $this->rankingFactor = $rankingFactor;

        $this->ensureScoreColumn(
            Indexer::getScoreColumn($rankingFactor)
        );

        $this->ensureValueColumn(
            Indexer::getValueColumn($rankingFactor)
        );

        $this->reset($productIds);

        $closure();

        $this->push();
    }

    public function add(int $productId, float $score, ?string $value, int $storeId = 0)
    {
        $this->rowPool[] = [
            IndexInterface::PRODUCT_ID                    => $productId,
            Indexer::getScoreColumn($this->rankingFactor) => $score,
            Indexer::getValueColumn($this->rankingFactor) => $value,
            IndexInterface::STORE_ID                      => $storeId,
        ];

        if (count($this->rowPool) > 1000) {
            $this->push();
        }
    }

    private function reset(array $productIds): void
    {
        $where = '';
        if ($productIds) {
            $where = IndexInterface::PRODUCT_ID . ' IN (' . implode(', ', $productIds) . ')';
        }
        $this->connection->update(
            $this->resource->getTableName(IndexInterface::TABLE_NAME),
            [
                Indexer::getScoreColumn($this->rankingFactor) => null,
                Indexer::getValueColumn($this->rankingFactor) => null,
            ],
            $where
        );
    }

    private function push()
    {
        if (!$this->rowPool) {
            return;
        }

        $this->connection->insertOnDuplicate(
            $this->resource->getTableName(IndexInterface::TABLE_NAME),
            $this->rowPool,
            [
                Indexer::getScoreColumn($this->rankingFactor),
                Indexer::getValueColumn($this->rankingFactor),
            ]
        );

        $this->rowPool = [];
    }

    private function ensureScoreColumn(string $columnName): void
    {
        $tableName = $this->resource->getTableName(IndexInterface::TABLE_NAME);

        if ($this->connection->tableColumnExists($tableName, $columnName)) {
            return;
        }

        $this->resource->getConnection()->addColumn($tableName, $columnName, [
            'type'     => Table::TYPE_DECIMAL,
            'length'   => '18,8',
            'nullable' => true,
            'unsigned' => false,
            'default'  => null,
            'comment'  => $columnName,
        ]);
    }

    private function ensureValueColumn(string $columnName): void
    {
        $tableName = $this->resource->getTableName(IndexInterface::TABLE_NAME);

        if ($this->connection->tableColumnExists($tableName, $columnName)) {
            return;
        }

        $this->resource->getConnection()->addColumn($tableName, $columnName, [
            'type'     => Table::TYPE_TEXT,
            'length'   => '255',
            'nullable' => true,
            'default'  => null,
            'comment'  => $columnName,
        ]);
    }
}
