<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration;

use Amasty\ImportCore\Import\Config\Profile\EntitiesConfig;
use Amasty\ImportCore\Import\Config\ProfileConfig;
use Amasty\ImportCore\Import\FileResolver\Type\ServerFile\ConfigInterface as FileResolverConfigInterface;
use Amasty\ImportCore\Import\Run;
use Amasty\ImportCore\Import\Source\Type\Csv\ConfigInterface as CsvSourceConfigInterface;
use Amasty\ImportCore\Test\Integration\TestModule\Model\ResourceModel\TestEntity1;
use Amasty\ImportCore\Test\Integration\TestModule\Setup\TestEntity1\CreateTable as CreateTableCommand;
use Amasty\ImportCore\Test\Integration\TestModule\Setup\TestEntity1\DropTable as DropTableCommand;
use Amasty\ImportCore\Test\Integration\Utils\ConfigManager;
use Amasty\ImportCore\Test\Integration\Utils\EntitiesConfigCreator;
use Amasty\ImportCore\Test\Integration\Utils\TempFileManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class BehaviorsTest extends \PHPUnit\Framework\TestCase
{
    use TempFileManager;
    use ConfigManager;
    use EntitiesConfigCreator;

    const FILE_RESOLVER_CLASS = 'custom_config_resolver';
    const INITIAL_TABLE_DATA = [
        ['id' => '2', 'field_1' => 'db value 2'],
        ['id' => '3', 'field_1' => 'db value 3']
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cleanupAll();
        $this->objectManager->get(CreateTableCommand::class)->execute();
    }

    protected function tearDown(): void
    {
        $this->revertImportConfigOverride();
        $this->cleanupAll();
    }

    protected function cleanupAll()
    {
        $this->objectManager->get(DropTableCommand::class)->execute();
        $this->cleanupTempDirectory();
    }

    protected function getTableContents(string $tableName, $columns = '*'): array
    {
        /** @var ResourceConnection $connection */
        $connection = $this->objectManager->get(ResourceConnection::class);

        return $connection->getConnection()->fetchAll(
            $connection->getConnection()->select()->from($connection->getTableName($tableName), $columns)
        );
    }

    protected function setTableContents(string $tableName, array $data): int
    {
        /** @var ResourceConnection $connection */
        $connection = $this->objectManager->get(ResourceConnection::class);
        $realTableName = $connection->getTableName($tableName);

        return $connection->getConnection()->truncateTable($realTableName)
            ->insertMultiple($realTableName, $data);
    }

    /**
     * Data provider for testImportRun
     * @return array
     */
    public function importRunDataProvider(): array
    {
        $expectations = [
            'add'       => [
                ['id' => '2', 'field_1' => 'db value 2'],
                ['id' => '3', 'field_1' => 'db value 3'],
                ['id' => '4', 'field_1' => 'import value 1'],
                ['id' => '5', 'field_1' => 'import value 2'],
            ],
            'addUpdate' => [
                ['id' => '1', 'field_1' => 'import value 1'],
                ['id' => '2', 'field_1' => 'import value 2'],
                ['id' => '3', 'field_1' => 'db value 3'],
            ],
            'update'    => [
                ['id' => '2', 'field_1' => 'import value 2'],
                ['id' => '3', 'field_1' => 'db value 3'],
            ],
            'delete'    => [
                ['id' => '3', 'field_1' => 'db value 3'],
            ]
        ];

        return [
            'add'       => ['add', $expectations['add']],
            'addUpdate' => ['addUpdate', $expectations['addUpdate']],
            'update'    => ['update', $expectations['update']],
            'delete'    => ['delete', $expectations['delete']],

            'addDirect'       => ['add_direct', $expectations['add']],
            'addUpdateDirect' => ['addUpdate_direct', $expectations['addUpdate']],
            'updateDirect'    => ['update_direct', $expectations['update']],
            'deleteDirect'    => ['delete_direct', $expectations['delete']],

            'custom' => ['customAdd', $expectations['add']],
        ];
    }

    /**
     * @magentoDbIsolation disabled
     * @dataProvider importRunDataProvider
     * @magentoConfigFixture default_store amasty_import/multi_process/enabled 0
     * @param string $behavior
     * @param array $expectedDataAfterImport
     */
    public function testImportRun(
        string $behavior,
        array $expectedDataAfterImport
    ) {
        $entitiesConfig = $this->createEntitiesConfig(['id', 'field_1']);
        $entitiesConfig->setBehavior($behavior);

        /** @var ProfileConfig $profileConfig */
        $profileConfig = $this->objectManager->create(
            ProfileConfig::class,
            [
                'data' => [
                    ProfileConfig::STRATEGY => 'validate_and_import',
                    ProfileConfig::BATCH_SIZE => 500,
                    ProfileConfig::BEHAVIOR => $behavior,
                    ProfileConfig::ENTITY_CODE => 'test',
                    ProfileConfig::FILE_RESOLVER_TYPE => 'server_file',
                    ProfileConfig::SOURCE_TYPE => 'csv',
                    ProfileConfig::ENTITIES_CONFIG => $entitiesConfig,
                    ProfileConfig::ENTITY_IDENTIFIER => 'id'
                ]
            ]
        );
        /** @var FileResolverConfigInterface $fileResolverConfig */
        $fileResolverConfig = $this->objectManager->create(FileResolverConfigInterface::class);
        $fileResolverConfig->setFilename(
            $this->deployTemporalImportFile(__DIR__ . '/_files/import_files/behaviors_test_entity_1.csv')
        );
        $profileConfig->getExtensionAttributes()->setServerFileResolver($fileResolverConfig);
        /** @var CsvSourceConfigInterface $fileResolverConfig */
        $sourceConfig = $this->objectManager->create(CsvSourceConfigInterface::class);
        $profileConfig->getExtensionAttributes()->setCsvSource($sourceConfig);

        $this->overrideImportConfig(__DIR__ . '/_files/config/am_import_test_entity_1.xml');

        $this->setTableContents(TestEntity1::TABLE_NAME, self::INITIAL_TABLE_DATA);

        /** @var Run $runner */
        $runner = $this->objectManager->create(Run::class);
        $result = $runner->execute($profileConfig, uniqid('test_'));

        $this->assertFalse(
            $result->isFailed(),
            'Import has failed with the following errors: ' . json_encode($result->getMessages())
        );

        $tableData = $this->getTableContents(TestEntity1::TABLE_NAME, ['id', 'field_1']);

        $this->assertEquals($expectedDataAfterImport, $tableData);
    }
}
