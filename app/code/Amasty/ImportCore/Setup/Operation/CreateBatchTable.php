<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Setup\Operation;

use Amasty\ImportCore\Model\Batch\Batch;
use Amasty\ImportCore\Model\Batch\ResourceModel\Batch as BatchResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateBatchTable
{
    public function execute(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable(BatchResource::TABLE_NAME))
            ->addColumn(
                Batch::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                Batch::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT
                ]
            )
            ->addColumn(
                Batch::PROCESS_IDENTITY,
                Table::TYPE_TEXT,
                127,
                ['nullable' => false]
            )
            ->addColumn(
                Batch::BATCH_DATA,
                Table::TYPE_BLOB,
                Table::MAX_TEXT_SIZE,
                ['nullable' => false]
            )
            ->addIndex(
                $installer->getIdxName(BatchResource::TABLE_NAME, [Batch::PROCESS_IDENTITY]),
                [Batch::PROCESS_IDENTITY]
            );

        $installer->getConnection()->createTable($table);
    }
}
