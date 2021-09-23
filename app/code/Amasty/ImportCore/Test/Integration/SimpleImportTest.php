<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration;

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

class SimpleImportTest extends \PHPUnit\Framework\TestCase
{
    use TempFileManager;
    use ConfigManager;
    use EntitiesConfigCreator;

    const FILE_RESOLVER_CLASS = 'custom_config_resolver';
    const BEHAVIOR = 'add';

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

    protected function getTableContents(string $tableName): array
    {
        /** @var ResourceConnection $connection */
        $connection = $this->objectManager->get(ResourceConnection::class);

        return $connection->getConnection()->fetchAll(
            $connection->getConnection()->select()->from($connection->getTableName($tableName))
        );
    }

    /**
     * Data provider for testImportRun
     * @return array
     */
    public function importRunDataProvider(): array
    {
        $entitiesConfig = $this->createEntitiesConfig(['id', 'field_1', 'field_2', 'field_3']);
        $entitiesConfig->setBehavior(self::BEHAVIOR);

        return [
            [
                'config/am_import_test_entity_1.xml',
                'import_files/test_entity_1.csv',
                [
                    ProfileConfig::STRATEGY           => 'validate_and_import',
                    ProfileConfig::BATCH_SIZE         => 500,
                    ProfileConfig::BEHAVIOR           => self::BEHAVIOR,
                    ProfileConfig::ENTITY_CODE        => 'test',
                    ProfileConfig::FILE_RESOLVER_TYPE => 'server_file',
                    ProfileConfig::SOURCE_TYPE        => 'csv',
                    ProfileConfig::ENTITIES_CONFIG => $entitiesConfig,
                    ProfileConfig::ENTITY_IDENTIFIER => 'id'
                ],
                [
                    [
                        'id'      => '1',
                        'field_1' => 'sample value 1',
                        'field_2' => '0',
                        'field_3' => '3.14',
                    ],
                    [
                        'id'      => '2',
                        'field_1' => 'sample value 2',
                        'field_2' => '0',
                        'field_3' => '123.00',
                    ],
                    [
                        'id'      => '3',
                        'field_1' => 'sample value 3',
                        'field_2' => '1',
                        'field_3' => '0.00',
                    ]
                ]
            ]
        ];
    }

    /**
     * @magentoDbIsolation disabled
     * @dataProvider importRunDataProvider
     * @magentoConfigFixture default_store amasty_import/multi_process/enabled 0
     * @param string $xmlConfigLocation
     * @param string $importFileLocation
     * @param array $configData
     * @param array $expectedResult
     */
    public function testImportRun(
        string $xmlConfigLocation,
        string $importFileLocation,
        array $configData,
        array $expectedResult
    ) {
        $this->overrideImportConfig(__DIR__ . '/_files/' . $xmlConfigLocation);

        /** @var ProfileConfig $profileConfig */
        $profileConfig = $this->objectManager->create(
            ProfileConfig::class,
            [
                'data' => $configData
            ]
        );
        /** @var FileResolverConfigInterface $fileResolverConfig */
        $fileResolverConfig = $this->objectManager->create(FileResolverConfigInterface::class);
        $fileResolverConfig->setFilename($this->deployTemporalImportFile(__DIR__ . '/_files/' . $importFileLocation));
        $profileConfig->getExtensionAttributes()->setServerFileResolver($fileResolverConfig);
        /** @var CsvSourceConfigInterface $fileResolverConfig */
        $sourceConfig = $this->objectManager->create(CsvSourceConfigInterface::class);
        $profileConfig->getExtensionAttributes()->setCsvSource($sourceConfig);

        /** @var Run $runner */
        $runner = $this->objectManager->create(Run::class);
        $result = $runner->execute($profileConfig, uniqid('test_'));

        $this->assertFalse(
            $result->isFailed(),
            'Import has failed with the following errors: ' . json_encode($result->getMessages())
        );

        $tableData = $this->getTableContents(TestEntity1::TABLE_NAME);

        $this->assertEquals($expectedResult, $tableData);
    }
}
