<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\TestModule\Setup\TestEntity1;

use Amasty\ImportCore\Test\Integration\TestModule\Model\ResourceModel\TestEntity1 as TestEntity1Resource;
use Amasty\ImportCore\Test\Integration\TestModule\Model\TestEntity1;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateTable
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    public function execute()
    {
        $table = $this->schemaSetup->getConnection()
            ->newTable($this->schemaSetup->getTable(TestEntity1Resource::TABLE_NAME))
            ->addColumn(
                TestEntity1::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                TestEntity1::FIELD_1,
                Table::TYPE_TEXT,
                127,
                ['nullable' => false]
            )
            ->addColumn(
                TestEntity1::FIELD_2,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                TestEntity1::FIELD_3,
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => true, 'precision' => '12', 'scale' => '2']
            );

        $this->schemaSetup->getConnection()->createTable($table);
    }
}
