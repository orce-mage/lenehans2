<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Indexer\Rule\Resources;

use Amasty\Stockstatus\Model\ResourceModel\RuleIndex as IndexResource;
use Exception;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\Table\Strategy as IndexerTableStrategy;

class TableWorker
{
    /**
     * @var IndexResource
     */
    private $indexResource;

    /**
     * @var IndexerTableStrategy
     */
    private $indexerTableStrategy;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    public function __construct(
        IndexResource $indexResource,
        IndexerTableStrategy $indexerTableStrategy,
        ActiveTableSwitcher $activeTableSwitcher
    ) {
        $this->indexResource = $indexResource;
        $this->indexerTableStrategy = $indexerTableStrategy;
        $this->activeTableSwitcher = $activeTableSwitcher;
    }

    protected function getConnection(): AdapterInterface
    {
        return $this->indexResource->getConnection();
    }

    public function getIdxTable(): string
    {
        $this->indexerTableStrategy->setUseIdxTable(true);
        return $this->indexResource->getTableName(
            $this->indexerTableStrategy->prepareTableName(IndexResource::REPLICA_TABLE)
        );
    }

    public function insert(array $data)
    {
        if ($data) {
            $this->getConnection()->insertOnDuplicate(
                $this->indexResource->getTableName($this->getIdxTable()),
                $data
            );
        }
    }

    public function clearReplica(): void
    {
        $this->getConnection()->truncateTable(
            $this->indexResource->getTableName(IndexResource::REPLICA_TABLE)
        );
    }

    public function createTemporaryTable(): void
    {
        $this->getConnection()->createTemporaryTableLike(
            $this->getIdxTable(),
            $this->indexResource->getTableName(IndexResource::REPLICA_TABLE),
            true
        );

        $this->getConnection()->delete($this->getIdxTable());
    }

    /**
     * @throws Exception
     */
    public function syncDataFull(): void
    {
        $this->syncData($this->indexResource->getTableName(IndexResource::REPLICA_TABLE));
    }

    /**
     * @param array $condition
     * @throws Exception
     */
    public function syncDataPartial(array $condition)
    {
        $this->syncData($this->indexResource->getTableName(IndexResource::MAIN_TABLE), $condition);
    }

    /**
     * @param string $destinationTable
     * @param array $condition
     * @throws Exception
     */
    private function syncData(string $destinationTable, array $condition = []): void
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->getConnection()->delete($destinationTable, $condition);
            $this->insertFromTable(
                $this->getIdxTable(),
                $destinationTable
            );
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }
    }

    public function switchTables(): void
    {
        $this->activeTableSwitcher->switchTable(
            $this->getConnection(),
            [$this->indexResource->getTableName(IndexResource::MAIN_TABLE)]
        );
    }

    protected function insertFromTable(string $sourceTable, string $destTable): void
    {
        $sourceColumns = array_keys($this->getConnection()->describeTable($sourceTable));
        $targetColumns = array_keys($this->getConnection()->describeTable($destTable));

        $select = $this->getConnection()->select()->from($sourceTable, $sourceColumns);

        $this->getConnection()->query($this->getConnection()->insertFromSelect($select, $destTable, $targetColumns));
    }
}
