<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Setup\Operation;

use Amasty\ImportCore\Model\FileUploadMap\FileUploadMap;
use Amasty\ImportCore\Model\FileUploadMap\ResourceModel\FileUploadMap as FileUploadMapResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateFileUploadMapTable
{
    public function execute(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable(FileUploadMapResource::TABLE_NAME))
            ->addColumn(
                FileUploadMap::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                FileUploadMap::FILENAME,
                Table::TYPE_TEXT,
                32,
                ['nullable' => false]
            )
            ->addColumn(
                FileUploadMap::FILEEXT,
                Table::TYPE_TEXT,
                32,
                ['nullable' => false]
            )
            ->addColumn(
                FileUploadMap::HASH,
                Table::TYPE_TEXT,
                32,
                ['nullable' => false]
            )
            ->addColumn(
                FileUploadMap::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT
                ]
            )
            ;

        $installer->getConnection()->createTable($table);
    }
}
