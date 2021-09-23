<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Setup\Operation;

use Amasty\ExportCore\Model\Process\Process;
use Amasty\ExportCore\Model\Process\ResourceModel\Process as ProcessResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateProcessTable
{
    public function execute(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable(ProcessResource::TABLE_NAME))
            ->addColumn(
                Process::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                Process::ENTITY_CODE,
                Table::TYPE_TEXT,
                127,
                ['nullable' => false]
            )
            ->addColumn(
                Process::PROFILE_CONFIG,
                Table::TYPE_BLOB,
                Table::MAX_TEXT_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                Process::IDENTITY,
                Table::TYPE_TEXT,
                127,
                ['nullable' => false]
            )
            ->addColumn(
                Process::PID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                Process::STATUS,
                Table::TYPE_TEXT,
                127,
                ['nullable' => false]
            )
            ->addColumn(
                Process::FINISHED,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                Process::EXPORT_RESULT,
                Table::TYPE_BLOB,
                null,
                ['nullable' => true]
            )
            ->addIndex(
                $installer->getIdxName(
                    ProcessResource::TABLE_NAME,
                    [Process::IDENTITY],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [Process::IDENTITY],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $installer->getConnection()->createTable($table);
    }
}
